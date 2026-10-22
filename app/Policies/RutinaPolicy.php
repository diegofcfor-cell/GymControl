<?php

namespace App\Policies;

use App\Models\Rutina;
use App\Models\User;

/** Una rutina la edita el profesor que la armó; el administrador puede editar cualquiera. */
class RutinaPolicy
{
    public function update(User $user, Rutina $rutina): bool
    {
        return $user->esAdministrador() || $rutina->user_id === $user->id;
    }
}
