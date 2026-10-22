<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Debes ingresar tu correo electrónico.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'password.required' => 'Debes ingresar tu contraseña.',
        ];
    }
}
