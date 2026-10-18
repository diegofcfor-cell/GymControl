<x-layouts.app :titulo="$plantilla['codigo']">
<a href="{{ route('rutinas.plantillas') }}" class="text-sm text-blue-700 hover:underline">← Biblioteca de rutinas</a>
<h1 class="mt-2 text-2xl font-bold">{{ $plantilla['nombre'] }}</h1>
<p class="mb-3 text-sm text-slate-500">{{ $plantilla['objetivo'] }} · {{ $plantilla['dias'] }} días por semana · {{ \App\Models\SolicitudRutina::NIVELES[$plantilla['nivel']] }}</p>
<p class="mb-2 max-w-4xl text-slate-700">{{ $plantilla['resumen'] }}</p>
<p class="mb-4 max-w-4xl whitespace-pre-line rounded-xl bg-amber-50 p-3 text-sm text-amber-900">{{ $plantilla['observaciones'] }}</p>

@if ($pendientes->isNotEmpty())
    <div class="mb-4 max-w-4xl rounded-xl bg-white p-4 shadow-sm">
        <p class="mb-2 text-sm font-medium">Usarla para un pedido pendiente:</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($pendientes as $s)
                <a href="{{ route('rutinas.create', [$s, 'plantilla' => $plantilla['codigo']]) }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm text-white">{{ $s->socio->nombre_completo }} · {{ $s->objetivo }}</a>
            @endforeach
        </div>
    </div>
@endif

<div class="grid max-w-5xl gap-3 md:grid-cols-2">
    @foreach ($plantilla['semana'] as $dia => $ejercicios)
        <section class="rounded-xl bg-white p-4 shadow-sm">
            <h2 class="mb-2 font-semibold">{{ $dia }}</h2>
            <ol class="space-y-1.5 text-sm">
                @foreach ($ejercicios as [$ejercicio, $series, $reps, $descanso, $notas])
                    <li class="flex justify-between gap-3 border-t border-slate-100 pt-1.5 first:border-0 first:pt-0">
                        <span>{{ $ejercicio }}@if ($notas)<span class="block text-xs text-slate-500">{{ $notas }}</span>@endif</span>
                        <span class="whitespace-nowrap text-slate-600">{{ $series }} × {{ $reps }}@if ($descanso)<span class="block text-right text-xs text-slate-400">{{ $descanso }}</span>@endif</span>
                    </li>
                @endforeach
            </ol>
        </section>
    @endforeach
</div>
</x-layouts.app>
