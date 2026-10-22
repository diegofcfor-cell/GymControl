<x-layouts.app titulo="Mi contraseña">
<h1 class="mb-4 text-2xl font-bold">Cambiar mi contraseña</h1>
<form method="POST" action="{{ route('cuenta.clave.update') }}" class="grid max-w-md gap-4 rounded-xl bg-white p-6 shadow-sm">
    @csrf @method('PUT')
    <x-input name="clave_actual" label="Contraseña actual" type="password" required autocomplete="current-password" />
    <x-input name="password" label="Nueva contraseña" type="password" required autocomplete="new-password" ayuda="Mínimo 8 caracteres, con letras y números." />
    <x-input name="password_confirmation" label="Repetir la nueva contraseña" type="password" required autocomplete="new-password" />
    <p class="text-xs text-slate-500">Al guardar se cierran las demás sesiones abiertas con tu usuario (por ejemplo, en otra computadora).</p>
    <div class="flex gap-3"><button class="rounded-lg bg-slate-900 px-5 py-2 text-sm text-white">Guardar</button><a href="{{ route('inicio') }}" class="rounded-lg border px-5 py-2 text-sm">Cancelar</a></div>
</form>
</x-layouts.app>
