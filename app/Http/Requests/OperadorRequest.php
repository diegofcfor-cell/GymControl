<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta y edición de operadores:
 *  - El correo es único (al editar se ignora el del propio operador).
 *  - En el alta la contraseña es obligatoria; al editar, vacía significa "no cambiarla".
 */
class OperadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User|null $operador */
        $operador = $this->route('operador');

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($operador?->id)],
            'password' => [$operador ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'rol' => ['required', Rule::in(User::ROLES)],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
