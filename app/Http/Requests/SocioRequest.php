<?php

namespace App\Http\Requests;

use App\Models\Socio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta y edición de socios:
 *  - El DNI es único (al editar se ignora el del propio socio).
 *  - En el alta solo se elige una membresía activa; al editar se permite conservar una que luego se desactivó.
 *  - En el alta el socio debe aceptar el aviso de privacidad (Ley 25.326, art. 5); al editar se puede registrar
 *    el consentimiento de un socio dado de alta antes de que existiera el aviso.
 */
class SocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Socio|null $socio */
        $socio = $this->route('socio');

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'digits_between:7,9', Rule::unique('socios', 'dni')->ignore($socio?->id)],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'membresia_id' => $socio
                ? ['required', 'exists:membresias,id']
                : ['required', Rule::exists('membresias', 'id')->where('estado', 'activa')],
            'consentimiento_datos' => $socio ? ['nullable', 'boolean'] : ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'consentimiento_datos.accepted' => 'El socio tiene que leer y aceptar el aviso de privacidad.',
        ];
    }
}
