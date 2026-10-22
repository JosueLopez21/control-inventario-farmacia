<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarUsuarios() ?? false;
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');
        $esCreacion = $usuario === null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($usuario?->id),
            ],
            'rol' => ['required', Rule::in(['administrador', 'farmaceutico', 'doctor'])],
            'activo' => ['nullable', 'boolean'],
            'password' => $esCreacion
                ? ['required', 'confirmed', Password::min(8)]
                : ['nullable', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Ya existe un usuario con ese correo electrónico.',
            'rol.required' => 'Debes seleccionar un rol.',
            'rol.in' => 'El rol seleccionado no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
