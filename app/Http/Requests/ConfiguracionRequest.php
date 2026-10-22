<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Configuración del gimnasio: capacidad para el aforo, ingreso con QR y textos de la web pública.
 *  - El ingreso con QR es una casilla: sin marcar se guarda "0" (solo PIN), marcada "1" (HU33).
 */
class ConfiguracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['acceso_qr' => $this->boolean('acceso_qr') ? '1' : '0']);
    }

    public function rules(): array
    {
        return [
            'capacidad_maxima' => ['required', 'integer', 'min:1', 'max:5000'],
            'acceso_qr' => ['required', 'in:0,1'],
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
