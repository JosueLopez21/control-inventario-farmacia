<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categoria>
 */
class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    private static array $categorias = [
        'Analgésicos' => 'Medicamentos para aliviar el dolor, como paracetamol e ibuprofeno.',
        'Antibióticos' => 'Medicamentos para combatir infecciones bacterianas.',
        'Antiinflamatorios' => 'Medicamentos que reducen la inflamación y la hinchazón.',
        'Antihipertensivos' => 'Medicamentos para controlar la presión arterial alta.',
        'Vitaminas y Suplementos' => 'Suplementos nutricionales y complejos vitamínicos.',
        'Antihistamínicos' => 'Medicamentos para tratar alergias y reacciones alérgicas.',
        'Antidiabéticos' => 'Medicamentos para el control de la glucosa en sangre.',
        'Gastrointestinales' => 'Medicamentos para trastornos digestivos y estomacales.',
        'Dermatológicos' => 'Medicamentos de uso tópico para afecciones de la piel.',
        'Broncodilatadores' => 'Medicamentos para enfermedades respiratorias y asma.',
    ];

    private static int $index = 0;

    public function definition(): array
    {
        $nombres = array_keys(self::$categorias);
        $descripciones = array_values(self::$categorias);

        $i = self::$index % count($nombres);
        self::$index++;

        return [
            'nombre' => $nombres[$i],
            'descripcion' => $descripciones[$i],
        ];
    }
}
