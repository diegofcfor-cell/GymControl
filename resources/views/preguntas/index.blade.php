<x-layouts.app titulo="Preguntas frecuentes">
<div class="mb-4 flex items-center justify-between"><h1 class="text-2xl font-bold">Preguntas frecuentes</h1><a href="{{ route('preguntas.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">+ Nueva pregunta</a></div>
<div class="overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">Orden</th><th>Pregunta</th><th>Palabras clave</th><th>Estado</th><th></th></tr></thead><tbody>
@forelse ($preguntas as $p)
<tr class="border-t"><td class="px-4 py-2">{{ $p->orden }}</td><td class="font-medium">{{ $p->pregunta }}</td><td class="text-slate-500">{{ $p->palabras_clave ?: '—' }}</td><td>{{ $p->activa ? 'Visible' : 'Oculta' }}</td>
    <td class="whitespace-nowrap pr-4 text-right"><a class="text-blue-700 hover:underline" href="{{ route('preguntas.edit', $p) }}">Editar</a>
        <form method="POST" action="{{ route('preguntas.destroy', $p) }}" class="inline" onsubmit="return confirm('¿Eliminar esta pregunta?')">@csrf @method('DELETE')<button class="ml-2 text-red-600 hover:underline">Eliminar</button></form></td></tr>
@empty
<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Todavía no hay preguntas cargadas.</td></tr>
@endforelse
</tbody></table></div>
<p class="mt-3 text-xs text-slate-500">Los socios las ven en su portal y el chatbot las usa para responder. Las consultas sobre planes, precios y cuánta gente hay se responden solas con los datos del sistema.</p>
</x-layouts.app>
