<?php

namespace App\Http\Requests;

use App\Models\SolicitudRutina;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Pedido de rutina que hace el socio desde su portal. La sesión del socio la controla el middleware `socio`. */
class SolicitudRutinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'objetivo' => ['required', Rule::in(SolicitudRutina::OBJETIVOS)],
            'dias_por_semana' => ['required', 'integer', 'min:1', 'max:7'],
            'nivel' => ['required', Rule::in(array_keys(SolicitudRutina::NIVELES))],
            'comentarios' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
