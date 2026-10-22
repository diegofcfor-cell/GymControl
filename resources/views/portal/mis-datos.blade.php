<x-layouts.portal titulo="Mis datos">
<h1 class="mb-1 text-2xl font-bold">Mis datos</h1>
<p class="mb-4 text-sm text-slate-500">Estos son los datos personales que el gimnasio tiene sobre vos (<a href="{{ route('privacidad') }}" class="underline">aviso de privacidad</a>).</p>

<section class="mb-6 rounded-xl bg-white p-5 shadow-sm">
    <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
        <div><dt class="text-xs uppercase text-slate-500">Nombre y apellido</dt><dd class="font-medium">{{ $socio->nombre_completo }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">DNI</dt><dd class="font-medium">{{ $socio->dni }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Teléfono</dt><dd class="font-medium">{{ $socio->telefono ?: '—' }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Correo electrónico</dt><dd class="font-medium">{{ $socio->email ?: '—' }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Membresía</dt><dd class="font-medium">{{ $socio->membresia->nombre }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Socio desde</dt><dd class="font-medium">{{ $socio->fecha_alta?->format('d/m/Y') ?? '—' }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-500">Consentimiento</dt>
            <dd class="font-medium">{{ $socio->dioConsentimiento() ? 'Aceptaste el aviso de privacidad el '.$socio->consentimiento_datos_at->format('d/m/Y') : 'No registrado' }}</dd></div>
    </dl>
    <p class="mt-3 text-xs text-slate-500">Tus pagos, asistencias y aptos físicos están en las otras secciones del portal.</p>
</section>

<section class="mb-6 rounded-xl bg-white p-5 shadow-sm">
    <h2 class="font-semibold">Pedir una corrección o la eliminación de tus datos</h2>
    <p class="mb-4 text-sm text-slate-500">Es tu derecho según el art. 16 de la Ley 25.326. Te respondemos acá dentro de los {{ \App\Models\SolicitudDatos::PLAZO_DIAS_HABILES }} días hábiles.</p>
    <form method="POST" action="{{ route('portal.mis-datos.solicitar') }}" class="grid gap-4">
        @csrf
        <div>
            <label for="tipo" class="mb-1 block text-sm font-medium text-slate-700">Qué querés pedir</label>
            <select id="tipo" name="tipo" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="rectificacion" @selected(old('tipo') === 'rectificacion')>Rectificación: corregir o actualizar un dato</option>
                <option value="supresion" @selected(old('tipo') === 'supresion')>Supresión: eliminar mis datos</option>
            </select>
            @error('tipo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="detalle" class="mb-1 block text-sm font-medium text-slate-700">Detalle</label>
            <textarea id="detalle" name="detalle" rows="3" maxlength="1000" required placeholder="Ej.: mi teléfono ahora es 370 4123456."
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300">{{ old('detalle') }}</textarea>
            @error('detalle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <p class="text-xs text-slate-500">La supresión no alcanza al historial de pagos que el gimnasio debe conservar por obligación legal.</p>
        <div><button class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700">Enviar pedido</button></div>
    </form>
</section>

<section class="rounded-xl bg-white p-5 shadow-sm">
    <h2 class="mb-3 font-semibold">Mis pedidos</h2>
    <ul class="space-y-3 text-sm">
        @forelse ($solicitudes as $s)
            <li class="rounded-lg border border-slate-200 p-3">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <span class="font-medium">{{ $tipos[$s->tipo] }} · {{ $s->created_at->format('d/m/Y') }}</span>
                    @if ($s->estado === 'resuelta')
                        <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800">Respondido el {{ $s->resuelta_at->format('d/m/Y') }}</span>
                    @else
                        <span class="rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-800">En curso · respuesta antes del {{ $s->plazo()->format('d/m/Y') }}</span>
                    @endif
                </div>
                <p class="mt-1 whitespace-pre-line text-slate-600">{{ $s->detalle }}</p>
                @if ($s->respuesta)<p class="mt-2 whitespace-pre-line rounded-lg bg-slate-50 p-2 text-slate-700"><span class="font-medium">Respuesta:</span> {{ $s->respuesta }}</p>@endif
            </li>
        @empty
            <li class="text-slate-500">Todavía no hiciste pedidos.</li>
        @endforelse
    </ul>
</section>
</x-layouts.portal>
