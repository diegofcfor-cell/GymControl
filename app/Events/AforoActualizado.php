<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Se transmite por WebSocket (Reverb) en el canal público `aforo` cada vez que alguien entra o sale.
 * Es público porque solo lleva números: nunca datos de un socio.
 */
class AforoActualizado implements ShouldBroadcastNow
{
    use Dispatchable;

    /** @param  array{presentes:int,capacidad:int,porcentaje:int,nivel:string}  $estado */
    public function __construct(public readonly array $estado) {}

    public function broadcastOn(): Channel
    {
        return new Channel('aforo');
    }

    public function broadcastAs(): string
    {
        return 'aforo.actualizado';
    }

    public function broadcastWith(): array
    {
        return $this->estado;
    }
}
