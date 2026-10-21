<?php

namespace App\Services;

use App\Models\Socio;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Ingreso del socio con DNI + PIN (portal web y API):
 *  - El PIN tiene solo 6 dígitos: se exige también el DNI y se limitan los intentos por DNI.
 *  - Tras MAX_INTENTOS fallidos el DNI queda bloqueado BLOQUEO_SEGUNDOS.
 *  - Un socio dado de baja o inactivo no puede ingresar.
 */
class AutenticacionSocioService
{
    /** Intentos fallidos permitidos por DNI antes de bloquear. */
    public const MAX_INTENTOS = 5;

    /** Segundos de bloqueo tras superar los intentos. */
    public const BLOQUEO_SEGUNDOS = 900;

    /** @return array{socio:?Socio,error:?string} */
    public function intentar(string $dni, string $pin): array
    {
        $clave = 'portal-socio:'.$dni;
        if (RateLimiter::tooManyAttempts($clave, self::MAX_INTENTOS)) {
            $minutos = (int) ceil(RateLimiter::availableIn($clave) / 60);

            return ['socio' => null, 'error' => "Demasiados intentos. Probá de nuevo en {$minutos} min."];
        }

        $socio = Socio::where('dni', $dni)->first();

        if (! $socio || ! hash_equals($socio->pin, $pin) || ! $socio->estaActivo()) {
            RateLimiter::hit($clave, self::BLOQUEO_SEGUNDOS);

            return ['socio' => null, 'error' => 'DNI o PIN incorrectos.'];
        }

        RateLimiter::clear($clave);

        return ['socio' => $socio, 'error' => null];
    }
}
