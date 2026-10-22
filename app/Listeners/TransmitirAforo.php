<?php

namespace App\Listeners;

use App\Events\AforoActualizado;
use App\Events\EgresoRegistrado;
use App\Events\IngresoRegistrado;
use App\Services\AforoService;
use Throwable;

/**
 * Segundo observador de la pantalla de acceso: avisa en tiempo real que cambió el aforo.
 *
 * Reglas:
 * - Solo reacciona a ingresos y egresos (un rechazo no cambia cuántos hay en la sala).
 * - Si el servidor de WebSockets no responde, el acceso no se interrumpe: se deja constancia en el log y
 *   los navegadores siguen actualizándose cada 30 segundos con wire:poll.
 */
class TransmitirAforo
{
    public function __construct(private AforoService $aforo) {}

    public function handle(IngresoRegistrado|EgresoRegistrado $evento): void
    {
        try {
            AforoActualizado::dispatch($this->aforo->estado());
        } catch (Throwable $e) {
            report($e);
        }
    }
}
