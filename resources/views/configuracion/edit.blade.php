<x-layouts.app titulo="Configuración">
<h1 class="mb-4 text-2xl font-bold">Configuración del gimnasio</h1>
<form method="POST" action="{{ route('configuracion.update') }}" class="grid max-w-2xl gap-4 rounded-xl bg-white p-6 shadow-sm">
    @csrf @method('PUT')
    <x-input name="capacidad_maxima" label="Capacidad máxima del local (personas)" type="number" min="1" max="5000" :value="$capacidad" required
             ayuda="Se usa para el indicador de aforo que ven los socios en su portal." />
    <div><button class="rounded-lg bg-slate-900 px-5 py-2 text-sm text-white">Guardar</button></div>
</form>
</x-layouts.app>
