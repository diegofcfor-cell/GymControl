<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Socio;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Reglas de negocio del control de acceso (PIN / QR):
 *  - El ingreso exige socio activo, cuota vigente y apto físico vigente.
 *  - El egreso SIEMPRE se permite y calcula el tiempo de permanencia.
 *  - Si la membresía tiene tope de horas diarias, se informa al superarlo.
 */
class AccesoService
{
    public const MAX_INTENTOS_FALLIDOS = 8;

    /** @return array{ok:bool,tipo:string,mensaje:string,motivos:array,socio:?Socio,hora:?string,permanencia:?string,aviso:?string} */
    public function procesar(string $codigo, ?string $ip = null): array
    {
        $codigo = trim($codigo);
        $clave = 'acceso-fallido:'.($ip ?? 'local');

        if (RateLimiter::tooManyAttempts($clave, self::MAX_INTENTOS_FALLIDOS)) {
            return $this->resultado(false, 'bloqueado', 'Demasiados intentos fallidos. Espere un minuto y vuelva a intentar.');
        }

        if ($codigo === '') {
            return $this->resultado(false, 'desconocido', 'Ingrese su PIN o escanee su código QR.');
        }

        $socio = Socio::with('membresia')
            ->where(function ($q) use ($codigo) {
                $q->where('pin', $codigo)->orWhere('qr_codigo', $codigo);
            })
            ->first();

        if (! $socio) {
            RateLimiter::hit($clave, 60);

            return $this->resultado(false, 'desconocido', 'Código no reconocido. Consulte en recepción.');
        }

        RateLimiter::clear($clave);

        $abierta = $socio->asistencias()->abiertas()->whereDate('fecha', today())->latest('id')->first();

        return $abierta ? $this->egreso($socio, $abierta) : $this->ingreso($socio);
    }

    protected function ingreso(Socio $socio): array
    {
        $motivos = [];
        if (! $socio->estaActivo()) {
            $motivos[] = 'Socio dado de baja o inactivo.';
        }
        if (! $socio->cuotaAlDia()) {
            $motivos[] = 'Cuota vencida: regularice su pago en recepción.';
        }
        if (! $socio->aptoVigente()) {
            $motivos[] = 'Apto físico vencido o no presentado.';
        }
        if (! $motivos && $this->topeAlcanzado($socio)) {
            $motivos[] = 'Alcanzó el tope de horas diarias de su membresía.';
        }

        if ($motivos) {
            return $this->resultado(false, 'denegado', 'Acceso denegado', $socio, $motivos);
        }

        $ahora = now();
        Asistencia::create([
            'socio_id' => $socio->id,
            'fecha' => $ahora->toDateString(),
            'hora_ingreso' => $ahora->format('H:i:s'),
        ]);

        $aviso = null;
        if ($socio->fecha_vencimiento && today()->diffInDays($socio->fecha_vencimiento, false) <= 3) {
            $aviso = 'Su cuota vence el '.$socio->fecha_vencimiento->format('d/m/Y').'.';
        }

        return $this->resultado(true, 'ingreso', '¡Bienvenido/a!', $socio, [], $ahora->format('H:i'), null, $aviso);
    }

    protected function egreso(Socio $socio, Asistencia $abierta): array
    {
        $ahora = now();
        $segundos = (int) max(0, floor($abierta->momentoIngreso()->diffInSeconds($ahora, false)));
        $segundos = min($segundos, 86399);

        $abierta->update([
            'hora_salida' => $ahora->format('H:i:s'),
            'tiempo_permanencia' => gmdate('H:i:s', $segundos),
        ]);

        $aviso = null;
        $max = $socio->membresia?->horas_diarias_max;
        if ($max !== null && $this->minutosHoy($socio) > $max * 60) {
            $aviso = "Superó el tope diario de {$max} h de su membresía.";
        }

        $permanencia = $abierta->fresh()->permanenciaTexto();

        return $this->resultado(true, 'egreso', '¡Hasta pronto!', $socio, [], $ahora->format('H:i'), $permanencia, $aviso);
    }

    protected function topeAlcanzado(Socio $socio): bool
    {
        $max = $socio->membresia?->horas_diarias_max;

        return $max !== null && $this->minutosHoy($socio) >= $max * 60;
    }

    protected function minutosHoy(Socio $socio): int
    {
        return (int) $socio->asistencias()
            ->whereDate('fecha', today())
            ->whereNotNull('tiempo_permanencia')
            ->get()
            ->sum(fn (Asistencia $a) => $a->minutosPermanencia() ?? 0);
    }

    protected function resultado(bool $ok, string $tipo, string $mensaje, ?Socio $socio = null, array $motivos = [], ?string $hora = null, ?string $permanencia = null, ?string $aviso = null): array
    {
        return compact('ok', 'tipo', 'mensaje', 'motivos', 'socio', 'hora', 'permanencia', 'aviso');
    }
}
