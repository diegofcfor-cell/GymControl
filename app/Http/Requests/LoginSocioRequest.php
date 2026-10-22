<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Ingreso del socio con DNI y PIN, compartido por el portal y la API (el límite de intentos está en AutenticacionSocioService). */
class LoginSocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dni' => ['required', 'string', 'max:15'],
            'pin' => ['required', 'digits:6'],
            'dispositivo' => ['nullable', 'string', 'max:60'],
        ];
    }
}
