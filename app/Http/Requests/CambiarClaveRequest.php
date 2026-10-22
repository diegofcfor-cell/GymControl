<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Cambio de la propia contraseña:
 *  - Hay que escribir la contraseña actual (si alguien encuentra la sesión abierta, no puede cambiarla).
 *  - La nueva cumple la política del sistema (Password::defaults(), en AppServiceProvider), se repite y no puede
 *    ser igual a la actual.
 */
class CambiarClaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clave_actual' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'different:clave_actual', Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return ['clave_actual' => 'contraseña actual', 'password' => 'nueva contraseña'];
    }
}
