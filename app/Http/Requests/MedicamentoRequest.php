<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarInventario() ?? false;
    }

    public function rules(): array
    {
        return [
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'laboratorio_id' => ['required', 'integer', 'exists:laboratorios,id'],
            'nombre_comercial' => ['required', 'string', 'max:255'],
            'principio_activo' => ['required', 'string', 'max:255'],
            'presentacion' => ['required', 'string', 'max:255'],
            'precio_venta' => ['required', 'numeric', 'gt:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'laboratorio_id.required' => 'Debes seleccionar un laboratorio.',
            'laboratorio_id.exists' => 'El laboratorio seleccionado no existe.',
            'nombre_comercial.required' => 'El nombre comercial es obligatorio.',
            'principio_activo.required' => 'El principio activo es obligatorio.',
            'presentacion.required' => 'La presentación es obligatoria.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_venta.gt' => 'El precio de venta debe ser mayor que 0.',
            'stock_minimo.min' => 'El stock mínimo no puede ser negativo.',
        ];
    }
}
