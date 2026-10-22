<?php

namespace App\Policies;

use App\Models\Socio;
use App\Models\User;

/**
 * Permisos sobre un socio puntual:
 *  - Solo el administrador da de baja, y solo a un socio que no esté dado de baja.
 *  - Solo el administrador reactiva, y solo a un socio dado de baja.
 */
class SocioPolicy
{
    public function delete(User $user, Socio $socio): bool
    {
        return $user->esAdministrador() && ! $socio->trashed();
    }

    public function restore(User $user, Socio $socio): bool
    {
        return $user->esAdministrador() && $socio->trashed();
    }
}
