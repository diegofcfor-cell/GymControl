<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar · Gym Control</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-900 px-4">
<form method="POST" action="{{ route('login') }}" class="w-full max-w-sm space-y-4 rounded-2xl bg-white p-8 shadow-xl">
    @csrf
    <div class="text-center">
        <p class="text-4xl">🏋️</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Gym Control</h1>
        <p class="text-sm text-slate-500">Gimnasio Fénix · Acceso de operadores</p>
    </div>
    @error('email')<div class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div>@enderror
    <x-input name="email" label="Correo electrónico" type="email" required autofocus autocomplete="username" />
    <x-input name="password" label="Contraseña" type="password" required autocomplete="current-password" />
    <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="recordar" class="rounded"> Mantener sesión iniciada</label>
    <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 font-medium text-white hover:bg-slate-700">Ingresar</button>
</form>
</body>
</html>
