<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Ingreso de operadores con correo y contraseña. El límite de intentos lo aplica `throttle` en la ruta. */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
