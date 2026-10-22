@props(['titulo' => 'Mi cuenta'])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} · Gym Control</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<header class="bg-slate-900 text-white">
    @php
        $enlaces = [
            ['portal.inicio', 'Mi cuenta'], ['portal.asistencias', 'Asistencias'], ['portal.pagos', 'Pagos'],
            ['portal.aptos', 'Apto médico'], ['portal.rutina', 'Mi rutina'], ['portal.aforo', 'Aforo'], ['portal.preguntas', 'Preguntas'], ['portal.mis-datos', 'Mis datos'],
        ];
    @endphp
    <div class="mx-auto flex max-w-3xl items-center justify-between px-4 pt-3">
        <a href="{{ route('portal.inicio') }}" class="font-bold">🏋️ Gimnasio Fénix</a>
        <form method="POST" action="{{ route('portal.logout') }}">@csrf<button class="rounded-lg border border-white/30 px-3 py-1 text-sm hover:bg-white/10">Salir</button></form>
    </div>
    <nav class="mx-auto flex max-w-3xl flex-wrap gap-1 px-4 py-2 text-sm">
        @foreach ($enlaces as [$ruta, $texto])
            <a href="{{ route($ruta) }}" class="whitespace-nowrap rounded px-3 py-1 hover:bg-slate-700 {{ request()->routeIs($ruta.'*') ? 'bg-slate-700' : '' }}">{{ $texto }}</a>
        @endforeach
    </nav>
</header>
<main class="mx-auto max-w-3xl px-4 py-6">
@if (session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('ok') }}</div>
@endif
{{ $slot }}
</main>
@livewireScripts
</body>
</html>
