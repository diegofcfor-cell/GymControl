<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Registro de un pago: plan abonado y método de pago. Importe y período los calcula PagoService. */
class PagoRequest extends FormRequest
{
    public const METODOS = ['efectivo', 'transferencia', 'debito', 'credito'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'membresia_id' => ['required', 'exists:membresias,id'],
            'metodo_pago' => ['required', 'in:'.implode(',', self::METODOS)],
        ];
    }
}
