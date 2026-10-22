<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><title>Credencial {{ $socio->nombre_completo }}</title>
@vite(['resources/css/app.css'])
<style>@media print { .no-print { display: none } body { background: #fff } }</style></head>
<body class="bg-slate-100 p-8">
<div class="no-print mb-4 flex gap-3"><button onclick="window.print()" class="rounded bg-slate-900 px-4 py-2 text-white">Imprimir</button><button onclick="window.close()" class="rounded border bg-white px-4 py-2">Cerrar</button></div>
<div class="mx-auto flex w-[420px] items-center gap-5 rounded-2xl border-2 border-slate-900 bg-white p-6">
    @if ($qr)<img src="data:image/svg+xml;base64,{{ $qr }}" alt="QR" class="h-40 w-40">@endif
    <div>
        <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Gimnasio Fénix</p>
        <p class="mt-1 text-lg font-bold leading-tight">{{ $socio->nombre_completo }}</p>
        <p class="text-sm text-slate-600">DNI {{ $socio->dni }}</p>
        <p class="text-sm text-slate-600">Plan {{ $socio->membresia->nombre }}</p>
        <p class="mt-3 text-xs uppercase text-slate-500">PIN</p>
        <p class="font-mono text-3xl font-bold tracking-[0.3em]">{{ $socio->pin }}</p>
    </div>
</div>
<p class="no-print mt-4 text-center text-sm text-slate-500">{{ $qr ? 'Presentá el PIN o el QR' : 'Marcá tu PIN' }} en la pantalla de acceso al ingresar y al retirarte.</p>
</body></html>
