<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Respuesta del administrador a un pedido de datos personales: solo se responde una vez. */
class ResolverSolicitudDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('solicitud')->estado === 'pendiente';
    }

    public function rules(): array
    {
        return ['respuesta' => ['required', 'string', 'max:1000']];
    }
}
