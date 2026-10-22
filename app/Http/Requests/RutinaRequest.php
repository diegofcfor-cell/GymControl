<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Rutina armada por el profesor:
 *  - Las filas de ejercicios que quedaron vacías se descartan antes de validar.
 *  - Exige al menos un ejercicio (máximo 80), cada uno con su día.
 */
class RutinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['ejercicios' => array_values(array_filter(
            (array) $this->input('ejercicios', []),
            fn ($e) => is_array($e) && trim((string) ($e['ejercicio'] ?? '')) !== ''
        ))]);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'objetivo' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'ejercicios' => ['required', 'array', 'min:1', 'max:80'],
            'ejercicios.*.dia' => ['required', 'string', 'max:30'],
            'ejercicios.*.ejercicio' => ['required', 'string', 'max:100'],
            'ejercicios.*.series' => ['nullable', 'integer', 'min:1', 'max:20'],
            'ejercicios.*.repeticiones' => ['nullable', 'string', 'max:20'],
            'ejercicios.*.descanso' => ['nullable', 'string', 'max:20'],
            'ejercicios.*.notas' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return ['ejercicios.required' => 'Cargá al menos un ejercicio.'];
    }
}
