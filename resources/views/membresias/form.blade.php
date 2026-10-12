<x-layouts.app titulo="Membresía">
<h1 class="mb-4 text-2xl font-bold">{{ $membresia->exists ? 'Editar membresía' : 'Nueva membresía' }}</h1>
<form method="POST" action="{{ $membresia->exists ? route('membresias.update', $membresia) : route('membresias.store') }}" class="grid max-w-2xl gap-4 rounded-xl bg-white p-6 shadow-sm sm:grid-cols-2">
    @csrf @if ($membresia->exists) @method('PUT') @endif
    <x-input name="nombre" label="Nombre del plan" :value="$membresia->nombre" required />
    <x-input name="precio" label="Precio ($)" type="number" step="0.01" min="0" :value="$membresia->precio" required />
    <x-input name="duracion_dias" label="Duración de la cuota (días)" type="number" min="1" :value="$membresia->duracion_dias" required />
    <x-input name="horas_diarias_max" label="Tope de horas diarias" type="number" min="1" max="24" :value="$membresia->horas_diarias_max" ayuda="Dejar vacío para pase libre (Premium)." />
    <div><label for="estado" class="mb-1 block text-sm font-medium">Estado</label>
        <select id="estado" name="estado" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"><option value="activa" @selected(old('estado', $membresia->estado) === 'activa')>Activa</option><option value="inactiva" @selected(old('estado', $membresia->estado) === 'inactiva')>Inactiva</option></select></div>
    <div class="flex items-end gap-3 sm:col-span-2"><button class="rounded-lg bg-slate-900 px-5 py-2 text-sm text-white">Guardar</button><a href="{{ route('membresias.index') }}" class="rounded-lg border px-5 py-2 text-sm">Cancelar</a></div>
</form>
</x-layouts.app>
