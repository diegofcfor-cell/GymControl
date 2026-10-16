@php
    $estilos = [
        'libre' => ['bg-emerald-500', 'text-emerald-700', 'Tranquilo', 'Buen momento para venir.'],
        'moderado' => ['bg-amber-400', 'text-amber-700', 'Moderado', 'Hay gente, pero se puede entrenar.'],
        'lleno' => ['bg-red-500', 'text-red-700', 'Muy concurrido', 'Puede haber espera en las máquinas.'],
    ];
    [$barra, $texto, $etiqueta, $consejo] = $estilos[$nivel];
@endphp
<div wire:poll.30s class="rounded-xl bg-white p-5 shadow-sm">
    <div class="flex items-baseline justify-between gap-3">
        <p class="text-xs uppercase text-slate-500">Ahora en el gimnasio</p>
        <span class="text-sm font-semibold {{ $texto }}">{{ $etiqueta }}</span>
    </div>
    <p class="mt-1"><span class="text-4xl font-bold">{{ $presentes }}</span> <span class="text-slate-500">de {{ $capacidad }} personas</span></p>
    <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
        <div class="h-full rounded-full {{ $barra }}" style="width: {{ min(100, $porcentaje) }}%"></div>
    </div>
    <p class="mt-2 text-sm text-slate-600">{{ $consejo }} <span class="text-xs text-slate-400">Actualizado {{ now()->format('H:i') }}</span></p>
</div>
