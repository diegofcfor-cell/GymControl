<?php

namespace App\Policies;

use App\Models\AptoFisico;
use App\Models\User;

/**
 * Los aptos físicos son datos de salud (Ley 25.326): entre los operadores, solo el administrador y
 * recepción los cargan y ven su archivo. El profesor no accede. El socio ve los suyos desde el portal,
 * que tiene su propia sesión y su propio control.
 */
class AptoFisicoPolicy
{
    public function create(User $user): bool
    {
        return $user->esAdministrador() || $user->esRecepcionista();
    }

    public function view(User $user, AptoFisico $apto): bool
    {
        return $user->esAdministrador() || $user->esRecepcionista();
    }
}
