<x-layouts.app titulo="Configuración">
<h1 class="mb-4 text-2xl font-bold">Configuración del gimnasio</h1>
<form method="POST" action="{{ route('configuracion.update') }}" class="grid max-w-2xl gap-6">
    @csrf @method('PUT')
    <section class="grid gap-4 rounded-xl bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Aforo</h2>
        <x-input name="capacidad_maxima" label="Capacidad máxima del local (personas)" type="number" min="1" max="5000" :value="$capacidad" required
                 ayuda="Se usa para el indicador de aforo de la página web y del portal del socio." />
    </section>

    <section class="grid gap-2 rounded-xl bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Pantalla de acceso</h2>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="acceso_qr" value="1" @checked(old('acceso_qr', $accesoQr)) class="rounded"> Permitir ingreso con código QR (lector o cámara)</label>
        <p class="text-xs text-slate-500">Sin marcar, los socios ingresan solo con su PIN y la credencial no muestra el QR. Cada socio conserva su código QR, así que se puede activar en cualquier momento.</p>
    </section>

    <section class="grid gap-4 rounded-xl bg-white p-6 shadow-sm">
        <div>
            <h2 class="font-semibold">Página web</h2>
            <p class="text-xs text-slate-500">Se muestra en la <a href="{{ route('web') }}" target="_blank" class="underline">página pública</a>. El chatbot usa los horarios y los datos de contacto para responder.</p>
        </div>
        @foreach ([
            ['descripcion', 'Presentación', 3, 'Un párrafo corto que aparece al inicio de la página.'],
            ['servicios', 'Servicios', 5, 'Uno por línea, por ejemplo: Musculación, Funcional, Spinning.'],
            ['horarios', 'Horarios', 4, 'Uno por línea, por ejemplo: Lunes a viernes: 7 a 23 h.'],
        ] as [$campo, $etiqueta, $filas, $ayuda])
            <div>
                <label for="{{ $campo }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $etiqueta }}</label>
                <textarea id="{{ $campo }}" name="{{ $campo }}" rows="{{ $filas }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300">{{ old($campo, $web[$campo]) }}</textarea>
                <p class="mt-1 text-xs text-slate-500">{{ $ayuda }}</p>
                @error($campo)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        @endforeach
        <div class="grid gap-4 sm:grid-cols-2">
            <x-input name="direccion" label="Dirección" :value="$web['direccion']" maxlength="200" />
            <x-input name="telefono" label="Teléfono / WhatsApp" :value="$web['telefono']" maxlength="50" />
            <x-input name="email" label="Correo de contacto" type="email" :value="$web['email']" maxlength="100" />
            <x-input name="instagram" label="Instagram" :value="$web['instagram']" maxlength="60" ayuda="Solo el usuario, por ejemplo @gimnasiofenix." />
        </div>
    </section>

    <div><button class="rounded-lg bg-slate-900 px-5 py-2 text-sm text-white">Guardar</button></div>
</form>
</x-layouts.app>
