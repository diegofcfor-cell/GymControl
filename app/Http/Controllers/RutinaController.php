<?php

namespace App\Http\Controllers;

use App\Models\Rutina;
use App\Models\SolicitudRutina;
use App\Support\PlantillasRutina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Panel del profesor: atiende los pedidos de rutina de los socios. */
class RutinaController extends Controller
{
    public function index()
    {
        return view('rutinas.index', [
            'pendientes' => SolicitudRutina::with('socio.membresia')->pendientes()->oldest()->get(),
            'rutinas' => Rutina::with(['socio', 'profesor'])->withCount('ejercicios')->latest('updated_at')->limit(30)->get(),
        ]);
    }

    /** Biblioteca de rutinas base numeradas, filtrable por objetivo. */
    public function plantillas(Request $request)
    {
        $objetivo = $request->query('objetivo');

        return view('rutinas.plantillas', [
            'plantillas' => PlantillasRutina::todas()->when($objetivo, fn ($c) => $c->where('objetivo', $objetivo)),
            'objetivos' => SolicitudRutina::OBJETIVOS,
            'objetivo' => $objetivo,
        ]);
    }

    public function plantilla(string $codigo)
    {
        $plantilla = PlantillasRutina::buscar($codigo) ?? abort(404);

        return view('rutinas.plantilla', [
            'plantilla' => $plantilla,
            'pendientes' => SolicitudRutina::with('socio')->pendientes()->oldest()->get(),
        ]);
    }

    public function create(Request $request, SolicitudRutina $solicitud)
    {
        abort_unless($solicitud->estado === 'pendiente', 404);
        $plantilla = PlantillasRutina::buscar($request->query('plantilla'));

        return view('rutinas.form', [
            'rutina' => $plantilla
                ? new Rutina(['nombre' => $plantilla['nombre'], 'objetivo' => $plantilla['objetivo'], 'observaciones' => $plantilla['observaciones']])
                : new Rutina(['nombre' => 'Rutina '.$solicitud->objetivo, 'objetivo' => $solicitud->objetivo]),
            'socio' => $solicitud->socio,
            'solicitud' => $solicitud,
            'ejercicios' => $plantilla ? PlantillasRutina::filas($plantilla) : $this->filasIniciales($solicitud->dias_por_semana),
            'plantilla' => $plantilla,
            'plantillas' => PlantillasRutina::sugeridas($solicitud->objetivo, $solicitud->dias_por_semana, $solicitud->nivel),
        ]);
    }

    public function store(Request $request, SolicitudRutina $solicitud)
    {
        abort_unless($solicitud->estado === 'pendiente', 404);
        $datos = $this->validar($request);

        DB::transaction(function () use ($datos, $request, $solicitud) {
            // La rutina nueva reemplaza a la que el socio tenía activa.
            Rutina::where('socio_id', $solicitud->socio_id)->where('activa', true)->update(['activa' => false]);

            $rutina = Rutina::create([
                'socio_id' => $solicitud->socio_id,
                'user_id' => $request->user()->id,
                'nombre' => $datos['nombre'],
                'objetivo' => $datos['objetivo'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
                'activa' => true,
            ]);
            $this->guardarEjercicios($rutina, $datos['ejercicios']);

            $solicitud->update(['estado' => 'atendida', 'rutina_id' => $rutina->id]);
        });

        return redirect()->route('rutinas.index')->with('ok', 'Rutina creada. El socio ya la ve en su portal.');
    }

    public function edit(Request $request, Rutina $rutina)
    {
        $solicitud = SolicitudRutina::where('rutina_id', $rutina->id)->first();
        $plantilla = PlantillasRutina::buscar($request->query('plantilla'));
        if ($plantilla) {
            // Solo se reemplaza en pantalla: no se guarda hasta que el profesor confirme.
            $rutina->fill(['nombre' => $plantilla['nombre'], 'objetivo' => $plantilla['objetivo'], 'observaciones' => $plantilla['observaciones']]);
        }

        return view('rutinas.form', [
            'rutina' => $rutina,
            'socio' => $rutina->socio,
            'solicitud' => $solicitud,
            'ejercicios' => $plantilla
                ? PlantillasRutina::filas($plantilla)
                : $rutina->ejercicios->map->only(['dia', 'ejercicio', 'series', 'repeticiones', 'descanso', 'notas'])->all(),
            'plantilla' => $plantilla,
            'plantillas' => PlantillasRutina::sugeridas($solicitud?->objetivo ?? $rutina->objetivo, $solicitud?->dias_por_semana, $solicitud?->nivel),
        ]);
    }

    public function update(Request $request, Rutina $rutina)
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($datos, $rutina) {
            $rutina->update([
                'nombre' => $datos['nombre'],
                'objetivo' => $datos['objetivo'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
            ]);
            $rutina->ejercicios()->delete();
            $this->guardarEjercicios($rutina, $datos['ejercicios']);
        });

        return redirect()->route('rutinas.index')->with('ok', 'Rutina actualizada.');
    }

    protected function validar(Request $request): array
    {
        // Las filas que el profesor dejó vacías se ignoran.
        $request->merge(['ejercicios' => array_values(array_filter(
            (array) $request->input('ejercicios', []),
            fn ($e) => is_array($e) && trim((string) ($e['ejercicio'] ?? '')) !== ''
        ))]);

        return $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'objetivo' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'ejercicios' => ['required', 'array', 'min:1', 'max:80'],
            'ejercicios.*.dia' => ['required', 'string', 'max:30'],
            'ejercicios.*.ejercicio' => ['required', 'string', 'max:100'],
            'ejercicios.*.series' => ['nullable', 'integer', 'min:1', 'max:20'],
            'ejercicios.*.repeticiones' => ['nullable', 'string', 'max:20'],
            'ejercicios.*.descanso' => ['nullable', 'string', 'max:20'],
            'ejercicios.*.notas' => ['nullable', 'string', 'max:200'],
        ], [
            'ejercicios.required' => 'Cargá al menos un ejercicio.',
        ]);
    }

    protected function guardarEjercicios(Rutina $rutina, array $ejercicios): void
    {
        foreach (array_values($ejercicios) as $i => $e) {
            $rutina->ejercicios()->create([
                'dia' => trim($e['dia']),
                'ejercicio' => trim($e['ejercicio']),
                'series' => $e['series'] ?? null,
                'repeticiones' => $e['repeticiones'] ?? null,
                'descanso' => $e['descanso'] ?? null,
                'notas' => $e['notas'] ?? null,
                'orden' => $i,
            ]);
        }
    }

    /** Tres filas vacías por cada día que pidió el socio. */
    protected function filasIniciales(int $dias): array
    {
        $filas = [];
        for ($d = 1; $d <= max(1, $dias); $d++) {
            for ($i = 0; $i < 3; $i++) {
                $filas[] = ['dia' => "Día {$d}", 'ejercicio' => '', 'series' => null, 'repeticiones' => '', 'descanso' => '', 'notas' => ''];
            }
        }

        return $filas;
    }
}
