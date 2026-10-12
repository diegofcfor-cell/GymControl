<x-layouts.app titulo="Pagos">
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold">Historial de pagos</h1>
    <a href="{{ route('pagos.pdf', request()->query()) }}" target="_blank" class="rounded-lg border bg-white px-4 py-2 text-sm">Imprimir PDF</a>
</div>
<form method="GET" class="mb-4 grid gap-3 rounded-xl bg-white p-4 shadow-sm sm:grid-cols-4">
    <x-input name="q" label="Socio (nombre, DNI o PIN)" :value="request('q')" />
    <x-input name="desde" label="Desde" type="date" :value="request('desde')" />
    <x-input name="hasta" label="Hasta" type="date" :value="request('hasta')" />
    <div class="flex items-end"><button class="w-full rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Filtrar</button></div>
</form>
<div class="overflow-x-auto rounded-xl bg-white shadow-sm">
<table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">Fecha</th><th>Socio</th><th>Plan</th><th>Período</th><th>Método</th><th>Cobró</th><th class="pr-4 text-right">Importe</th></tr></thead><tbody>
@forelse ($pagos as $p)
    <tr class="border-t"><td class="px-4 py-2">{{ $p->fecha_pago->format('d/m/Y') }}</td><td><a class="text-blue-700 hover:underline" href="{{ route('socios.show', $p->socio) }}">{{ $p->socio->nombre_completo }}</a></td><td>{{ $p->membresia->nombre }}</td><td>{{ $p->periodo_desde->format('d/m') }} → {{ $p->periodo_hasta->format('d/m/Y') }}</td><td>{{ ucfirst($p->metodo_pago) }}</td><td>{{ $p->operador->name }}</td><td class="pr-4 text-right">$ {{ number_format($p->importe, 2, ',', '.') }}</td></tr>
@empty
    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No hay pagos para los filtros elegidos.</td></tr>
@endforelse
</tbody>
<tfoot><tr class="border-t bg-slate-50 font-semibold"><td colspan="6" class="px-4 py-2 text-right">Total filtrado</td><td class="pr-4 text-right">$ {{ number_format($total, 2, ',', '.') }}</td></tr></tfoot></table>
</div>
<div class="mt-4">{{ $pagos->links() }}</div>
</x-layouts.app>
