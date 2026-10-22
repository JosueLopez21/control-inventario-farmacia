<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarInventario() ?? false;
    }

    public function rules(): array
    {
        $lote = $this->route('lote');

        return [
            'medicamento_id' => ['required', 'integer', 'exists:medicamentos,id'],
            'numero_lote' => [
                'required', 'string', 'max:255',
                Rule::unique('lotes', 'numero_lote')->ignore($lote?->id),
            ],
            'fecha_vencimiento' => ['required', 'date'],
            'cantidad_stock' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'medicamento_id.required' => 'Debes seleccionar un medicamento.',
            'medicamento_id.exists' => 'El medicamento seleccionado no existe.',
            'numero_lote.required' => 'El número de lote es obligatorio.',
            'numero_lote.unique' => 'Ese número de lote ya está registrado.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento no es válida.',
            'cantidad_stock.required' => 'La cantidad es obligatoria.',
            'cantidad_stock.min' => 'La cantidad no puede ser negativa.',
        ];
    }
}
