<x-layouts.app titulo="Registrar pago">
<h1 class="mb-1 text-2xl font-bold">Registrar pago</h1>
<p class="mb-4 text-slate-600">Socio: <b>{{ $socio->nombre_completo }}</b> · DNI {{ $socio->dni }} · Cuota actual vence: {{ $socio->fecha_vencimiento?->format('d/m/Y') ?? 'sin pagos' }}</p>
<form method="POST" action="{{ route('pagos.store', $socio) }}" class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
    @csrf
    <div>
        <label class="mb-1 block text-sm font-medium">Membresía a abonar</label>
        <div class="space-y-2">
        @foreach ($membresias as $m)
            <label class="flex cursor-pointer items-center justify-between rounded-lg border p-3 hover:bg-slate-50">
                <span><input type="radio" name="membresia_id" value="{{ $m->id }}" @checked(old('membresia_id', $socio->membresia_id) == $m->id) required class="mr-2">{{ $m->nombre }}
                    <span class="text-xs text-slate-500">· {{ $m->duracion_dias }} días · {{ $m->horas_diarias_max ? $m->horas_diarias_max.' h diarias' : 'pase libre' }}</span></span>
                <b>$ {{ number_format($m->precio, 2, ',', '.') }}</b>
            </label>
        @endforeach
        </div>
    </div>
    <div>
        <label for="metodo_pago" class="mb-1 block text-sm font-medium">Método de pago</label>
        <select id="metodo_pago" name="metodo_pago" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
            @foreach (['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'debito' => 'Tarjeta de débito', 'credito' => 'Tarjeta de crédito'] as $k => $v)
                <option value="{{ $k }}" @selected(old('metodo_pago') === $k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <p class="text-xs text-slate-500">El importe se toma del precio vigente del plan y el vencimiento se calcula automáticamente.</p>
    <div class="flex gap-3"><button class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-medium text-white">Confirmar pago</button><a href="{{ route('socios.show', $socio) }}" class="rounded-lg border px-5 py-2 text-sm">Cancelar</a></div>
</form>
</x-layouts.app>
