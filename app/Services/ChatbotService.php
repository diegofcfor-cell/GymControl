<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Membresia;
use App\Models\PreguntaFrecuente;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Chatbot de preguntas frecuentes sin IA (sin costo de uso):
 *  - Busca la pregunta frecuente cuyas palabras clave y texto mejor coinciden con el mensaje.
 *  - Responde con datos en vivo: planes/precios (membresías activas), aforo actual y, si el admin
 *    los cargó en Configuración, horarios y datos de contacto.
 *  - Si nada coincide lo suficiente, sugiere preguntas y deriva a recepción.
 */
class ChatbotService
{
    /** Puntaje mínimo para dar una respuesta en lugar de sugerencias. */
    public const PUNTAJE_MINIMO = 3;

    protected const VACIAS = [
        'a', 'al', 'algo', 'como', 'con', 'cual', 'cuales', 'cuando', 'de', 'del', 'el', 'en', 'es', 'esta',
        'hay', 'la', 'las', 'le', 'lo', 'los', 'me', 'mi', 'para', 'por', 'puedo', 'que', 'se', 'si', 'su', 'te',
        'tiene', 'tienen', 'un', 'una', 'y', 'o', 'hola', 'quiero', 'saber', 'gym', 'gimnasio',
    ];

    /** Temas con respuesta generada a partir de los datos del sistema. */
    protected const TEMAS_EN_VIVO = [
        'planes' => ['precio', 'precios', 'cuesta', 'cuestan', 'valor', 'cuota', 'plan', 'planes', 'membresia', 'membresias', 'tarifa', 'tarifas'],
        'aforo' => ['gente', 'personas', 'lleno', 'aforo', 'ocupado', 'concurrido', 'ahora'],
        'horarios' => ['horario', 'horarios', 'hora', 'horas', 'abren', 'abre', 'cierran', 'cierra', 'abierto', 'cerrado', 'sabado', 'domingo', 'feriado', 'feriados'],
        'contacto' => ['direccion', 'donde', 'ubicacion', 'ubicados', 'queda', 'quedan', 'telefono', 'whatsapp', 'contacto', 'llamar', 'mail', 'correo', 'email', 'instagram'],
    ];

    public function __construct(protected AforoService $aforo) {}

    /** @return array{respuesta:string,sugerencias:array<int,string>,encontrada:bool} */
    public function responder(string $mensaje): array
    {
        $palabras = $this->palabras($mensaje);

        if ($palabras === []) {
            return $this->sinRespuesta();
        }

        $candidatas = PreguntaFrecuente::activas()->get()
            ->map(fn ($p) => ['pregunta' => $p, 'puntaje' => $this->puntaje($palabras, $p)])
            ->sortByDesc('puntaje')->values();

        $mejor = $candidatas->first();
        $tema = $this->temaEnVivo($palabras);

        // Una pregunta cargada por el admin con buena coincidencia tiene prioridad sobre los temas en vivo.
        if ($mejor && $mejor['puntaje'] >= self::PUNTAJE_MINIMO && (! $tema || $mejor['puntaje'] > $tema['puntaje'])) {
            return [
                'respuesta' => $mejor['pregunta']->respuesta,
                'sugerencias' => $this->relacionadas($candidatas->slice(1)),
                'encontrada' => true,
            ];
        }

        if ($tema) {
            return ['respuesta' => $this->respuestaEnVivo($tema['nombre']), 'sugerencias' => [], 'encontrada' => true];
        }

        return $this->sinRespuesta($this->relacionadas($candidatas));
    }

    /** @return array<int,string> palabras normalizadas sin tildes ni palabras vacías */
    public function palabras(string $texto): array
    {
        $texto = Str::of(Str::ascii($texto))->lower()->replaceMatches('/[^a-z0-9ñ ]+/u', ' ')->squish()->toString();
        $lista = array_filter(explode(' ', $texto), fn ($w) => strlen($w) > 1 && ! in_array($w, self::VACIAS, true));

        return array_values(array_unique($lista));
    }

    /** Raíz simple para que "horario", "horarios" y "horas" coincidan. */
    protected function raiz(string $palabra): string
    {
        return substr($palabra, 0, 4);
    }

    protected function coincide(string $a, string $b): bool
    {
        return $a === $b || (strlen($a) >= 4 && strlen($b) >= 4 && $this->raiz($a) === $this->raiz($b));
    }

    /** Palabra clave = 3 puntos, palabra de la pregunta = 1 punto. */
    protected function puntaje(array $palabras, PreguntaFrecuente $p): int
    {
        $claves = $this->palabras(str_replace(',', ' ', (string) $p->palabras_clave));
        $texto = $this->palabras($p->pregunta);
        $puntos = 0;

        foreach ($palabras as $w) {
            if (collect($claves)->contains(fn ($c) => $this->coincide($w, $c))) {
                $puntos += 3;
            } elseif (collect($texto)->contains(fn ($t) => $this->coincide($w, $t))) {
                $puntos += 1;
            }
        }

        return $puntos;
    }

    /** @return array{nombre:string,puntaje:int}|null */
    protected function temaEnVivo(array $palabras): ?array
    {
        $mejor = null;
        foreach (self::TEMAS_EN_VIVO as $nombre => $claves) {
            if (! $this->temaDisponible($nombre)) {
                continue;
            }
            $puntos = 3 * count(array_filter($palabras, fn ($w) => in_array($w, $claves, true)));
            if ($puntos > 0 && (! $mejor || $puntos > $mejor['puntaje'])) {
                $mejor = ['nombre' => $nombre, 'puntaje' => $puntos];
            }
        }

        return $mejor;
    }

    /** Horarios y contacto solo se responden en vivo si el admin los cargó. */
    protected function temaDisponible(string $tema): bool
    {
        return match ($tema) {
            'horarios' => filled(Configuracion::obtener('horarios')),
            'contacto' => collect(Configuracion::varios(['direccion', 'telefono', 'email', 'instagram']))->filter()->isNotEmpty(),
            default => true,
        };
    }

    protected function respuestaEnVivo(string $tema): string
    {
        if ($tema === 'horarios') {
            return "Nuestros horarios:\n".trim(Configuracion::obtener('horarios'));
        }

        if ($tema === 'contacto') {
            $c = Configuracion::varios(['direccion', 'telefono', 'email', 'instagram']);
            $lineas = array_filter([
                $c['direccion'] ? "Dirección: {$c['direccion']}" : null,
                $c['telefono'] ? "Teléfono / WhatsApp: {$c['telefono']}" : null,
                $c['email'] ? "Correo: {$c['email']}" : null,
                $c['instagram'] ? 'Instagram: @'.ltrim($c['instagram'], '@') : null,
            ]);

            return "Datos de contacto:\n".implode("\n", $lineas);
        }

        if ($tema === 'aforo') {
            $e = $this->aforo->estado();

            $personas = $e['presentes'] === 1 ? '1 persona' : "{$e['presentes']} personas";

            return "Ahora hay {$personas} en el gimnasio, sobre una capacidad de {$e['capacidad']}. "
                .'Podés ver las horas pico en la sección Aforo.';
        }

        $planes = Membresia::activas()->orderBy('precio')->get();
        if ($planes->isEmpty()) {
            return 'Por ahora no hay planes publicados. Consultá en recepción.';
        }

        $lineas = $planes->map(fn ($m) => "• {$m->nombre}: $ ".number_format($m->precio, 0, ',', '.')
            ." por {$m->duracion_dias} días, ".($m->esPaseLibre() ? 'pase libre' : "hasta {$m->horas_diarias_max} h por día"));

        return "Estos son los planes vigentes:\n".$lineas->implode("\n");
    }

    /** @return array<int,string> */
    protected function relacionadas(Collection $candidatas): array
    {
        return $candidatas->filter(fn ($c) => $c['puntaje'] > 0)->take(3)
            ->map(fn ($c) => $c['pregunta']->pregunta)->values()->all();
    }

    /** @return array{respuesta:string,sugerencias:array<int,string>,encontrada:bool} */
    protected function sinRespuesta(array $sugerencias = []): array
    {
        if ($sugerencias === []) {
            $sugerencias = PreguntaFrecuente::activas()->limit(3)->pluck('pregunta')->all();
        }

        return [
            'respuesta' => 'No encontré una respuesta para eso. Probá con otras palabras o consultá en recepción.',
            'sugerencias' => $sugerencias,
            'encontrada' => false,
        ];
    }
}
