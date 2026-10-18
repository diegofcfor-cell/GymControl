<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gimnasio Fénix</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($web['descripcion'] ?: 'Gimnasio Fénix: planes, horarios y aforo en vivo.', 155) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-white text-slate-900 antialiased">
<header class="sticky top-0 z-20 border-b border-white/10 bg-slate-900/95 text-white backdrop-blur" x-data="{ abierto: false }">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <a href="#inicio" class="text-lg font-bold">🏋️ Gimnasio Fénix</a>
        <nav class="hidden items-center gap-1 text-sm md:flex">
            @if ($servicios)<a href="#servicios" class="rounded px-3 py-1.5 hover:bg-white/10">Servicios</a>@endif
            @if ($planes->isNotEmpty())<a href="#planes" class="rounded px-3 py-1.5 hover:bg-white/10">Planes</a>@endif
            @if ($horarios)<a href="#horarios" class="rounded px-3 py-1.5 hover:bg-white/10">Horarios</a>@endif
            <a href="#contacto" class="rounded px-3 py-1.5 hover:bg-white/10">Contacto</a>
            @auth<a href="{{ route('inicio') }}" class="ml-2 rounded-lg border border-white/30 px-4 py-1.5 font-medium hover:bg-white/10">Ir al panel</a>@endauth
            <a href="{{ route('login') }}" class="ml-2 rounded-lg border border-white/30 px-4 py-1.5 font-medium hover:bg-white/10">Personal</a>
            <a href="{{ route('portal.login') }}" class="ml-2 rounded-lg bg-white px-4 py-1.5 font-medium text-slate-900 hover:bg-slate-200">Soy socio</a>
        </nav>
        <button class="rounded p-2 hover:bg-white/10 md:hidden" x-on:click="abierto = !abierto" aria-label="Menú">☰</button>
    </div>
    <nav x-show="abierto" x-cloak class="space-y-1 px-4 pb-3 text-sm md:hidden" x-on:click="abierto = false">
        @if ($servicios)<a href="#servicios" class="block rounded px-3 py-2 hover:bg-white/10">Servicios</a>@endif
        @if ($planes->isNotEmpty())<a href="#planes" class="block rounded px-3 py-2 hover:bg-white/10">Planes</a>@endif
        @if ($horarios)<a href="#horarios" class="block rounded px-3 py-2 hover:bg-white/10">Horarios</a>@endif
        <a href="#contacto" class="block rounded px-3 py-2 hover:bg-white/10">Contacto</a>
        @auth<a href="{{ route('inicio') }}" class="block rounded px-3 py-2 hover:bg-white/10">Ir al panel</a>@endauth
        <a href="{{ route('portal.login') }}" class="block rounded bg-white px-3 py-2 font-medium text-slate-900">Soy socio</a>
        <a href="{{ route('login') }}" class="block rounded border border-white/30 px-3 py-2 font-medium">Personal del gimnasio</a>
    </nav>
</header>

<section id="inicio" class="relative overflow-hidden bg-slate-900 text-white">
    @if ($fotos)
        {{-- Fondo: las fotos de public/img/portada pasan una tras otra con un acercamiento lento --}}
        @php($segundos = 6)
        <style>
            .portada-foto { position: absolute; inset: 0; background-size: cover; background-position: center; opacity: 0; animation: portada-zoom {{ $segundos * count($fotos) }}s linear infinite; }
            @if (count($fotos) > 1)
            .portada-foto { animation: portada-pasar {{ $segundos * count($fotos) }}s linear infinite; }
            @keyframes portada-pasar {
                0% { opacity: 0; transform: scale(1); }
                {{ round(100 / count($fotos) * 0.25, 2) }}% { opacity: 1; }
                {{ round(100 / count($fotos), 2) }}% { opacity: 1; }
                {{ round(100 / count($fotos) * 1.25, 2) }}% { opacity: 0; transform: scale(1.12); }
                100% { opacity: 0; transform: scale(1.12); }
            }
            @else
            .portada-foto { opacity: 1; animation-direction: alternate; }
            @endif
            @keyframes portada-zoom { from { transform: scale(1); } to { transform: scale(1.12); } }
            @media (prefers-reduced-motion: reduce) { .portada-foto { animation: none; } .portada-foto:first-child { opacity: 1; } }
        </style>
        <div class="absolute inset-0" aria-hidden="true">
            @foreach ($fotos as $i => $foto)
                <div class="portada-foto" style="background-image: url('{{ $foto }}'); animation-delay: {{ $i * $segundos - $segundos * 0.25 }}s"></div>
            @endforeach
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-900/70 to-slate-900/40" aria-hidden="true"></div>
    @endif
    <div class="relative mx-auto grid max-w-6xl gap-8 px-4 py-16 md:grid-cols-2 md:items-center md:py-24">
        <div>
            <h1 class="text-4xl font-bold leading-tight md:text-5xl">Entrená en Gimnasio Fénix</h1>
            <p class="mt-4 text-lg text-slate-300">{{ $web['descripcion'] ?: 'Musculación, entrenamiento y profesores en sala. Mirá cuánta gente hay antes de venir.' }}</p>
            <div class="mt-6 flex flex-wrap gap-3">
                @if ($planes->isNotEmpty())<a href="#planes" class="rounded-lg bg-emerald-500 px-5 py-2.5 font-medium text-slate-900 hover:bg-emerald-400">Ver planes</a>@endif
                <a href="{{ route('portal.login') }}" class="rounded-lg border border-white/30 px-5 py-2.5 font-medium hover:bg-white/10">Ingresar al portal del socio</a>
                <a href="{{ route('login') }}" class="rounded-lg border border-white/30 px-5 py-2.5 font-medium hover:bg-white/10">Ingreso del personal</a>
            </div>
        </div>
        <div class="text-slate-900"><livewire:aforo-en-vivo /></div>
    </div>
</section>

@if ($servicios)
<section id="servicios" class="mx-auto max-w-6xl scroll-mt-16 px-4 py-14">
    <h2 class="text-2xl font-bold">Servicios</h2>
    <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($servicios as $s)
            <li class="rounded-xl border border-slate-200 p-4 font-medium">✓ {{ $s }}</li>
        @endforeach
    </ul>
</section>
@endif

@if ($planes->isNotEmpty())
<section id="planes" class="scroll-mt-16 bg-slate-50">
    <div class="mx-auto max-w-6xl px-4 py-14">
        <h2 class="text-2xl font-bold">Planes y precios</h2>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($planes as $p)
                <div class="flex flex-col rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold">{{ $p->nombre }}</h3>
                    <p class="mt-2"><span class="text-3xl font-bold">$ {{ number_format($p->precio, 0, ',', '.') }}</span> <span class="text-sm text-slate-500">/ {{ $p->duracion_dias }} días</span></p>
                    <p class="mt-2 text-sm text-slate-600">{{ $p->esPaseLibre() ? 'Pase libre, sin límite de horas.' : "Hasta {$p->horas_diarias_max} h por día." }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-4 text-sm text-slate-500">Para inscribirte acercate a recepción con tu DNI y un apto físico vigente.</p>
    </div>
</section>
@endif

@if ($horarios)
<section id="horarios" class="mx-auto max-w-6xl scroll-mt-16 px-4 py-14">
    <h2 class="text-2xl font-bold">Horarios</h2>
    <ul class="mt-6 max-w-lg divide-y divide-slate-200 rounded-xl border border-slate-200">
        @foreach ($horarios as $h)<li class="px-4 py-3">{{ $h }}</li>@endforeach
    </ul>
</section>
@endif

<section id="contacto" class="scroll-mt-16 bg-slate-900 text-white">
    <div class="mx-auto grid max-w-6xl gap-6 px-4 py-14 md:grid-cols-2">
        <div>
            <h2 class="text-2xl font-bold">Contacto</h2>
            <ul class="mt-4 space-y-2 text-slate-300">
                @if ($web['direccion'])<li>📍 {{ $web['direccion'] }}</li>@endif
                @if ($web['telefono'])<li>📞 <a class="underline" href="tel:{{ preg_replace('/[^0-9+]/', '', $web['telefono']) }}">{{ $web['telefono'] }}</a></li>@endif
                @if ($web['email'])<li>✉️ <a class="underline" href="mailto:{{ $web['email'] }}">{{ $web['email'] }}</a></li>@endif
                @if ($web['instagram'])<li>📷 <a class="underline" href="https://instagram.com/{{ ltrim($web['instagram'], '@') }}" target="_blank" rel="noopener">{{ '@'.ltrim($web['instagram'], '@') }}</a></li>@endif
                @unless ($web['direccion'] || $web['telefono'] || $web['email'] || $web['instagram'])<li>Consultanos en recepción.</li>@endunless
            </ul>
        </div>
        <div class="md:text-right">
            <p class="text-slate-300">¿Ya sos socio? Consultá tu cuota, tus asistencias y cuánta gente hay.</p>
            <a href="{{ route('portal.login') }}" class="mt-3 inline-block rounded-lg bg-white px-5 py-2.5 font-medium text-slate-900 hover:bg-slate-200">Ingresar al portal del socio</a>
        </div>
    </div>
    <p class="border-t border-white/10 py-4 text-center text-xs text-slate-500">© {{ now()->year }} Gimnasio Fénix · <a href="{{ route('login') }}" class="underline hover:text-slate-300">Ingreso del personal</a></p>
</section>

{{-- Chatbot flotante --}}
<div class="fixed bottom-4 right-4 z-30 w-[calc(100%-2rem)] max-w-sm" x-data="{ chat: false }">
    <div x-show="chat" x-cloak x-transition class="mb-3 shadow-2xl"><livewire:chatbot-faq /></div>
    <button type="button" x-on:click="chat = !chat" class="ml-auto flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-3 font-medium text-slate-900 shadow-lg hover:bg-emerald-400" :aria-expanded="chat">
        <span x-text="chat ? 'Cerrar' : '💬 ¿Tenés dudas?'">💬 ¿Tenés dudas?</span>
    </button>
</div>
@livewireScripts
</body>
</html>
