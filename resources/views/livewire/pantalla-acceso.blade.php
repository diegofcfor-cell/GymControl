<div class="flex min-h-screen flex-col items-center justify-center px-4 py-8"
     x-data="{
        camara: false, stream: null, timer: null,
        soporta: 'BarcodeDetector' in window && !!navigator.mediaDevices,
        async iniciar() {
            this.camara = true;
            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            this.$refs.video.srcObject = this.stream; await this.$refs.video.play();
            const det = new BarcodeDetector({ formats: ['qr_code'] });
            this.timer = setInterval(async () => {
                const r = await det.detect(this.$refs.video).catch(() => []);
                if (r.length) { this.detener(); this.$wire.set('codigo', r[0].rawValue); this.$wire.registrar(); }
            }, 400);
        },
        detener() { clearInterval(this.timer); this.stream?.getTracks().forEach(t => t.stop()); this.camara = false; }
     }"
     x-on:enfocar.window="$refs.codigo.focus()">

    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Gimnasio Fénix</p>
    <h1 class="mt-1 text-4xl font-bold">Control de acceso</h1>
    <p class="mt-2 text-slate-300">Ingrese su <b>PIN</b> o escanee su <b>código QR</b>. Use el mismo código al ingresar y al retirarse.</p>

    @if ($resultado)
        @php
            $color = $resultado['ok'] ? ($resultado['tipo'] === 'ingreso' ? 'bg-emerald-600' : 'bg-blue-600') : 'bg-red-600';
        @endphp
        <div class="mt-8 w-full max-w-xl rounded-3xl {{ $color }} p-8 text-center shadow-2xl" x-data x-init="setTimeout(() => $wire.limpiar(), 7000)" wire:key="res-{{ now()->timestamp }}">
            <p class="text-6xl">{{ $resultado['ok'] ? ($resultado['tipo'] === 'ingreso' ? '✅' : '👋') : '⛔' }}</p>
            <p class="mt-2 text-3xl font-bold">{{ $resultado['mensaje'] }}</p>
            @if ($resultado['socio'])<p class="mt-2 text-2xl">{{ $resultado['socio'] }}</p>@endif
            @if ($resultado['ok'])
                <p class="mt-3 text-lg">
                    {{ $resultado['tipo'] === 'ingreso' ? 'Ingreso registrado' : 'Egreso registrado' }} a las <b>{{ $resultado['hora'] }}</b>
                    @if ($resultado['permanencia'])<br>Tiempo de permanencia: <b>{{ $resultado['permanencia'] }}</b>@endif
                </p>
            @endif
            @foreach ($resultado['motivos'] as $m)<p class="mt-2 text-lg">• {{ $m }}</p>@endforeach
            @if ($resultado['aviso'])<p class="mt-3 rounded-lg bg-black/20 px-3 py-2">⚠️ {{ $resultado['aviso'] }}</p>@endif
        </div>
    @endif

    <form wire:submit="registrar" class="mt-8 w-full max-w-xl">
        <input x-ref="codigo" wire:model="codigo" autofocus autocomplete="off" inputmode="text" placeholder="PIN o código QR"
               class="w-full rounded-2xl border-2 border-slate-600 bg-slate-800 px-6 py-5 text-center font-mono text-3xl tracking-[0.3em] text-white placeholder:tracking-normal placeholder:text-slate-500 focus:border-white focus:outline-none">
        <button class="mt-4 w-full rounded-2xl bg-white px-6 py-4 text-xl font-bold text-slate-900 hover:bg-slate-200">Registrar</button>
    </form>

    <div class="mt-4 w-full max-w-xl" x-show="soporta" x-cloak>
        <button type="button" x-show="!camara" x-on:click="iniciar()" class="w-full rounded-xl border border-slate-600 px-4 py-3 text-slate-300 hover:bg-slate-800">📷 Escanear QR con la cámara</button>
        <div x-show="camara" x-cloak class="overflow-hidden rounded-2xl border border-slate-600">
            <video x-ref="video" playsinline muted class="w-full"></video>
            <button type="button" x-on:click="detener()" class="w-full bg-slate-800 py-2 text-sm">Cerrar cámara</button>
        </div>
    </div>

    {{-- En la terminal de la entrada no hay salida al sistema: la sesión la cierra el administrador desactivándola. --}}
    @unless (auth()->user()->esTerminal())
        <a href="{{ route('inicio') }}" class="mt-8 text-sm text-slate-500 hover:text-slate-300">← Volver al sistema</a>
    @endunless
</div>
