<x-layouts.app titulo="Dashboard">
@php $money = fn ($v) => '$ '.number_format($v, 2, ',', '.'); @endphp
<h1 class="mb-4 text-2xl font-bold">Dashboard</h1>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([
        ['Socios con cuota al día', $sociosActivos, 'text-emerald-600'],
        ['Socios con cuota vencida', $sociosVencidos, 'text-red-600'],
        ['Presentes ahora', $presentes, 'text-blue-600'],
        ['Asistencias de hoy', $asistenciasHoy, 'text-slate-900'],
        ['Ingresos de hoy', $money($ingresosHoy), 'text-slate-900'],
        ['Ingresos del mes', $money($ingresosMes), 'text-slate-900'],
        ['Cuotas que vencen en 7 días', $porVencer, 'text-amber-600'],
        ['Activos sin apto físico vigente', $sinApto, 'text-amber-600'],
    ] as [$t, $v, $c])
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">{{ $t }}</p>
            <p class="mt-2 text-3xl font-bold {{ $c }}">{{ $v }}</p>
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="rounded-xl bg-white p-5 shadow-sm">
        <h2 class="mb-4 font-semibold">Asistencias de los últimos 7 días</h2>
        <div class="flex h-44 items-end gap-3">
            @foreach ($serie as $s)
                <div class="flex flex-1 flex-col items-center justify-end gap-1">
                    <span class="text-xs font-medium">{{ $s['total'] }}</span>
                    <div class="w-full rounded-t bg-slate-800" style="height: {{ max(4, ($s['total'] / $maxSerie) * 120) }}px"></div>
                    <span class="text-[10px] text-slate-500">{{ $s['dia'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-xl bg-white p-5 shadow-sm">
        <h2 class="mb-3 font-semibold">Ingresos del mes por membresía</h2>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-xs uppercase text-slate-500"><th class="py-1">Plan</th><th class="text-right">Pagos</th><th class="text-right">Total</th></tr></thead>
            <tbody>
            @forelse ($porMembresia as $plan => $x)
                <tr class="border-t"><td class="py-1.5">{{ $plan }}</td><td class="text-right">{{ $x['cantidad'] }}</td><td class="text-right">{{ $money($x['total']) }}</td></tr>
            @empty
                <tr><td colspan="3" class="py-4 text-center text-slate-400">Sin pagos este mes.</td></tr>
            @endforelse
            </tbody>
        </table>
    </section>

    <section class="rounded-xl bg-white p-5 shadow-sm">
        <h2 class="mb-3 font-semibold">Socios presentes ahora</h2>
        <ul class="divide-y text-sm">
            @forelse ($presentesLista as $a)
                <li class="flex justify-between py-1.5"><a class="hover:underline" href="{{ route('socios.show', $a->socio) }}">{{ $a->socio->nombre_completo }}</a><span class="text-slate-500">desde {{ substr($a->hora_ingreso, 0, 5) }}</span></li>
            @empty
                <li class="py-4 text-center text-slate-400">Nadie en el gimnasio.</li>
            @endforelse
        </ul>
    </section>

    <section class="rounded-xl bg-white p-5 shadow-sm">
        <h2 class="mb-3 font-semibold">Últimos pagos</h2>
        <ul class="divide-y text-sm">
            @forelse ($ultimosPagos as $p)
                <li class="flex justify-between py-1.5"><span>{{ $p->socio->nombre_completo }} · {{ $p->membresia->nombre }}</span><span class="font-medium">{{ $money($p->importe) }}</span></li>
            @empty
                <li class="py-4 text-center text-slate-400">Sin pagos registrados.</li>
            @endforelse
        </ul>
    </section>
</div>
</x-layouts.app>
