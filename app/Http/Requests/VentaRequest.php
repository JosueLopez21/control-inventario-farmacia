<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeOperarVentas() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('items'))) {
            $decodificado = json_decode($this->input('items'), true);
            $this->merge(['items' => is_array($decodificado) ? $decodificado : []]);
        }
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicamento_id' => ['required', 'integer', 'exists:medicamentos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debes agregar al menos un medicamento a la venta.',
            'items.min' => 'Debes agregar al menos un medicamento a la venta.',
            'items.*.medicamento_id.required' => 'Hay un medicamento inválido en el carrito.',
            'items.*.medicamento_id.exists' => 'Uno de los medicamentos del carrito no existe.',
            'items.*.cantidad.required' => 'Debes indicar la cantidad de cada medicamento.',
            'items.*.cantidad.min' => 'La cantidad debe ser mayor o igual a 1.',
        ];
    }
}
