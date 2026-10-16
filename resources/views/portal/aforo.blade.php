<x-layouts.portal titulo="Aforo">
<h1 class="mb-4 text-2xl font-bold">¿Cuánta gente hay?</h1>

<livewire:aforo-en-vivo />

<section class="mt-6 rounded-xl bg-white p-5 shadow-sm">
    <h2 class="font-semibold">Horas pico</h2>
    <p class="mb-3 text-sm text-slate-500">Promedio de personas por hora en las últimas {{ \App\Services\AforoService::SEMANAS_HISTORIAL }} semanas.</p>

    <div class="mb-4 flex flex-wrap gap-1">
        @foreach ($dias as $n => $nombre)
            <a href="{{ route('portal.aforo', ['dia' => $n]) }}"
               class="rounded-lg px-3 py-1 text-sm {{ $n === $dia ? 'bg-slate-900 text-white' : 'border bg-white hover:bg-slate-50' }}">{{ $nombre }}</a>
        @endforeach
    </div>

    @if (array_sum($horas) == 0)
        <p class="text-sm text-slate-500">Todavía no hay datos suficientes para este día.</p>
    @else
        <div class="flex h-40 items-end gap-1" aria-label="Personas promedio por hora">
            @foreach ($horas as $hora => $promedio)
                @php $pct = $promedio * 100 / $capacidad; @endphp
                <div class="flex flex-1 flex-col items-center justify-end gap-1" title="{{ $hora }}:00 · {{ $promedio }} personas">
                    <div class="w-full rounded-t {{ $pct >= 80 ? 'bg-red-500' : ($pct >= 50 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                         style="height: {{ max(2, ($promedio / $maxHoras) * 140) }}px"></div>
                    <span class="text-[10px] text-slate-500">{{ $hora }}</span>
                </div>
            @endforeach
        </div>
        <p class="mt-2 text-xs text-slate-500">Verde: tranquilo · Amarillo: moderado · Rojo: muy concurrido (respecto de una capacidad de {{ $capacidad }} personas).</p>
    @endif
</section>
</x-layouts.portal>
