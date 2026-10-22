<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Pregunta frecuente del chatbot: las palabras clave van separadas por coma. */
class PreguntaFrecuenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pregunta' => ['required', 'string', 'max:200'],
            'respuesta' => ['required', 'string', 'max:2000'],
            'palabras_clave' => ['nullable', 'string', 'max:255'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}
