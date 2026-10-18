<x-layouts.app titulo="Biblioteca de rutinas">
<div class="mb-1 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold">Biblioteca de rutinas</h1>
    <a href="{{ route('rutinas.index') }}" class="text-sm text-blue-700 hover:underline">← Volver a pedidos</a>
</div>
<p class="mb-4 text-sm text-slate-500">Rutinas semanales completas, numeradas por objetivo. Elegí una al armar la rutina de un socio y ajustala a su caso.</p>

<nav class="mb-4 flex flex-wrap gap-2 text-sm">
    <a href="{{ route('rutinas.plantillas') }}" @class(['rounded-full px-3 py-1', 'bg-slate-900 text-white' => ! $objetivo, 'bg-white shadow-sm hover:bg-slate-50' => $objetivo])>Todas</a>
    @foreach ($objetivos as $o)
        <a href="{{ route('rutinas.plantillas', ['objetivo' => $o]) }}" @class(['rounded-full px-3 py-1', 'bg-slate-900 text-white' => $objetivo === $o, 'bg-white shadow-sm hover:bg-slate-50' => $objetivo !== $o])>{{ $o }}</a>
    @endforeach
</nav>

<div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
    @forelse ($plantillas as $p)
        <a href="{{ route('rutinas.plantilla', $p['codigo']) }}" class="block rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md">
            <p class="font-semibold">{{ $p['nombre'] }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $p['objetivo'] }} · {{ $p['dias'] }} días/semana · {{ \App\Models\SolicitudRutina::NIVELES[$p['nivel']] }} · {{ \App\Support\PlantillasRutina::cantidadEjercicios($p) }} ejercicios</p>
            <p class="mt-2 text-sm text-slate-700">{{ $p['resumen'] }}</p>
        </a>
    @empty
        <p class="rounded-xl bg-white p-6 text-center text-sm text-slate-500 shadow-sm md:col-span-2 xl:col-span-3">No hay rutinas para ese objetivo.</p>
    @endforelse
</div>
</x-layouts.app>
