<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\OperadorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Restablece la contraseña de un operador desde la consola del servidor (en Render: Shell del servicio).
 * Sirve cuando nadie puede entrar al panel. La contraseña se escribe sin mostrarse en pantalla y nunca pasa
 * por el repositorio ni por las variables de entorno; también reactiva al operador.
 */
class ClaveOperador extends Command
{
    protected $signature = 'gym:clave-operador {email : Correo del operador}';

    protected $description = 'Restablece la contraseña de un operador (se escribe sin mostrarse)';

    public function handle(OperadorService $operadores): int
    {
        $operador = User::where('email', $this->argument('email'))->first();
        if (! $operador) {
            $this->error('No existe un operador con ese correo.');

            return self::FAILURE;
        }

        $clave = (string) $this->secret('Nueva contraseña');
        $validacion = Validator::make(
            ['password' => $clave, 'password_confirmation' => (string) $this->secret('Repetir la contraseña')],
            ['password' => ['required', 'confirmed', Password::defaults()]],
        );
        if ($validacion->fails()) {
            foreach ($validacion->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $operador->update(['activo' => true]);
        $operadores->cambiarClave($operador, $clave);
        $this->info("Contraseña de {$operador->email} actualizada. Se cerraron sus sesiones abiertas.");

        return self::SUCCESS;
    }
}
