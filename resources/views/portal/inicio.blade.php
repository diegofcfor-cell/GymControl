<x-layouts.portal>
<div class="mb-4">
    <h1 class="text-2xl font-bold">Hola, {{ $socio->nombre }}</h1>
    <p class="text-sm text-slate-500">
        @if ($dentro) Estás dentro del gimnasio ahora. @else {{ $visitasMes }} {{ $visitasMes === 1 ? 'visita' : 'visitas' }} y {{ $horasMes }} h este mes. @endif
    </p>
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs uppercase text-slate-500">Membresía</p>
        <p class="mt-1 text-lg font-semibold">{{ $socio->membresia->nombre }}</p>
        <p class="text-xs text-slate-500">{{ $socio->membresia->horas_diarias_max ? $socio->membresia->horas_diarias_max.' h diarias' : 'Pase libre' }}</p></div>
    <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs uppercase text-slate-500">Cuota</p>
        @if ($socio->cuotaAlDia())<p class="mt-1 text-lg font-semibold text-emerald-600">Al día</p>@else<p class="mt-1 text-lg font-semibold text-red-600">Vencida</p>@endif
        <p class="text-xs text-slate-500">Vence: {{ $socio->fecha_vencimiento?->format('d/m/Y') ?? 'sin pagos' }}</p></div>
    <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs uppercase text-slate-500">Apto físico</p>
        @if ($apto?->estaVigente())<p class="mt-1 text-lg font-semibold text-emerald-600">Vigente</p>@else<p class="mt-1 text-lg font-semibold text-red-600">{{ $apto ? 'Vencido' : 'No presentado' }}</p>@endif
        <p class="text-xs text-slate-500">{{ $apto ? 'Vence: '.$apto->fecha_vencimiento->format('d/m/Y') : 'Presentalo en recepción' }}</p></div>
</div>

@unless ($socio->cuotaAlDia() && $apto?->estaVigente())
    <p class="mt-4 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800">
        Con la cuota vencida o sin apto físico vigente, la pantalla de acceso no te va a dejar ingresar. Regularizalo en recepción.
    </p>
@endunless

<section class="mt-6 rounded-xl bg-white p-5 shadow-sm">
    <h2 class="mb-3 font-semibold">Mis últimas asistencias</h2>
    @if ($asistencias->isEmpty())
        <p class="text-sm text-slate-500">Todavía no registrás asistencias.</p>
    @else
        <table class="w-full text-sm">
            <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="py-1">Fecha</th><th>Ingreso</th><th>Egreso</th><th>Permanencia</th></tr></thead>
            <tbody class="divide-y">
            @foreach ($asistencias as $a)
                <tr><td class="py-1.5">{{ $a->fecha->format('d/m/Y') }}</td><td>{{ substr($a->hora_ingreso, 0, 5) }}</td>
                    <td>{{ $a->hora_salida ? substr($a->hora_salida, 0, 5) : '—' }}</td><td>{{ $a->permanenciaTexto() }}</td></tr>
            @endforeach
            </tbody>
        </table>
    @endif
</section>
</x-layouts.portal>
