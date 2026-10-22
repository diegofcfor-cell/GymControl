<?php

namespace App\Http\Requests;

use App\Models\SolicitudDatos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Pedido de rectificación o supresión desde el portal. La sesión del socio la controla el middleware `socio`. */
class SolicitudDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(array_keys(SolicitudDatos::TIPOS))],
            'detalle' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return ['detalle.min' => 'Contanos con un poco más de detalle qué dato querés corregir o eliminar.'];
    }
}
