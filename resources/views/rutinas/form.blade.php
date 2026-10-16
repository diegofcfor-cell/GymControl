<x-layouts.app titulo="Rutina">
<h1 class="text-2xl font-bold">{{ $rutina->exists ? 'Editar rutina' : 'Armar rutina' }}</h1>
<p class="mb-4 text-sm text-slate-500">Para {{ $socio->nombre_completo }} · {{ $socio->membresia->nombre }}</p>

@if ($solicitud)
    <div class="mb-4 max-w-4xl rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
        <p><strong>Pedido del socio:</strong> {{ $solicitud->objetivo }} · {{ $solicitud->dias_por_semana }} días por semana · {{ \App\Models\SolicitudRutina::NIVELES[$solicitud->nivel] }}</p>
        @if ($solicitud->comentarios)<p class="mt-1 whitespace-pre-line">{{ $solicitud->comentarios }}</p>@endif
    </div>
@endif

@php $filas = old('ejercicios', $ejercicios); @endphp
<form method="POST" action="{{ $rutina->exists ? route('rutinas.update', $rutina) : route('rutinas.store', $solicitud) }}" class="max-w-4xl space-y-4"
      x-data="{ filas: @js(array_values($filas)), agregar(dia) { this.filas.push({ dia: dia || (this.filas.at(-1)?.dia ?? 'Día 1'), ejercicio: '', series: null, repeticiones: '', descanso: '', notas: '' }) } }">
    @csrf @if ($rutina->exists) @method('PUT') @endif
    <div class="grid gap-4 rounded-xl bg-white p-5 shadow-sm sm:grid-cols-2">
        <x-input name="nombre" label="Nombre de la rutina" :value="$rutina->nombre" maxlength="100" required />
        <x-input name="objetivo" label="Objetivo" :value="$rutina->objetivo" maxlength="100" />
        <div class="sm:col-span-2">
            <label for="observaciones" class="mb-1 block text-sm font-medium text-slate-700">Indicaciones generales</label>
            <textarea id="observaciones" name="observaciones" rows="3" maxlength="2000" placeholder="Entrada en calor, progresión, cuidados…"
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm">{{ old('observaciones', $rutina->observaciones) }}</textarea>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-semibold">Ejercicios</h2>
            <p class="text-xs text-slate-500">Las filas sin ejercicio no se guardan.</p>
        </div>
        @error('ejercicios')<p class="mb-2 text-sm text-red-600">{{ $message }}</p>@enderror
        <div class="hidden grid-cols-12 gap-2 px-1 text-xs font-medium uppercase text-slate-500 md:grid">
            <span class="col-span-2">Día</span><span class="col-span-3">Ejercicio</span><span>Series</span><span class="col-span-2">Repeticiones</span><span>Descanso</span><span class="col-span-2">Notas</span><span></span>
        </div>
        <template x-for="(f, i) in filas" :key="i">
            <div class="grid grid-cols-2 gap-2 border-t border-slate-100 py-2 md:grid-cols-12 md:border-0 md:py-1">
                <input :name="`ejercicios[${i}][dia]`" x-model="f.dia" placeholder="Día" aria-label="Día" maxlength="30" class="col-span-1 rounded-lg border border-slate-300 px-2 py-1.5 text-sm md:col-span-2">
                <input :name="`ejercicios[${i}][ejercicio]`" x-model="f.ejercicio" placeholder="Ejercicio" aria-label="Ejercicio" maxlength="100" class="col-span-1 rounded-lg border border-slate-300 px-2 py-1.5 text-sm md:col-span-3">
                <input :name="`ejercicios[${i}][series]`" x-model="f.series" type="number" min="1" max="20" placeholder="Series" aria-label="Series" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                <input :name="`ejercicios[${i}][repeticiones]`" x-model="f.repeticiones" placeholder="10-12" aria-label="Repeticiones" maxlength="20" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm md:col-span-2">
                <input :name="`ejercicios[${i}][descanso]`" x-model="f.descanso" placeholder="60 s" aria-label="Descanso" maxlength="20" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                <input :name="`ejercicios[${i}][notas]`" x-model="f.notas" placeholder="Notas" aria-label="Notas" maxlength="200" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm md:col-span-2">
                <button type="button" x-on:click="filas.splice(i, 1)" class="rounded-lg px-2 text-sm text-red-600 hover:bg-red-50" aria-label="Quitar fila">✕</button>
            </div>
        </template>
        <button type="button" x-on:click="agregar()" class="mt-3 rounded-lg border px-3 py-1.5 text-sm hover:bg-slate-50">+ Agregar ejercicio</button>
    </div>

    <div class="flex gap-3">
        <button class="rounded-lg bg-slate-900 px-5 py-2 text-sm text-white">{{ $rutina->exists ? 'Guardar cambios' : 'Guardar y enviar al socio' }}</button>
        <a href="{{ route('rutinas.index') }}" class="rounded-lg border px-5 py-2 text-sm">Cancelar</a>
    </div>
</form>
</x-layouts.app>
