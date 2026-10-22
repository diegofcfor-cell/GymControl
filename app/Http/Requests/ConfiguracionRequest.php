<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Configuración del gimnasio: capacidad para el aforo y textos de la web pública. */
class ConfiguracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'capacidad_maxima' => ['required', 'integer', 'min:1', 'max:5000'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'servicios' => ['nullable', 'string', 'max:2000'],
            'horarios' => ['nullable', 'string', 'max:1000'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'instagram' => ['nullable', 'string', 'max:60', 'regex:/^@?[A-Za-z0-9._]+$/'],
        ];
    }
}
