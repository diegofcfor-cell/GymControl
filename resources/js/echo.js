import Echo from 'laravel-echo';

import Pusher from 'pusher-js';

// Tiempo real con Laravel Reverb. El servidor imprime la clave pública en window.gymReverb (componente
// <x-tiempo-real>); sin ella no se crea Echo y el aforo se sigue actualizando con wire:poll cada 30 segundos.
// El navegador se conecta al mismo dominio de la página: Apache redirige /app al servidor de Reverb.
const reverb = window.gymReverb;

if (reverb?.key) {
    window.Pusher = Pusher;
    const puerto = reverb.puerto ?? window.location.port;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverb.key,
        wsHost: window.location.hostname,
        wsPort: puerto || 80,
        wssPort: puerto || 443,
        forceTLS: window.location.protocol === 'https:',
        enabledTransports: ['ws', 'wss'],
    });
}
