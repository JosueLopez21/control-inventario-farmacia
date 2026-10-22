<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LaboratorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarInventario() ?? false;
    }

    public function rules(): array
    {
        $laboratorio = $this->route('laboratorio');

        return [
            'nombre' => [
                'required', 'string', 'max:255',
                Rule::unique('laboratorios', 'nombre')->ignore($laboratorio?->id),
            ],
            'pais' => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del laboratorio es obligatorio.',
            'nombre.unique' => 'Ya existe un laboratorio con ese nombre.',
            'pais.required' => 'El país es obligatorio.',
            'email.email' => 'El correo del laboratorio no es válido.',
        ];
    }
}
