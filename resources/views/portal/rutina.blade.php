<x-layouts.portal titulo="Mi rutina">
<h1 class="mb-4 text-2xl font-bold">Mi rutina</h1>

@if ($rutina)
    <section class="mb-6 rounded-xl bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 class="text-lg font-semibold">{{ $rutina->nombre }}</h2>
            <p class="text-xs text-slate-500">Armada por {{ $rutina->profesor->name }} · {{ $rutina->updated_at->format('d/m/Y') }}</p>
        </div>
        @if ($rutina->objetivo)<p class="text-sm text-slate-600">Objetivo: {{ $rutina->objetivo }}</p>@endif
        @if ($rutina->observaciones)<p class="mt-2 whitespace-pre-line rounded-lg bg-slate-50 p-3 text-sm text-slate-700">{{ $rutina->observaciones }}</p>@endif

        <div class="mt-4 space-y-4">
            @foreach ($rutina->porDia() as $dia => $ejercicios)
                <div>
                    <h3 class="mb-2 font-semibold">{{ $dia }}</h3>
                    <ul class="divide-y divide-slate-200 rounded-lg border border-slate-200">
                        @foreach ($ejercicios as $e)
                            <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-3 py-2 text-sm">
                                <span class="font-medium">{{ $e->ejercicio }}</span>
                                <span class="text-slate-600">
                                    {{ collect([$e->series ? $e->series.' series' : null, $e->repeticiones ? $e->repeticiones.' rep.' : null, $e->descanso ? 'descanso '.$e->descanso : null])->filter()->implode(' · ') }}
                                </span>
                                @if ($e->notas)<span class="w-full text-xs text-slate-500">{{ $e->notas }}</span>@endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>
@endif

@if ($pendiente)
    <div class="rounded-xl bg-amber-50 p-5 text-sm text-amber-800">
        <p class="font-semibold">Tu pedido está en curso</p>
        <p>Lo enviaste el {{ $pendiente->created_at->format('d/m/Y') }}: {{ $pendiente->objetivo }}, {{ $pendiente->dias_por_semana }} días por semana, nivel {{ strtolower($niveles[$pendiente->nivel]) }}. Un profesor te va a armar la rutina y la vas a ver acá.</p>
    </div>
@else
    <section class="rounded-xl bg-white p-5 shadow-sm">
        <h2 class="font-semibold">{{ $rutina ? 'Pedir una rutina nueva' : 'Pedí tu rutina' }}</h2>
        <p class="mb-4 text-sm text-slate-500">Contanos qué buscás y un profesor te arma una rutina personalizada.</p>
        <form method="POST" action="{{ route('portal.rutina.solicitar') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <label for="objetivo" class="mb-1 block text-sm font-medium text-slate-700">Objetivo</label>
                <select id="objetivo" name="objetivo" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    @foreach ($objetivos as $o)<option value="{{ $o }}" @selected(old('objetivo') === $o)>{{ $o }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="nivel" class="mb-1 block text-sm font-medium text-slate-700">Experiencia</label>
                <select id="nivel" name="nivel" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    @foreach ($niveles as $valor => $texto)<option value="{{ $valor }}" @selected(old('nivel') === $valor)>{{ $texto }}</option>@endforeach
                </select>
            </div>
            <x-input name="dias_por_semana" label="¿Cuántos días por semana venís?" type="number" min="1" max="7" value="3" required />
            <div class="sm:col-span-2">
                <label for="comentarios" class="mb-1 block text-sm font-medium text-slate-700">Comentarios para el profesor</label>
                <textarea id="comentarios" name="comentarios" rows="3" maxlength="1000" placeholder="Lesiones, molestias, ejercicios que preferís o evitás…"
                          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300">{{ old('comentarios') }}</textarea>
                @error('comentarios')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2"><button class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700">Enviar pedido</button></div>
        </form>
    </section>
@endif
</x-layouts.portal>
