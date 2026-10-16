<x-layouts.portal titulo="Apto médico">
<h1 class="mb-4 text-2xl font-bold">Constancias médicas</h1>
@php $vigente = $aptos->first(fn ($a) => $a->estaVigente()); @endphp
<div class="mb-4 rounded-xl p-4 text-sm {{ $vigente ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800' }}">
    @if ($vigente) Tu apto físico está vigente hasta el {{ $vigente->fecha_vencimiento->format('d/m/Y') }}.
    @else No tenés un apto físico vigente. Sin él la pantalla de acceso no te deja ingresar: presentalo en recepción. @endif
</div>
<div class="overflow-x-auto rounded-xl bg-white shadow-sm">
<table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">Emisión</th><th class="pr-3">Vencimiento</th><th class="pr-3">Estado</th><th></th></tr></thead><tbody>
@forelse ($aptos as $a)
    <tr class="border-t border-slate-200"><td class="px-4 py-2">{{ $a->fecha_emision->format('d/m/Y') }}</td><td class="pr-3">{{ $a->fecha_vencimiento->format('d/m/Y') }}</td>
        <td>@if ($a->estaVigente())<span class="font-medium text-emerald-700">Vigente</span>@else<span class="text-slate-500">Vencido</span>@endif</td>
        <td class="px-4 text-right">@if ($a->archivo)<a href="{{ route('portal.aptos.archivo', $a) }}" target="_blank" class="text-blue-700 hover:underline">Ver archivo</a>@else<span class="text-slate-400">Sin archivo</span>@endif</td></tr>
@empty
    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">No hay constancias cargadas.</td></tr>
@endforelse
</tbody></table>
</div>
</x-layouts.portal>
