<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarInventario() ?? false;
    }

    public function rules(): array
    {
        $categoria = $this->route('categoria');

        return [
            'nombre' => [
                'required', 'string', 'max:255',
                Rule::unique('categorias', 'nombre')->ignore($categoria?->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ];
    }
}
