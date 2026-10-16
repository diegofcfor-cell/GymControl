<x-layouts.app titulo="Operador">
<h1 class="mb-4 text-2xl font-bold">{{ $operador->exists ? 'Editar operador' : 'Nuevo operador' }}</h1>
<form method="POST" action="{{ $operador->exists ? route('operadores.update', $operador) : route('operadores.store') }}" class="grid max-w-2xl gap-4 rounded-xl bg-white p-6 shadow-sm sm:grid-cols-2">
    @csrf @if ($operador->exists) @method('PUT') @endif
    <x-input name="name" label="Nombre y apellido" :value="$operador->name" required />
    <x-input name="email" label="Correo electrónico" type="email" :value="$operador->email" required />
    <x-input name="password" label="Contraseña" type="password" :required="! $operador->exists" autocomplete="new-password" :ayuda="$operador->exists ? 'Dejar vacío para no cambiarla.' : 'Mínimo 8 caracteres.'" />
    <x-input name="password_confirmation" label="Repetir contraseña" type="password" autocomplete="new-password" />
    <div><label for="rol" class="mb-1 block text-sm font-medium">Rol</label>
        <select id="rol" name="rol" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="recepcionista" @selected(old('rol', $operador->rol) === 'recepcionista')>Recepcionista</option>
            <option value="profesor" @selected(old('rol', $operador->rol) === 'profesor')>Profesor (arma rutinas)</option>
            <option value="administrador" @selected(old('rol', $operador->rol) === 'administrador')>Administrador</option></select></div>
    @if ($operador->exists)
        <label class="flex items-end gap-2 pb-2 text-sm"><input type="checkbox" name="activo" value="1" @checked(old('activo', $operador->activo)) class="rounded"> Usuario activo</label>
    @endif
    <div class="flex gap-3 sm:col-span-2"><button class="rounded-lg bg-slate-900 px-5 py-2 text-sm text-white">Guardar</button><a href="{{ route('operadores.index') }}" class="rounded-lg border px-5 py-2 text-sm">Cancelar</a></div>
</form>
</x-layouts.app>
