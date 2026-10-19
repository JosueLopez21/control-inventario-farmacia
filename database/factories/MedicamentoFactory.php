<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Medicamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medicamento>
 */
class MedicamentoFactory extends Factory
{
    protected $model = Medicamento::class;

    private static array $medicamentos = [
        ['Panadol', 'Paracetamol', 'Tabletas 500mg x 20'],
        ['Advil', 'Ibuprofeno', 'Cápsulas 400mg x 10'],
        ['Amoxil', 'Amoxicilina', 'Cápsulas 500mg x 21'],
        ['Aspirina', 'Ácido acetilsalicílico', 'Tabletas 500mg x 20'],
        ['Losartán MK', 'Losartán potásico', 'Tabletas 50mg x 30'],
        ['Metformina Genfar', 'Metformina clorhidrato', 'Tabletas 850mg x 30'],
        ['Omeprazol MK', 'Omeprazol', 'Cápsulas 20mg x 14'],
        ['Loratadina Genfar', 'Loratadina', 'Tabletas 10mg x 10'],
        ['Azitromicina MK', 'Azitromicina', 'Tabletas 500mg x 3'],
        ['Diclofenaco Genfar', 'Diclofenaco sódico', 'Tabletas 50mg x 20'],
        ['Cetirizina MK', 'Cetirizina', 'Tabletas 10mg x 10'],
        ['Enalapril Genfar', 'Enalapril maleato', 'Tabletas 20mg x 30'],
        ['Vitamina C Bayer', 'Ácido ascórbico', 'Tabletas efervescentes 1g x 10'],
        ['Centrum', 'Multivitamínico', 'Tabletas x 30'],
        ['Ranitidina MK', 'Ranitidina', 'Tabletas 150mg x 20'],
        ['Ciprofloxacino Genfar', 'Ciprofloxacino', 'Tabletas 500mg x 10'],
        ['Glucosamina Bagó', 'Glucosamina sulfato', 'Sobres 1500mg x 15'],
        ['Naproxeno MK', 'Naproxeno sódico', 'Tabletas 275mg x 10'],
        ['Dexametasona Genfar', 'Dexametasona', 'Tabletas 0.5mg x 30'],
        ['Salbutamol MK', 'Salbutamol', 'Inhalador 100mcg x 200 dosis'],
    ];

    private static int $index = 0;

    public function definition(): array
    {
        $i = self::$index % count(self::$medicamentos);
        self::$index++;

        [$nombreComercial, $principioActivo, $presentacion] = self::$medicamentos[$i];

        // Sufijo único para evitar colisión en nombre_comercial (UNIQUE no está en medicamentos,
        // pero sí evitamos duplicados lógicos usando un índice)
        $sufijo = '-' . str_pad(self::$index, 3, '0', STR_PAD_LEFT);

        return [
            'categoria_id'     => Categoria::inRandomOrder()->value('id') ?? Categoria::factory(),
            'laboratorio_id'   => Laboratorio::inRandomOrder()->value('id') ?? Laboratorio::factory(),
            'nombre_comercial' => $nombreComercial . $sufijo,
            'principio_activo' => $principioActivo,
            'presentacion'     => $presentacion,
            'precio_venta'     => fake()->randomFloat(2, 5.00, 150.00),
            'stock_minimo'     => fake()->numberBetween(5, 25),
        ];
    }
}
