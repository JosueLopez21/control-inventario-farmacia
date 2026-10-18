<?php

namespace Database\Factories;

use App\Models\DetalleVenta;
use App\Models\Lote;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetalleVenta>
 */
class DetalleVentaFactory extends Factory
{
    protected $model = DetalleVenta::class;

    public function definition(): array
    {
        return [
            'venta_id' => Venta::factory(),
            'lote_id' => Lote::factory(),
            'cantidad' => fake()->numberBetween(1, 10),
            'precio_unitario' => fake()->randomFloat(2, 5.00, 150.00),
        ];
    }
}
