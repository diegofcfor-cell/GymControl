<x-layouts.app titulo="Recepción">
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold">Recepción</h1>
    <a href="{{ route('acceso') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Abrir pantalla de acceso</a>
</div>

<form method="GET" action="{{ route('socios.index') }}" class="mb-6 flex gap-2 rounded-xl bg-white p-4 shadow-sm">
    <label for="q" class="sr-only">Buscar socio</label>
    <input id="q" name="q" type="search" autofocus placeholder="Buscar socio por nombre, DNI o PIN"
           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Buscar</button>
</form>

<div class="grid gap-6 lg:grid-cols-2">
    <section class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-baseline justify-between">
            <h2 class="font-semibold">Socios en sala</h2>
            <span class="text-3xl font-bold text-blue-600">{{ $presentes->count() }}</span>
        </div>
        <ul class="divide-y text-sm">
            @forelse ($presentes as $a)
                <li class="flex justify-between py-1.5"><a class="hover:underline" href="{{ route('socios.show', $a->socio) }}">{{ $a->socio->nombre_completo }}</a><span class="text-slate-500">desde {{ substr($a->hora_ingreso, 0, 5) }}</span></li>
            @empty
                <li class="py-4 text-center text-slate-400">Nadie en el gimnasio.</li>
            @endforelse
        </ul>
    </section>

    <section class="rounded-xl bg-white p-5 shadow-sm">
        <h2 class="mb-3 font-semibold">Últimos accesos de hoy</h2>
        @include('recepcion.ultimos-accesos', ['accesos' => $ultimosAccesos])
    </section>
</div>
</x-layouts.app>
