<x-layouts.app titulo="Asistencias">
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold">Historial de asistencias</h1>
    <a href="{{ route('asistencias.pdf', request()->query()) }}" target="_blank" class="rounded-lg border bg-white px-4 py-2 text-sm">Imprimir PDF</a>
</div>
<form method="GET" class="mb-4 grid gap-3 rounded-xl bg-white p-4 shadow-sm sm:grid-cols-4">
    @if (request('socio'))<input type="hidden" name="socio" value="{{ request('socio') }}">@endif
    <x-input name="q" label="Socio (nombre, DNI o PIN)" :value="request('q')" />
    <x-input name="desde" label="Desde" type="date" :value="request('desde')" />
    <x-input name="hasta" label="Hasta" type="date" :value="request('hasta')" />
    <div class="flex items-end"><button class="w-full rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Filtrar</button></div>
</form>
<div class="overflow-x-auto rounded-xl bg-white shadow-sm">
<table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">Fecha</th><th>Socio</th><th>Ingreso</th><th>Egreso</th><th>Permanencia</th></tr></thead><tbody>
@forelse ($asistencias as $a)
    <tr class="border-t"><td class="px-4 py-2">{{ $a->fecha->format('d/m/Y') }}</td><td><a class="text-blue-700 hover:underline" href="{{ route('socios.show', $a->socio) }}">{{ $a->socio->nombre_completo }}</a></td><td>{{ substr($a->hora_ingreso, 0, 5) }}</td>
        <td>{{ $a->hora_salida ? substr($a->hora_salida, 0, 5) : ($a->fecha->isToday() ? 'en el gimnasio' : 'sin egreso') }}</td><td>{{ $a->permanenciaTexto() }}</td></tr>
@empty
    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No hay asistencias para los filtros elegidos.</td></tr>
@endforelse
</tbody></table>
</div>
<div class="mt-4">{{ $asistencias->links() }}</div>
</x-layouts.app>
