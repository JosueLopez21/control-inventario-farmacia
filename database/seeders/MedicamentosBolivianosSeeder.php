<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Lote de medicamentos de uso común en Bolivia, de laboratorios bolivianos.
 *
 * Es información de demostración para la feria: los precios y las existencias son
 * ilustrativos y no representan el catálogo oficial de ningún laboratorio.
 * Es idempotente: se puede ejecutar varias veces sin duplicar nada.
 */
class MedicamentosBolivianosSeeder extends Seeder
{
    public function run(): void
    {
        $laboratorios = [
            'INTI' => 'Laboratorios INTI',
            'LAFAR' => 'Laboratorios Lafar',
            'COFAR' => 'COFAR',
            'VITA' => 'Laboratorios Vita',
        ];

        $categorias = [
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
            'Antiparasitarios' => 'Medicamentos contra parásitos intestinales, frecuentes en la región.',
        ];

        DB::transaction(function () use ($laboratorios, $categorias) {
            $labs = [];
            foreach ($laboratorios as $clave => $nombre) {
                $labs[$clave] = Laboratorio::firstOrCreate(['nombre' => $nombre], ['pais' => 'Bolivia']);
            }

            $cats = [];
            foreach ($categorias as $nombre => $descripcion) {
                $cats[$nombre] = Categoria::firstOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion]);
            }

            foreach ($this->catalogo() as $i => $fila) {
                [$lab, $categoria, $nombre, $principio, $presentacion, $precio, $stock, $minimo, $meses] = $fila;

                $medicamento = Medicamento::firstOrCreate(
                    [
                        'nombre_comercial' => $nombre,
                        'presentacion' => $presentacion,
                        'laboratorio_id' => $labs[$lab]->id,
                    ],
                    [
                        'categoria_id' => $cats[$categoria]->id,
                        'principio_activo' => $principio,
                        'precio_venta' => $precio,
                        'stock_minimo' => $minimo,
                    ]
                );

                $numeroLote = sprintf('BOL-%s-%03d', $lab, $i + 1);

                if (Lote::where('numero_lote', $numeroLote)->exists()) {
                    continue;
                }

                $lote = Lote::create([
                    'medicamento_id' => $medicamento->id,
                    'numero_lote' => $numeroLote,
                    'fecha_vencimiento' => now()->addMonths($meses)->endOfMonth()->toDateString(),
                    'cantidad_stock' => $stock,
                ]);

                MovimientoInventario::registrar($lote, 'ingreso', $stock, 0, null, 'Ingreso del lote de medicamentos bolivianos');
            }
        });
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: string, 4: string, 5: float, 6: int, 7: int, 8: int}>
     *                                                                                                                   [laboratorio, categoría, nombre, principio activo, presentación, precio Bs, stock, mínimo, meses de vigencia]
     */
    private function catalogo(): array
    {
        return [
            ['INTI', 'Analgésicos', 'Paracetamol INTI', 'Paracetamol', 'Tabletas 500 mg x 100', 18.00, 240, 40, 20],
            ['INTI', 'Antiinflamatorios', 'Ibuprofeno INTI', 'Ibuprofeno', 'Tabletas 400 mg x 100', 35.00, 160, 30, 22],
            ['INTI', 'Antibióticos', 'Amoxicilina INTI', 'Amoxicilina', 'Cápsulas 500 mg x 100', 70.00, 120, 25, 18],
            ['INTI', 'Antiinflamatorios', 'Diclofenaco INTI', 'Diclofenaco sódico', 'Tabletas 50 mg x 100', 30.00, 140, 30, 20],
            ['INTI', 'Gastrointestinales', 'Omeprazol INTI', 'Omeprazol', 'Cápsulas 20 mg x 28', 24.00, 130, 25, 24],
            ['INTI', 'Antihistamínicos', 'Loratadina INTI', 'Loratadina', 'Tabletas 10 mg x 100', 28.00, 110, 25, 22],
            ['INTI', 'Antiparasitarios', 'Albendazol INTI', 'Albendazol', 'Tabletas 400 mg x 2', 6.00, 200, 40, 24],
            ['LAFAR', 'Analgésicos', 'Paracetamol Lafar', 'Paracetamol', 'Jarabe 120 mg/5 ml x 60 ml', 11.00, 90, 20, 16],
            ['LAFAR', 'Antibióticos', 'Azitromicina Lafar', 'Azitromicina', 'Tabletas 500 mg x 3', 38.00, 80, 20, 20],
            ['LAFAR', 'Antibióticos', 'Ciprofloxacino Lafar', 'Ciprofloxacino', 'Tabletas 500 mg x 10', 32.00, 85, 20, 20],
            ['LAFAR', 'Antihipertensivos', 'Enalapril Lafar', 'Enalapril', 'Tabletas 10 mg x 30', 15.00, 100, 25, 24],
            ['LAFAR', 'Antihipertensivos', 'Losartán Lafar', 'Losartán potásico', 'Tabletas 50 mg x 30', 26.00, 100, 25, 24],
            ['LAFAR', 'Antidiabéticos', 'Metformina Lafar', 'Metformina', 'Tabletas 850 mg x 30', 22.00, 120, 30, 26],
            ['LAFAR', 'Antiparasitarios', 'Metronidazol Lafar', 'Metronidazol', 'Tabletas 500 mg x 20', 14.00, 150, 30, 22],
            ['COFAR', 'Antiinflamatorios', 'Naproxeno COFAR', 'Naproxeno sódico', 'Tabletas 550 mg x 20', 27.00, 90, 20, 20],
            ['COFAR', 'Analgésicos', 'Ketorolaco COFAR', 'Ketorolaco trometamina', 'Tabletas 10 mg x 10', 12.00, 110, 25, 18],
            ['COFAR', 'Gastrointestinales', 'Hioscina COFAR', 'Butilbromuro de hioscina', 'Tabletas 10 mg x 20', 20.00, 95, 20, 22],
            ['COFAR', 'Gastrointestinales', 'Sales de Rehidratación COFAR', 'Sales de rehidratación oral', 'Sobre 27,9 g', 4.50, 300, 60, 24],
            ['COFAR', 'Broncodilatadores', 'Salbutamol COFAR', 'Salbutamol', 'Inhalador 100 mcg x 200 dosis', 36.00, 60, 15, 18],
            ['COFAR', 'Antihistamínicos', 'Cetirizina COFAR', 'Cetirizina', 'Tabletas 10 mg x 10', 9.00, 130, 25, 24],
            ['VITA', 'Vitaminas y Suplementos', 'Vitamina C Vita', 'Ácido ascórbico', 'Tabletas 500 mg x 100', 25.00, 180, 35, 22],
            ['VITA', 'Vitaminas y Suplementos', 'Complejo B Vita', 'Complejo vitamínico B', 'Tabletas x 60', 29.00, 140, 30, 22],
            ['VITA', 'Dermatológicos', 'Clotrimazol Vita', 'Clotrimazol', 'Crema 1% x 20 g', 13.00, 85, 20, 20],
            ['VITA', 'Dermatológicos', 'Hidrocortisona Vita', 'Hidrocortisona', 'Crema 1% x 15 g', 15.00, 70, 15, 20],
            ['VITA', 'Broncodilatadores', 'Ambroxol Vita', 'Ambroxol', 'Jarabe 15 mg/5 ml x 120 ml', 16.00, 100, 20, 18],
            ['VITA', 'Antiparasitarios', 'Ivermectina Vita', 'Ivermectina', 'Tabletas 6 mg x 4', 17.00, 90, 20, 24],
        ];
    }
}
