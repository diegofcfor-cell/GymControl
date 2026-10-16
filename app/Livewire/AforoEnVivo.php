<?php

namespace App\Livewire;

use App\Services\AforoService;
use Livewire\Component;

/** Indicador de aforo que se actualiza solo cada 30 segundos (wire:poll). */
class AforoEnVivo extends Component
{
    public function render(AforoService $aforo)
    {
        return view('livewire.aforo-en-vivo', $aforo->estado());
    }
}
