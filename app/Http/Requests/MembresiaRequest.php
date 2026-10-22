<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Alta y edición de membresías:
 *  - Precio y duración obligatorios; horas_diarias_max vacío significa pase libre.
 */
class MembresiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:50'],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'duracion_dias' => ['required', 'integer', 'min:1', 'max:366'],
            'horas_diarias_max' => ['nullable', 'integer', 'min:1', 'max:24'],
            'estado' => ['required', 'in:activa,inactiva'],
        ];
    }
}
