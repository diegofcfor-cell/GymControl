<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi cuenta · Gym Control</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-900 px-4">
<form method="POST" action="{{ route('portal.login') }}" class="w-full max-w-sm space-y-4 rounded-2xl bg-white p-8 shadow-xl">
    @csrf
    <div class="text-center">
        <p class="text-4xl">🏋️</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Gimnasio Fénix</h1>
        <p class="text-sm text-slate-500">Portal del socio</p>
    </div>
    @error('dni')<div class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div>@enderror
    <x-input name="dni" label="DNI" inputmode="numeric" required autofocus autocomplete="username" />
    <x-input name="pin" label="PIN de acceso" type="password" inputmode="numeric" maxlength="6" required autocomplete="current-password"
             ayuda="Es el mismo PIN de 6 dígitos que usás para entrar al gimnasio." />
    <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 font-medium text-white hover:bg-slate-700">Ingresar</button>
    <p class="text-center text-sm"><a href="{{ route('web') }}" class="text-slate-500 underline">Volver a la página del gimnasio</a></p>
    <p class="text-center text-xs"><a href="{{ route('login') }}" class="text-slate-400 underline">¿Sos del personal? Ingresá acá</a></p>
</form>
</body>
</html>
