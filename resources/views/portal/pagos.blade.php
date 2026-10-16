<x-layouts.portal titulo="Mis pagos">
<h1 class="mb-1 text-2xl font-bold">Mis pagos</h1>
<p class="mb-4 text-sm text-slate-500">Cuota {{ $socio->cuotaAlDia() ? 'al día' : 'vencida' }} · vence {{ $socio->fecha_vencimiento?->format('d/m/Y') ?? 'sin pagos' }}</p>
<div class="divide-y divide-slate-200 rounded-xl bg-white shadow-sm">
@forelse ($pagos as $p)
    <div class="flex flex-wrap items-center justify-between gap-3 p-4">
        <div>
            <p class="font-medium">{{ $p->membresia->nombre }} · $ {{ number_format($p->importe, 2, ',', '.') }}</p>
            <p class="text-sm text-slate-500">Pagado el {{ $p->fecha_pago->format('d/m/Y') }} · {{ ucfirst($p->metodo_pago) }}</p>
            <p class="text-sm text-slate-500">Cubre del {{ $p->periodo_desde->format('d/m/Y') }} al {{ $p->periodo_hasta->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('portal.pagos.comprobante', $p) }}" target="_blank" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-slate-50">Comprobante PDF</a>
    </div>
@empty
    <p class="p-8 text-center text-slate-400">Todavía no hay pagos registrados.</p>
@endforelse
</div>
<div class="mt-4">{{ $pagos->links() }}</div>
</x-layouts.portal>
