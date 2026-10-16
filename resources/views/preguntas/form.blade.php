<x-layouts.app titulo="Pregunta frecuente">
<h1 class="mb-4 text-2xl font-bold">{{ $pregunta->exists ? 'Editar pregunta' : 'Nueva pregunta' }}</h1>
<form method="POST" action="{{ $pregunta->exists ? route('preguntas.update', $pregunta) : route('preguntas.store') }}" class="grid max-w-2xl gap-4 rounded-xl bg-white p-6 shadow-sm">
    @csrf @if ($pregunta->exists) @method('PUT') @endif
    <x-input name="pregunta" label="Pregunta" :value="$pregunta->pregunta" maxlength="200" required />
    <div>
        <label for="respuesta" class="mb-1 block text-sm font-medium text-slate-700">Respuesta</label>
        <textarea id="respuesta" name="respuesta" rows="5" maxlength="2000" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300">{{ old('respuesta', $pregunta->respuesta) }}</textarea>
        @error('respuesta')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <x-input name="palabras_clave" label="Palabras clave" :value="$pregunta->palabras_clave" ayuda="Separadas por coma, por ejemplo: horario, abren, cierran, domingo. Ayudan al chatbot a encontrar esta respuesta." />
    <x-input name="orden" label="Orden" type="number" min="0" max="999" :value="$pregunta->orden" />
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="activa" value="1" class="rounded" @checked(old('activa', $pregunta->activa))> Visible para los socios</label>
    <div class="flex gap-3"><button class="rounded-lg bg-slate-900 px-5 py-2 text-sm text-white">Guardar</button><a href="{{ route('preguntas.index') }}" class="rounded-lg border px-5 py-2 text-sm">Cancelar</a></div>
</form>
</x-layouts.app>
