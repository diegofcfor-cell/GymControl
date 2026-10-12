<x-layouts.app titulo="Operadores">
<div class="mb-4 flex items-center justify-between"><h1 class="text-2xl font-bold">Operadores del sistema</h1><a href="{{ route('operadores.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">+ Nuevo operador</a></div>
<div class="overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th></th></tr></thead><tbody>
@foreach ($operadores as $o)
<tr class="border-t"><td class="px-4 py-2 font-medium">{{ $o->name }}</td><td>{{ $o->email }}</td><td>{{ $o->rol }}</td><td>{!! $o->activo ? '<span class="text-emerald-600">activo</span>' : '<span class="text-red-600">inactivo</span>' !!}</td><td class="pr-4 text-right"><a class="text-blue-700 hover:underline" href="{{ route('operadores.edit', $o) }}">Editar</a></td></tr>
@endforeach
</tbody></table></div>
</x-layouts.app>
