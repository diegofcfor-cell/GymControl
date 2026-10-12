<x-layouts.app :titulo="$socio->nombre_completo">
@php $apto = $aptos->first(); @endphp
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold">{{ $socio->nombre_completo }}
            @if ($socio->trashed()) <span class="ml-2 rounded bg-slate-300 px-2 py-0.5 text-xs">BAJA</span> @endif
        </h1>
        <p class="text-sm text-slate-500">DNI {{ $socio->dni }} · {{ $socio->telefono ?: 'sin teléfono' }} · {{ $socio->email ?: 'sin correo' }}</p>
    </div>
    <div class="flex flex-wrap gap-2 text-sm">
        @unless ($socio->trashed())
            <a href="{{ route('socios.edit', $socio) }}" class="rounded-lg border bg-white px-3 py-1.5">Editar</a>
            <a href="{{ route('socios.credencial', $socio) }}" target="_blank" class="rounded-lg border bg-white px-3 py-1.5">Credencial PIN/QR</a>
            <a href="{{ route('pagos.create', $socio) }}" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-white">Registrar pago</a>
        @endunless
        <a href="{{ route('socios.ficha', $socio) }}" target="_blank" class="rounded-lg border bg-white px-3 py-1.5">Ficha PDF</a>
        @if (auth()->user()->esAdministrador())
            @if ($socio->trashed())
                <form method="POST" action="{{ route('socios.restore', $socio) }}">@csrf<button class="rounded-lg bg-slate-900 px-3 py-1.5 text-white">Reactivar</button></form>
            @else
                <form method="POST" action="{{ route('socios.destroy', $socio) }}" onsubmit="return confirm('¿Dar de baja a este socio? Se conserva su historial.')">@csrf @method('DELETE')<button class="rounded-lg bg-red-600 px-3 py-1.5 text-white">Dar de baja</button></form>
            @endif
        @endif
    </div>
</div>

<div class="grid gap-4 md:grid-cols-4">
    <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs uppercase text-slate-500">Membresía</p><p class="mt-1 text-lg font-semibold">{{ $socio->membresia->nombre }}</p><p class="text-xs text-slate-500">{{ $socio->membresia->horas_diarias_max ? $socio->membresia->horas_diarias_max.' h diarias' : 'Pase libre' }}</p></div>
    <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs uppercase text-slate-500">Cuota</p>
        @if ($socio->cuotaAlDia())<p class="mt-1 text-lg font-semibold text-emerald-600">Al día</p>@else<p class="mt-1 text-lg font-semibold text-red-600">Vencida</p>@endif
        <p class="text-xs text-slate-500">Vence: {{ $socio->fecha_vencimiento?->format('d/m/Y') ?? 'sin pagos' }}</p></div>
    <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs uppercase text-slate-500">Apto físico</p>
        @if ($socio->aptoVigente())<p class="mt-1 text-lg font-semibold text-emerald-600">Vigente</p>@else<p class="mt-1 text-lg font-semibold text-red-600">{{ $apto ? 'Vencido' : 'No presentado' }}</p>@endif
        <p class="text-xs text-slate-500">{{ $apto ? 'Vence: '.$apto->fecha_vencimiento->format('d/m/Y') : '' }}</p></div>
    <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs uppercase text-slate-500">PIN de acceso</p><p class="mt-1 font-mono text-2xl font-bold tracking-widest">{{ $socio->pin }}</p><p class="text-xs text-slate-500">Alta: {{ $socio->fecha_alta->format('d/m/Y') }} · {{ $socio->operador->name }}</p></div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="rounded-xl bg-white p-5 shadow-sm">
        <h2 class="mb-3 font-semibold">Aptos físicos</h2>
        @unless ($socio->trashed())
        <form method="POST" action="{{ route('aptos.store', $socio) }}" enctype="multipart/form-data" class="mb-4 grid gap-3 rounded-lg bg-slate-50 p-3 sm:grid-cols-2">
            @csrf
            <x-input name="fecha_emision" label="Emisión" type="date" required />
            <x-input name="fecha_vencimiento" label="Vencimiento" type="date" required />
            <x-input name="archivo" label="Certificado (PDF o imagen, máx. 5 MB)" type="file" accept=".pdf,.jpg,.jpeg,.png" class="sm:col-span-2" />
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white sm:col-span-2">Cargar apto físico</button>
        </form>
        @endunless
        <table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-slate-500"><th>Emisión</th><th>Vencimiento</th><th>Estado</th><th></th></tr></thead><tbody>
        @forelse ($aptos as $a)
            <tr class="border-t"><td class="py-1.5">{{ $a->fecha_emision->format('d/m/Y') }}</td><td>{{ $a->fecha_vencimiento->format('d/m/Y') }}</td>
                <td>{!! $a->estaVigente() ? '<span class="text-emerald-600">vigente</span>' : '<span class="text-red-600">vencido</span>' !!}</td>
                <td class="text-right">@if ($a->archivo)<a class="text-blue-600 hover:underline" target="_blank" href="{{ route('aptos.archivo', $a) }}">ver archivo</a>@endif</td></tr>
        @empty
            <tr><td colspan="4" class="py-4 text-center text-slate-400">Sin aptos cargados.</td></tr>
        @endforelse
        </tbody></table>
    </section>

    <section class="rounded-xl bg-white p-5 shadow-sm">
        <h2 class="mb-3 font-semibold">Últimos pagos</h2>
        <table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-slate-500"><th>Fecha</th><th>Plan</th><th>Período</th><th class="text-right">Importe</th></tr></thead><tbody>
        @forelse ($pagos as $p)
            <tr class="border-t"><td class="py-1.5">{{ $p->fecha_pago->format('d/m/Y') }}</td><td>{{ $p->membresia->nombre }}</td><td>{{ $p->periodo_desde->format('d/m') }} → {{ $p->periodo_hasta->format('d/m/Y') }}</td><td class="text-right">$ {{ number_format($p->importe, 2, ',', '.') }}</td></tr>
        @empty
            <tr><td colspan="4" class="py-4 text-center text-slate-400">Sin pagos.</td></tr>
        @endforelse
        </tbody></table>
    </section>

    <section class="rounded-xl bg-white p-5 shadow-sm lg:col-span-2">
        <div class="mb-3 flex items-center justify-between"><h2 class="font-semibold">Últimas asistencias</h2><a class="text-sm text-blue-600 hover:underline" href="{{ route('asistencias.index', ['socio' => $socio->id]) }}">Ver historial completo</a></div>
        <table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-slate-500"><th>Fecha</th><th>Ingreso</th><th>Egreso</th><th>Permanencia</th></tr></thead><tbody>
        @forelse ($asistencias as $a)
            <tr class="border-t"><td class="py-1.5">{{ $a->fecha->format('d/m/Y') }}</td><td>{{ substr($a->hora_ingreso, 0, 5) }}</td><td>{{ $a->hora_salida ? substr($a->hora_salida, 0, 5) : '—' }}</td><td>{{ $a->permanenciaTexto() }}</td></tr>
        @empty
            <tr><td colspan="4" class="py-4 text-center text-slate-400">Sin asistencias.</td></tr>
        @endforelse
        </tbody></table>
    </section>
</div>
</x-layouts.app>
