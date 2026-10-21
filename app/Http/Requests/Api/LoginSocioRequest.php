<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginSocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dni' => ['required', 'string', 'max:15'],
            'pin' => ['required', 'digits:6'],
            'dispositivo' => ['nullable', 'string', 'max:60'],
        ];
    }
}
