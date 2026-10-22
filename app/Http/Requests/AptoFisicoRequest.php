<?php

namespace App\Http\Requests;

use App\Models\AptoFisico;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Carga de un apto físico:
 *  - Solo puede cargarlo quien autorice AptoFisicoPolicy::create (administrador o recepción).
 *  - Es un dato de salud (sensible): el socio tiene que haber dado su consentimiento antes.
 *  - La emisión no puede ser futura y el vencimiento es posterior a la emisión.
 *  - El archivo es opcional: PDF o imagen de hasta 5 MB.
 */
class AptoFisicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', AptoFisico::class);
    }

    public function rules(): array
    {
        return [
            'fecha_emision' => ['required', 'date', 'before_or_equal:today'],
            'fecha_vencimiento' => ['required', 'date', 'after:fecha_emision'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->route('socio')->dioConsentimiento()) {
                    $validator->errors()->add('consentimiento', 'El socio todavía no aceptó el aviso de privacidad. Registralo en "Editar" antes de cargar el apto.');
                }
            },
        ];
    }
}
