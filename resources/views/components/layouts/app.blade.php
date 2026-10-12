@props(['titulo' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? 'Gym Control' }} · Gym Control</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
@php
    $u = auth()->user();
    $enlaces = [
        ['dashboard', 'Dashboard', 'administrador'],
        ['acceso', 'Pantalla de acceso', null],
        ['socios.index', 'Socios', null],
        ['pagos.index', 'Pagos', null],
        ['asistencias.index', 'Asistencias', null],
        ['membresias.index', 'Membresías', 'administrador'],
        ['operadores.index', 'Operadores', 'administrador'],
    ];
@endphp
<nav class="bg-slate-900 text-white" x-data="{ abierto: false }">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
        <a href="{{ route('inicio') }}" class="text-lg font-bold tracking-tight">🏋️ Gym Control <span class="text-xs font-normal text-slate-400">· Gimnasio Fénix</span></a>
        <button class="md:hidden rounded p-2 hover:bg-slate-800" x-on:click="abierto = !abierto" aria-label="Menú">☰</button>
        <div class="hidden items-center gap-1 md:flex">
            @foreach ($enlaces as [$ruta, $texto, $rol])
                @if (! $rol || $u->rol === $rol)
                    <a href="{{ route($ruta) }}" class="rounded px-3 py-1.5 text-sm hover:bg-slate-700 {{ request()->routeIs(explode('.', $ruta)[0].'*') ? 'bg-slate-700' : '' }}">{{ $texto }}</a>
                @endif
            @endforeach
            <form method="POST" action="{{ route('logout') }}" class="ml-3 flex items-center gap-2 border-l border-slate-700 pl-3">
                @csrf
                <span class="text-xs text-slate-300">{{ $u->name }} <span class="text-slate-500">({{ $u->rol }})</span></span>
                <button class="rounded bg-slate-700 px-3 py-1.5 text-sm hover:bg-red-600">Salir</button>
            </form>
        </div>
    </div>
    <div class="space-y-1 px-4 pb-3 md:hidden" x-show="abierto" x-cloak>
        @foreach ($enlaces as [$ruta, $texto, $rol])
            @if (! $rol || $u->rol === $rol)
                <a href="{{ route($ruta) }}" class="block rounded px-3 py-2 text-sm hover:bg-slate-700">{{ $texto }}</a>
            @endif
        @endforeach
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded bg-slate-700 px-3 py-2 text-left text-sm">Salir ({{ $u->name }})</button></form>
    </div>
</nav>

<main class="mx-auto max-w-7xl px-4 py-6">
    @if (session('ok'))
        <div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('ok') }}</div>
    @endif
    @if ($errors->any() && ! request()->routeIs('login'))
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Revise los datos ingresados:</p>
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    {{ $slot }}
</main>
@livewireScripts
</body>
</html>
