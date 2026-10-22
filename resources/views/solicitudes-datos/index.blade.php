<x-layouts.app titulo="Datos personales">
<h1 class="mb-1 text-2xl font-bold">Pedidos sobre datos personales</h1>
<p class="mb-4 text-sm text-slate-500">Rectificación y supresión pedidas por los socios desde su portal (Ley 25.326, art. 16). Hay que responder dentro de los {{ \App\Models\SolicitudDatos::PLAZO_DIAS_HABILES }} días hábiles. Hacé la corrección o la baja desde la ficha del socio y después respondé acá.</p>

<section class="mb-6">
    <h2 class="mb-2 font-semibold">Pendientes <span class="text-sm font-normal text-slate-500">({{ $pendientes->count() }})</span></h2>
    <div class="grid gap-3 md:grid-cols-2">
        @forelse ($pendientes as $s)
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <a href="{{ route('socios.show', $s->socio) }}" class="font-semibold text-blue-700 hover:underline">{{ $s->socio->nombre_completo }}</a>
                    <span class="text-xs {{ $s->estaVencida() ? 'font-semibold text-red-600' : 'text-slate-500' }}">{{ $tipos[$s->tipo] }} · vence el {{ $s->plazo()->format('d/m/Y') }}</span>
                </div>
                <p class="mt-2 whitespace-pre-line rounded-lg bg-amber-50 p-2 text-sm text-amber-900">{{ $s->detalle }}</p>
                <form method="POST" action="{{ route('solicitudes-datos.resolver', $s) }}" class="mt-3 space-y-2">
                    @csrf @method('PUT')
                    <textarea name="respuesta" rows="2" maxlength="1000" required placeholder="Qué se hizo con el pedido…"
                              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"></textarea>
                    <button class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm text-white">Responder y cerrar</button>
                </form>
            </div>
        @empty
            <p class="rounded-xl bg-white p-6 text-center text-sm text-slate-500 shadow-sm md:col-span-2">No hay pedidos pendientes.</p>
        @endforelse
    </div>
    @error('respuesta')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</section>

<section>
    <h2 class="mb-2 font-semibold">Respondidos</h2>
    <div class="overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">Socio</th><th>Tipo</th><th>Pedido</th><th>Respuesta</th><th>Respondió</th><th>Fecha</th></tr></thead><tbody>
    @forelse ($resueltas as $s)
        <tr class="border-t border-slate-200 align-top"><td class="px-4 py-2 font-medium">{{ $s->socio->nombre_completo }}</td><td>{{ $tipos[$s->tipo] }}</td>
            <td class="max-w-xs text-slate-600">{{ $s->detalle }}</td><td class="max-w-xs">{{ $s->respuesta }}</td><td>{{ $s->operador?->name }}</td><td class="pr-4">{{ $s->resuelta_at->format('d/m/Y') }}</td></tr>
    @empty
        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Todavía no hay pedidos respondidos.</td></tr>
    @endforelse
    </tbody></table></div>
</section>
</x-layouts.app>
