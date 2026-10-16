<x-layouts.portal titulo="Mis asistencias">
<h1 class="mb-4 text-2xl font-bold">Mis asistencias</h1>
<form method="GET" class="mb-4 grid gap-3 rounded-xl bg-white p-4 shadow-sm sm:grid-cols-3">
    <x-input name="desde" label="Desde" type="date" :value="request('desde')" />
    <x-input name="hasta" label="Hasta" type="date" :value="request('hasta')" />
    <div class="flex items-end gap-2">
        <button class="flex-1 rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Filtrar</button>
        @if (request('desde') || request('hasta'))<a href="{{ route('portal.asistencias') }}" class="rounded-lg border px-3 py-2 text-sm">Limpiar</a>@endif
    </div>
</form>
<p class="mb-2 text-sm text-slate-500">{{ $asistencias->total() }} {{ $asistencias->total() === 1 ? 'asistencia' : 'asistencias' }} · {{ $horasTotal }} h en total</p>
<div class="overflow-x-auto rounded-xl bg-white shadow-sm">
<table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">Fecha</th><th class="pr-3">Ingreso</th><th class="pr-3">Egreso</th><th class="pr-3">Permanencia</th></tr></thead><tbody>
@forelse ($asistencias as $a)
    <tr class="border-t border-slate-200"><td class="px-4 py-2">{{ $a->fecha->format('d/m/Y') }}</td><td class="pr-3">{{ substr($a->hora_ingreso, 0, 5) }}</td>
        <td>{{ $a->hora_salida ? substr($a->hora_salida, 0, 5) : ($a->fecha->isToday() ? 'en el gimnasio' : 'sin egreso') }}</td><td class="pr-3">{{ $a->permanenciaTexto() }}</td></tr>
@empty
    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">No hay asistencias para las fechas elegidas.</td></tr>
@endforelse
</tbody></table>
</div>
<div class="mt-4">{{ $asistencias->links() }}</div>
</x-layouts.portal>
