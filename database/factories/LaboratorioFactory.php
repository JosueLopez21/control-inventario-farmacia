<?php

namespace Database\Factories;

use App\Models\Laboratorio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Laboratorio>
 */
class LaboratorioFactory extends Factory
{
    protected $model = Laboratorio::class;

    private static array $laboratorios = [
        ['nombre' => 'Pfizer', 'pais' => 'Estados Unidos'],
        ['nombre' => 'Bayer', 'pais' => 'Alemania'],
        ['nombre' => 'Roche', 'pais' => 'Suiza'],
        ['nombre' => 'Novartis', 'pais' => 'Suiza'],
        ['nombre' => 'Sanofi', 'pais' => 'Francia'],
        ['nombre' => 'Johnson & Johnson', 'pais' => 'Estados Unidos'],
        ['nombre' => 'AstraZeneca', 'pais' => 'Reino Unido'],
        ['nombre' => 'GlaxoSmithKline', 'pais' => 'Reino Unido'],
        ['nombre' => 'Abbott Laboratories', 'pais' => 'Estados Unidos'],
        ['nombre' => 'Genfar', 'pais' => 'Colombia'],
        ['nombre' => 'MK (Tecnoquímicas)', 'pais' => 'Colombia'],
        ['nombre' => 'Laboratorios Bagó', 'pais' => 'Argentina'],
    ];

    private static int $index = 0;

    public function definition(): array
    {
        $i = self::$index % count(self::$laboratorios);
        self::$index++;

        $lab = self::$laboratorios[$i];

        return [
            'nombre' => $lab['nombre'],
            'pais' => $lab['pais'],
            'direccion' => fake()->address(),
            'telefono' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
        ];
    }
}
