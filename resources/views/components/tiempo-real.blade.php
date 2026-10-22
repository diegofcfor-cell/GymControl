{{-- Datos públicos para que Echo se conecte a Reverb (aforo en tiempo real). Va antes de @vite.
     Sin Reverb activado no se imprime nada y el aforo se actualiza con wire:poll. --}}
@if (config('broadcasting.default') === 'reverb' && config('broadcasting.connections.reverb.key'))
    <script>window.gymReverb = @json(['key' => config('broadcasting.connections.reverb.key'), 'puerto' => config('broadcasting.cliente_puerto')]);</script>
@endif
