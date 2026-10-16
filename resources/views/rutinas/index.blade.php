<x-layouts.app titulo="Rutinas">
<h1 class="mb-4 text-2xl font-bold">Rutinas</h1>

<section class="mb-6">
    <h2 class="mb-2 font-semibold">Pedidos pendientes <span class="text-sm font-normal text-slate-500">({{ $pendientes->count() }})</span></h2>
    <div class="grid gap-3 md:grid-cols-2">
        @forelse ($pendientes as $s)
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold">{{ $s->socio->nombre_completo }}</p>
                        <p class="text-sm text-slate-600">{{ $s->objetivo }} · {{ $s->dias_por_semana }} días/semana · {{ \App\Models\SolicitudRutina::NIVELES[$s->nivel] }}</p>
                        <p class="text-xs text-slate-500">Pedido el {{ $s->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <a href="{{ route('rutinas.create', $s) }}" class="whitespace-nowrap rounded-lg bg-slate-900 px-3 py-1.5 text-sm text-white">Armar rutina</a>
                </div>
                @if ($s->comentarios)<p class="mt-2 whitespace-pre-line rounded-lg bg-amber-50 p-2 text-sm text-amber-900">{{ $s->comentarios }}</p>@endif
            </div>
        @empty
            <p class="rounded-xl bg-white p-6 text-center text-sm text-slate-500 shadow-sm md:col-span-2">No hay pedidos pendientes.</p>
        @endforelse
    </div>
</section>

<section>
    <h2 class="mb-2 font-semibold">Rutinas recientes</h2>
    <div class="overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">Socio</th><th>Rutina</th><th>Ejercicios</th><th>Profesor</th><th>Estado</th><th>Actualizada</th><th></th></tr></thead><tbody>
    @forelse ($rutinas as $r)
        <tr class="border-t border-slate-200"><td class="px-4 py-2 font-medium">{{ $r->socio->nombre_completo }}</td><td>{{ $r->nombre }}</td><td>{{ $r->ejercicios_count }}</td><td>{{ $r->profesor->name }}</td>
            <td>{{ $r->activa ? 'Vigente' : 'Reemplazada' }}</td><td>{{ $r->updated_at->format('d/m/Y') }}</td>
            <td class="pr-4 text-right"><a class="text-blue-700 hover:underline" href="{{ route('rutinas.edit', $r) }}">Editar</a></td></tr>
    @empty
        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">Todavía no hay rutinas.</td></tr>
    @endforelse
    </tbody></table></div>
</section>
</x-layouts.app>
