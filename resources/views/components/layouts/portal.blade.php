@props(['titulo' => 'Mi cuenta'])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} · Gym Control</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<header class="bg-slate-900 text-white">
    <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-3">
        <a href="{{ route('portal.inicio') }}" class="font-bold">🏋️ Gimnasio Fénix</a>
        <form method="POST" action="{{ route('portal.logout') }}">@csrf<button class="rounded-lg border border-white/30 px-3 py-1 text-sm hover:bg-white/10">Salir</button></form>
    </div>
</header>
<main class="mx-auto max-w-3xl px-4 py-6">
{{ $slot }}
</main>
</body>
</html>
