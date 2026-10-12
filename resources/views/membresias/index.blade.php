<x-layouts.app titulo="Membresías">
<div class="mb-4 flex items-center justify-between"><h1 class="text-2xl font-bold">Membresías</h1><a href="{{ route('membresias.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">+ Nueva membresía</a></div>
<div class="overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">Plan</th><th>Precio</th><th>Duración</th><th>Tope diario</th><th>Socios</th><th>Estado</th><th></th></tr></thead><tbody>
@foreach ($membresias as $m)
<tr class="border-t"><td class="px-4 py-2 font-medium">{{ $m->nombre }}</td><td>$ {{ number_format($m->precio, 2, ',', '.') }}</td><td>{{ $m->duracion_dias }} días</td><td>{{ $m->horas_diarias_max ? $m->horas_diarias_max.' h' : 'Pase libre' }}</td><td>{{ $m->socios_count }}</td><td>{{ $m->estado }}</td><td class="pr-4 text-right"><a class="text-blue-700 hover:underline" href="{{ route('membresias.edit', $m) }}">Editar</a></td></tr>
@endforeach
</tbody></table></div>
<p class="mt-3 text-xs text-slate-500">Cambiar el precio no altera los pagos ya registrados (cada pago guarda el importe cobrado).</p>
</x-layouts.app>
