<?php

namespace App\Events;

use App\Models\Socio;
use Illuminate\Foundation\Events\Dispatchable;

/** La pantalla de acceso rechazó el ingreso de un socio. Lleva los motivos que se le mostraron. */
class AccesoDenegado
{
    use Dispatchable;

    /** @param  list<string>  $motivos */
    public function __construct(public readonly Socio $socio, public readonly array $motivos) {}
}
