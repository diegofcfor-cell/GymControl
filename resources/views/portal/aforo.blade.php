<x-layouts.portal titulo="Aforo">
<h1 class="mb-4 text-2xl font-bold">¿Cuánta gente hay?</h1>

<livewire:aforo-en-vivo />

@php
    $nombresDia = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo'];
    $tonos = ['bg-slate-100', 'bg-sky-100', 'bg-sky-200', 'bg-sky-400', 'bg-sky-600', 'bg-sky-800'];
    $tono = fn ($p) => $tonos[$p <= 0 ? 0 : min(5, (int) ceil($p / $maxMapa * 5))];
@endphp
<section class="mt-6 rounded-xl bg-white p-5 shadow-sm">
    <h2 class="font-semibold">Semana del gimnasio: días y horarios con más gente</h2>
    <p class="mb-3 text-sm text-slate-500">Cuanto más oscuro, más personas en promedio (últimas {{ \App\Services\AforoService::SEMANAS_HISTORIAL }} semanas). Usalo para organizar tu semana.</p>

    @if (empty($masConcurridas))
        <p class="text-sm text-slate-500">Todavía no hay datos suficientes. El gráfico se completa a medida que los socios registran sus ingresos.</p>
    @else
        <div>
            <table class="w-full table-fixed border-separate border-spacing-px text-[10px] sm:border-spacing-0.5 sm:text-[11px]" aria-label="Personas promedio por día y hora">
                <thead><tr><th class="w-8 sm:w-10"></th>
                    @foreach (array_keys($mapa[1]) as $hora)<th class="font-normal text-slate-500">{{ $hora }}</th>@endforeach
                </tr></thead>
                <tbody>
                @foreach ($nombresDia as $n => $nombre)
                    <tr>
                        <th scope="row" class="whitespace-nowrap pr-1 text-left font-medium text-slate-700">{{ mb_substr($nombre, 0, 3) }}</th>
                        @foreach ($mapa[$n] as $hora => $promedio)
                            <td class="h-6 rounded-sm sm:rounded {{ $tono($promedio) }}" title="{{ $nombre }} {{ $hora }}:00 · {{ $promedio }} personas"><span class="sr-only">{{ $nombre }} {{ $hora }} h: {{ $promedio }}</span></td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-2 flex items-center gap-1 text-xs text-slate-500">
            <span>Menos gente</span>@foreach ($tonos as $t)<span class="h-3 w-5 rounded {{ $t }}"></span>@endforeach<span>Más gente</span>
        </div>

        @if ($masConcurridas)
            <div class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-900">
                <p class="font-medium">Franjas más concurridas</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($masConcurridas as $f)
                        <li>{{ $nombresDia[$f['dia']] }} de {{ $f['hora'] }} a {{ $f['hora'] + 1 }} h · {{ str_replace('.', ',', (string) $f['promedio']) }} personas en promedio</li>
                    @endforeach
                </ul>
                <p class="mt-1 text-xs">Si querés entrenar tranquilo, elegí los horarios más claros del gráfico.</p>
            </div>
        @endif
    @endif
</section>

<section class="mt-6 rounded-xl bg-white p-5 shadow-sm">
    <h2 class="font-semibold">Horas pico por día</h2>
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
