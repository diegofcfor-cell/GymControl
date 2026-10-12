<x-layouts.app :titulo="$socio->exists ? 'Editar socio' : 'Nuevo socio'">
<h1 class="mb-4 text-2xl font-bold">{{ $socio->exists ? 'Editar socio' : 'Registrar nuevo socio' }}</h1>
<form method="POST" action="{{ $socio->exists ? route('socios.update', $socio) : route('socios.store') }}" class="grid max-w-3xl gap-4 rounded-xl bg-white p-6 shadow-sm sm:grid-cols-2">
    @csrf
    @if ($socio->exists) @method('PUT') @endif
    <x-input name="nombre" label="Nombre" :value="$socio->nombre" required />
    <x-input name="apellido" label="Apellido" :value="$socio->apellido" required />
    <x-input name="dni" label="DNI" :value="$socio->dni" inputmode="numeric" required ayuda="Solo números, sin puntos." />
    <x-input name="telefono" label="Teléfono" :value="$socio->telefono" />
    <x-input name="email" label="Correo electrónico" type="email" :value="$socio->email" class="sm:col-span-2" />
    <div class="sm:col-span-2">
        <label for="membresia_id" class="mb-1 block text-sm font-medium text-slate-700">Membresía</label>
        <select id="membresia_id" name="membresia_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
            @foreach ($membresias as $m)
                <option value="{{ $m->id }}" @selected(old('membresia_id', $socio->membresia_id) == $m->id)>
                    {{ $m->nombre }} — $ {{ number_format($m->precio, 2, ',', '.') }} · {{ $m->horas_diarias_max ? $m->horas_diarias_max.' h diarias' : 'pase libre' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-3 sm:col-span-2">
        <button class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700">Guardar</button>
        <a href="{{ $socio->exists ? route('socios.show', $socio) : route('socios.index') }}" class="rounded-lg border px-5 py-2 text-sm">Cancelar</a>
    </div>
</form>
</x-layouts.app>
