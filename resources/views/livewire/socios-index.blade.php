<div>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Socios</h1>
        <a href="{{ route('socios.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">+ Nuevo socio</a>
    </div>
    @if (session('ok'))<div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('ok') }}</div>@endif

    <div class="mb-4 flex flex-wrap gap-3">
        <input type="search" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar por nombre, apellido, DNI o PIN…" class="w-full max-w-md rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
        <select wire:model.live="filtro" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="todos">Todos</option>
            <option value="al_dia">Cuota al día</option>
            <option value="vencidos">Cuota vencida</option>
            @if (auth()->user()->esAdministrador())<option value="bajas">Dados de baja</option>@endif
        </select>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">Socio</th><th>DNI</th><th>PIN</th><th>Membresía</th><th>Vencimiento</th><th>Cuota</th></tr></thead>
            <tbody>
            @forelse ($socios as $s)
                <tr class="border-t hover:bg-slate-50" wire:key="socio-{{ $s->id }}">
                    <td class="px-4 py-2"><a class="font-medium text-blue-700 hover:underline" href="{{ route('socios.show', $s) }}">{{ $s->nombre_completo }}</a></td>
                    <td>{{ $s->dni }}</td><td class="font-mono">{{ $s->pin }}</td><td>{{ $s->membresia->nombre }}</td>
                    <td>{{ $s->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                    <td>@if ($s->trashed())<span class="text-slate-500">baja</span>@elseif ($s->cuotaAlDia())<span class="rounded bg-emerald-100 px-2 py-0.5 text-emerald-700">al día</span>@else<span class="rounded bg-red-100 px-2 py-0.5 text-red-700">vencida</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No se encontraron socios.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $socios->links() }}</div>
</div>
