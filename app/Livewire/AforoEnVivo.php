<?php

namespace App\Livewire;

use App\Services\AforoService;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Indicador de aforo. Se vuelve a dibujar apenas llega el evento `aforo.actualizado` por WebSocket (Reverb + Echo)
 * y, como respaldo si el WebSocket no está disponible, cada 30 segundos (wire:poll).
 */
class AforoEnVivo extends Component
{
    #[On('echo:aforo,.aforo.actualizado')]
    public function actualizar(): void
    {
        // Sin cuerpo: Livewire vuelve a ejecutar render() con los datos actuales.
    }

    public function render(AforoService $aforo)
    {
        return view('livewire.aforo-en-vivo', $aforo->estado());
    }
}
