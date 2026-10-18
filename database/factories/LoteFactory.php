<?php

namespace Database\Factories;

use App\Models\Lote;
use App\Models\Medicamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lote>
 */
class LoteFactory extends Factory
{
    protected $model = Lote::class;

    public function definition(): array
    {
        return [
            'medicamento_id' => Medicamento::factory(),
            'numero_lote' => 'LOT-' . strtoupper(fake()->unique()->bothify('??-#####')),
            'fecha_vencimiento' => fake()->dateTimeBetween('+2 months', '+2 years')->format('Y-m-d'),
            'cantidad_stock' => fake()->numberBetween(20, 200),
        ];
    }

    public function porVencer(): static
    {
        return $this->state(fn () => [
            'fecha_vencimiento' => fake()->dateTimeBetween('+1 days', '+30 days')->format('Y-m-d'),
            'cantidad_stock' => fake()->numberBetween(5, 50),
        ]);
    }

    public function vencido(): static
    {
        return $this->state(fn () => [
            'fecha_vencimiento' => fake()->dateTimeBetween('-1 year', '-1 day')->format('Y-m-d'),
            'cantidad_stock' => fake()->numberBetween(1, 30),
        ]);
    }
}
