{{-- Aviso de privacidad según la Ley 25.326. Conviene que lo revise un profesional del derecho antes del uso real. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aviso de privacidad · Gimnasio Fénix</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
<header class="bg-slate-900 text-white">
    <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-3">
        <a href="{{ route('web') }}" class="text-lg font-bold">🏋️ Gimnasio Fénix</a>
        <a href="{{ route('portal.login') }}" class="rounded-lg bg-white px-4 py-1.5 text-sm font-medium text-slate-900 hover:bg-slate-200">Soy socio</a>
    </div>
</header>
<main class="mx-auto max-w-3xl space-y-6 px-4 py-8 text-sm leading-relaxed">
    <h1 class="text-2xl font-bold text-slate-900">Aviso de privacidad</h1>
    <p>Este aviso explica qué datos personales trata el Gimnasio Fénix, para qué los usa y cómo podés ejercer tus derechos, conforme a la Ley 25.326 de Protección de los Datos Personales.</p>

    <section>
        <h2 class="mb-1 text-lg font-semibold text-slate-900">Quién es el responsable</h2>
        <p>Gimnasio Fénix, Formosa.@if ($web['direccion']) Domicilio: {{ $web['direccion'] }}.@endif @if ($web['telefono']) Teléfono: {{ $web['telefono'] }}.@endif @if ($web['email']) Correo: {{ $web['email'] }}.@endif</p>
    </section>

    <section>
        <h2 class="mb-1 text-lg font-semibold text-slate-900">Qué datos tratamos</h2>
        <ul class="list-disc space-y-1 pl-5">
            <li>De identificación y contacto: nombre, apellido, DNI, teléfono y correo electrónico.</li>
            <li>De tu membresía: plan contratado, pagos, vencimientos y registro de ingresos y egresos.</li>
            <li>De salud: el certificado de apto físico (fechas de emisión y vencimiento y el archivo del certificado).</li>
        </ul>
    </section>

    <section>
        <h2 class="mb-1 text-lg font-semibold text-slate-900">Para qué los usamos</h2>
        <p>Para administrar tu membresía, registrar los cobros, controlar el acceso a la sala, verificar que tengas un apto físico vigente para entrenar y mostrarte tu información en el portal del socio. No los usamos para otros fines ni los cedemos a terceros.</p>
    </section>

    <section>
        <h2 class="mb-1 text-lg font-semibold text-slate-900">Tus datos de salud</h2>
        <p>El apto físico es un dato sensible (art. 2 de la ley). Se guarda en un almacenamiento privado, no accesible desde internet, y solo lo ven la administración, la recepción y vos desde tu portal. Nadie está obligado a entregar datos sensibles (art. 7); sin embargo, el gimnasio exige un apto físico vigente para habilitar el ingreso a la sala.</p>
    </section>

    <section>
        <h2 class="mb-1 text-lg font-semibold text-slate-900">Tu consentimiento</h2>
        <p>Al darte de alta aceptás este aviso de forma libre, expresa e informada (art. 5), y el sistema registra la fecha. Sin ese consentimiento no se cargan tus datos de salud.</p>
    </section>

    <section>
        <h2 class="mb-1 text-lg font-semibold text-slate-900">Cuánto tiempo los conservamos</h2>
        <p>Mientras seas socio. Si te das de baja, conservamos el historial de pagos y asistencias para respaldar los cobros realizados, y podés pedir que se supriman tus datos. La supresión no procede cuando exista una obligación legal de conservarlos (art. 16).</p>
    </section>

    <section>
        <h2 class="mb-1 text-lg font-semibold text-slate-900">Tus derechos</h2>
        <ul class="list-disc space-y-1 pl-5">
            <li><strong>Acceso</strong> (art. 14): podés consultar tus datos en el portal del socio o pedirlos en recepción. Es gratuito si lo pedís con intervalos de al menos seis meses.</li>
            <li><strong>Rectificación, actualización y supresión</strong> (art. 16): podés pedir que corrijamos o eliminemos tus datos; respondemos dentro de los cinco días hábiles.</li>
        </ul>
        <p class="mt-2">Para ejercerlos, acercate a recepción o escribinos a los datos de contacto de arriba.</p>
    </section>

    <section>
        <h2 class="mb-1 text-lg font-semibold text-slate-900">Órgano de control</h2>
        <p>La Agencia de Acceso a la Información Pública (AAIP) es la autoridad de aplicación de la Ley 25.326 y atiende las denuncias y reclamos por incumplimiento de la ley.</p>
    </section>

    <p><a href="{{ route('web') }}" class="text-slate-500 underline">Volver a la página del gimnasio</a></p>
</main>
</body>
</html>
