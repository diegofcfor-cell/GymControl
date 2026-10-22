<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Cuentas del personal (HU10 y HU30). Reglas:
 *  - Las contraseñas se guardan con hash bcrypt (cast `hashed` del modelo User), nunca en texto.
 *  - Al editar, una contraseña vacía significa "no cambiarla".
 *  - El administrador no puede quitarse a sí mismo el rol ni desactivarse (el sistema nunca queda sin administrador).
 *  - Si a un operador le cambian la contraseña o lo desactivan, se cierran sus sesiones abiertas y se invalida
 *    su cookie "recordarme": quien conocía la contraseña anterior deja de tener acceso en ese momento.
 */
class OperadorService
{
    public function crear(array $datos): User
    {
        return User::create($datos + ['activo' => true]);
    }

    public function actualizar(User $operador, array $datos, User $quienEdita): void
    {
        if ($operador->is($quienEdita)) {
            $datos['rol'] = User::ROL_ADMIN;
            $datos['activo'] = true;
        }
        if (empty($datos['password'])) {
            unset($datos['password']);
        }

        $operador->update($datos);

        if (isset($datos['password']) || ! $operador->activo) {
            $this->cerrarSesiones($operador, $operador->is($quienEdita) ? session()->getId() : null);
        }
    }

    /** El propio operador cambia su contraseña; su sesión actual sigue abierta y se cierran las demás. */
    public function cambiarClave(User $operador, string $nueva, ?string $sesionActual = null): void
    {
        $operador->update(['password' => $nueva]);

        $this->cerrarSesiones($operador, $sesionActual);
    }

    protected function cerrarSesiones(User $operador, ?string $excepto): void
    {
        $operador->forceFill(['remember_token' => Str::random(60)])->save();

        // Con SESSION_DRIVER=database (producción) cada sesión es una fila con el id del usuario.
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $operador->id)
                ->when($excepto, fn ($q) => $q->where('id', '!=', $excepto))
                ->delete();
        }
    }
}
