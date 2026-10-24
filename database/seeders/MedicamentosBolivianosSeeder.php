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
 * Catálogo de medicamentos de uso común en Bolivia, de laboratorios bolivianos.
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
            'BAGO' => 'Bagó de Bolivia',
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
            ['BAGO', 'Analgésicos', 'Paracetamol Bagó Bolivia', 'Paracetamol', 'Tabletas 500 mg x 20', 8.50, 200, 40, 20],
            ['INTI', 'Analgésicos', 'Paracetamol INTI', 'Paracetamol', 'Tabletas 1 g x 20', 12.00, 150, 30, 20],
            ['COFAR', 'Analgésicos', 'Paracetamol COFAR', 'Paracetamol', 'Gotas 100 mg/ml x 15 ml', 14.00, 80, 20, 18],
            ['VITA', 'Analgésicos', 'Dipirona Vita', 'Metamizol sódico', 'Tabletas 500 mg x 20', 9.00, 140, 30, 20],
            ['LAFAR', 'Analgésicos', 'Dipirona Lafar', 'Metamizol sódico', 'Ampollas 1 g/2 ml x 5', 16.00, 70, 15, 16],
            ['COFAR', 'Analgésicos', 'Tramadol COFAR', 'Tramadol', 'Cápsulas 50 mg x 10', 22.00, 60, 15, 18],
            ['BAGO', 'Analgésicos', 'Aspirina Bagó Bolivia', 'Ácido acetilsalicílico', 'Tabletas 500 mg x 20', 10.00, 120, 25, 22],
            ['INTI', 'Analgésicos', 'Acetaminofén INTI Pediátrico', 'Paracetamol', 'Jarabe 160 mg/5 ml x 90 ml', 13.50, 90, 20, 16],
            ['VITA', 'Analgésicos', 'Paracetamol Cafeína Vita', 'Paracetamol y cafeína', 'Tabletas 500/65 mg x 20', 11.00, 100, 20, 20],
            ['LAFAR', 'Analgésicos', 'Ketorolaco Lafar', 'Ketorolaco trometamina', 'Ampollas 30 mg/ml x 3', 18.00, 12, 20, 18],
            ['BAGO', 'Antiinflamatorios', 'Ibuprofeno Bagó Bolivia', 'Ibuprofeno', 'Tabletas 600 mg x 20', 16.00, 120, 25, 22],
            ['VITA', 'Antiinflamatorios', 'Ibuprofeno Vita', 'Ibuprofeno', 'Suspensión 100 mg/5 ml x 120 ml', 17.00, 85, 20, 16],
            ['LAFAR', 'Antiinflamatorios', 'Diclofenaco Lafar', 'Diclofenaco sódico', 'Gel 1% x 50 g', 21.00, 90, 20, 20],
            ['COFAR', 'Antiinflamatorios', 'Diclofenaco COFAR', 'Diclofenaco sódico', 'Ampollas 75 mg/3 ml x 3', 15.00, 75, 15, 18],
            ['INTI', 'Antiinflamatorios', 'Naproxeno INTI', 'Naproxeno sódico', 'Tabletas 275 mg x 20', 19.00, 100, 20, 22],
            ['BAGO', 'Antiinflamatorios', 'Meloxicam Bagó Bolivia', 'Meloxicam', 'Tabletas 15 mg x 10', 24.00, 80, 15, 24],
            ['VITA', 'Antiinflamatorios', 'Piroxicam Vita', 'Piroxicam', 'Cápsulas 20 mg x 10', 14.00, 70, 15, 22],
            ['LAFAR', 'Antiinflamatorios', 'Nimesulida Lafar', 'Nimesulida', 'Tabletas 100 mg x 20', 18.00, 90, 20, 20],
            ['COFAR', 'Antiinflamatorios', 'Betametasona COFAR', 'Betametasona', 'Ampollas 4 mg/ml x 1', 20.00, 50, 10, 18],
            ['INTI', 'Antiinflamatorios', 'Prednisona INTI', 'Prednisona', 'Tabletas 20 mg x 20', 17.00, 110, 20, 24],
            ['VITA', 'Antiinflamatorios', 'Dexametasona Vita', 'Dexametasona', 'Ampollas 4 mg/ml x 1', 9.00, 95, 20, 18],
            ['BAGO', 'Antibióticos', 'Amoxicilina Bagó Bolivia', 'Amoxicilina', 'Suspensión 250 mg/5 ml x 60 ml', 23.00, 90, 20, 14],
            ['LAFAR', 'Antibióticos', 'Amoxicilina Lafar', 'Amoxicilina', 'Cápsulas 500 mg x 21', 28.00, 110, 25, 20],
            ['COFAR', 'Antibióticos', 'Amoxicilina Clavulánico COFAR', 'Amoxicilina y ácido clavulánico', 'Tabletas 875/125 mg x 14', 85.00, 60, 15, 20],
            ['INTI', 'Antibióticos', 'Azitromicina INTI', 'Azitromicina', 'Suspensión 200 mg/5 ml x 15 ml', 34.00, 55, 12, 18],
            ['VITA', 'Antibióticos', 'Cefalexina Vita', 'Cefalexina', 'Cápsulas 500 mg x 20', 42.00, 80, 15, 20],
            ['BAGO', 'Antibióticos', 'Ciprofloxacino Bagó Bolivia', 'Ciprofloxacino', 'Tabletas 500 mg x 14', 36.00, 70, 15, 22],
            ['LAFAR', 'Antibióticos', 'Claritromicina Lafar', 'Claritromicina', 'Tabletas 500 mg x 14', 72.00, 40, 10, 20],
            ['COFAR', 'Antibióticos', 'Doxiciclina COFAR', 'Doxiciclina', 'Cápsulas 100 mg x 10', 19.00, 75, 15, 22],
            ['INTI', 'Antibióticos', 'Trimetoprima Sulfametoxazol INTI', 'Sulfametoxazol y trimetoprima', 'Tabletas 800/160 mg x 20', 26.00, 95, 20, 24],
            ['VITA', 'Antibióticos', 'Ampicilina Vita', 'Ampicilina', 'Cápsulas 500 mg x 20', 30.00, 65, 15, 18],
            ['LAFAR', 'Antibióticos', 'Penicilina Benzatínica Lafar', 'Penicilina G benzatínica', 'Frasco ampolla 1.200.000 UI', 24.00, 8, 15, 16],
            ['COFAR', 'Antibióticos', 'Gentamicina COFAR', 'Gentamicina', 'Ampollas 80 mg/2 ml x 3', 13.00, 60, 12, 20],
            ['BAGO', 'Antibióticos', 'Levofloxacino Bagó Bolivia', 'Levofloxacino', 'Tabletas 500 mg x 7', 48.00, 45, 10, 22],
            ['BAGO', 'Antihipertensivos', 'Enalapril Bagó Bolivia', 'Enalapril', 'Tabletas 20 mg x 30', 18.00, 100, 20, 24],
            ['INTI', 'Antihipertensivos', 'Losartán INTI', 'Losartán potásico', 'Tabletas 100 mg x 30', 32.00, 90, 20, 24],
            ['COFAR', 'Antihipertensivos', 'Amlodipino COFAR', 'Amlodipino', 'Tabletas 5 mg x 30', 17.00, 130, 25, 26],
            ['VITA', 'Antihipertensivos', 'Amlodipino Vita', 'Amlodipino', 'Tabletas 10 mg x 30', 22.00, 85, 20, 26],
            ['LAFAR', 'Antihipertensivos', 'Atenolol Lafar', 'Atenolol', 'Tabletas 50 mg x 30', 14.00, 110, 25, 24],
            ['INTI', 'Antihipertensivos', 'Captopril INTI', 'Captopril', 'Tabletas 25 mg x 30', 11.00, 100, 20, 24],
            ['BAGO', 'Antihipertensivos', 'Hidroclorotiazida Bagó Bolivia', 'Hidroclorotiazida', 'Tabletas 25 mg x 30', 9.50, 90, 20, 26],
            ['COFAR', 'Antihipertensivos', 'Furosemida COFAR', 'Furosemida', 'Tabletas 40 mg x 20', 8.00, 100, 20, 24],
            ['VITA', 'Antihipertensivos', 'Nifedipino Vita', 'Nifedipino', 'Cápsulas 10 mg x 30', 15.00, 70, 15, 20],
            ['LAFAR', 'Antihipertensivos', 'Propranolol Lafar', 'Propranolol', 'Tabletas 40 mg x 30', 10.00, 80, 15, 24],
            ['INTI', 'Antihipertensivos', 'Valsartán INTI', 'Valsartán', 'Tabletas 80 mg x 28', 45.00, 50, 10, 24],
            ['BAGO', 'Antidiabéticos', 'Metformina Bagó Bolivia', 'Metformina', 'Tabletas 500 mg x 60', 18.00, 130, 25, 26],
            ['INTI', 'Antidiabéticos', 'Metformina INTI', 'Metformina', 'Tabletas 850 mg x 60', 26.00, 110, 25, 26],
            ['COFAR', 'Antidiabéticos', 'Glibenclamida COFAR', 'Glibenclamida', 'Tabletas 5 mg x 30', 9.00, 100, 20, 26],
            ['VITA', 'Antidiabéticos', 'Glimepirida Vita', 'Glimepirida', 'Tabletas 2 mg x 30', 28.00, 60, 12, 24],
            ['LAFAR', 'Antidiabéticos', 'Insulina NPH Lafar', 'Insulina humana NPH', 'Frasco 100 UI/ml x 10 ml', 95.00, 25, 8, 12],
            ['INTI', 'Antidiabéticos', 'Sitagliptina INTI', 'Sitagliptina', 'Tabletas 100 mg x 28', 110.00, 30, 8, 24],
            ['VITA', 'Vitaminas y Suplementos', 'Vitamina C Vita Efervescente', 'Ácido ascórbico', 'Tabletas efervescentes 1 g x 10', 22.00, 120, 25, 20],
            ['BAGO', 'Vitaminas y Suplementos', 'Complejo B Bagó Bolivia', 'Complejo vitamínico B', 'Ampollas x 3', 18.00, 80, 15, 18],
            ['INTI', 'Vitaminas y Suplementos', 'Ácido Fólico INTI', 'Ácido fólico', 'Tabletas 5 mg x 30', 7.00, 150, 30, 26],
            ['LAFAR', 'Vitaminas y Suplementos', 'Hierro Lafar', 'Sulfato ferroso', 'Tabletas 300 mg x 30', 12.00, 130, 25, 24],
            ['COFAR', 'Vitaminas y Suplementos', 'Calcio con Vitamina D COFAR', 'Carbonato de calcio y vitamina D3', 'Tabletas 600 mg/400 UI x 60', 34.00, 90, 20, 24],
            ['VITA', 'Vitaminas y Suplementos', 'Vitamina D3 Vita', 'Colecalciferol', 'Gotas 400 UI/gota x 10 ml', 38.00, 60, 12, 20],
            ['BAGO', 'Vitaminas y Suplementos', 'Zinc Bagó Bolivia', 'Sulfato de zinc', 'Jarabe 10 mg/5 ml x 100 ml', 20.00, 70, 15, 18],
            ['INTI', 'Vitaminas y Suplementos', 'Magnesio INTI', 'Citrato de magnesio', 'Tabletas 400 mg x 30', 27.00, 75, 15, 24],
            ['VITA', 'Vitaminas y Suplementos', 'Omega 3 Vita', 'Omega 3', 'Cápsulas 1000 mg x 30', 45.00, 65, 12, 24],
            ['LAFAR', 'Vitaminas y Suplementos', 'Multivitamínico Lafar', 'Multivitamínico y minerales', 'Tabletas x 30', 32.00, 85, 15, 22],
            ['COFAR', 'Vitaminas y Suplementos', 'Vitamina E COFAR', 'Tocoferol', 'Cápsulas 400 UI x 30', 29.00, 60, 12, 22],
            ['VITA', 'Vitaminas y Suplementos', 'Vitamina B12 Vita', 'Cianocobalamina', 'Ampollas 1000 mcg x 3', 21.00, 55, 10, 18],
            ['BAGO', 'Antihistamínicos', 'Loratadina Bagó Bolivia', 'Loratadina', 'Jarabe 5 mg/5 ml x 60 ml', 12.00, 90, 20, 18],
            ['LAFAR', 'Antihistamínicos', 'Loratadina Lafar', 'Loratadina', 'Tabletas 10 mg x 10', 7.50, 140, 30, 24],
            ['INTI', 'Antihistamínicos', 'Cetirizina INTI', 'Cetirizina', 'Gotas 10 mg/ml x 20 ml', 16.00, 70, 15, 20],
            ['VITA', 'Antihistamínicos', 'Clorfeniramina Vita', 'Clorfeniramina', 'Tabletas 4 mg x 20', 6.00, 120, 25, 24],
            ['COFAR', 'Antihistamínicos', 'Desloratadina COFAR', 'Desloratadina', 'Tabletas 5 mg x 10', 18.00, 80, 15, 24],
            ['LAFAR', 'Antihistamínicos', 'Difenhidramina Lafar', 'Difenhidramina', 'Ampollas 50 mg/ml x 1', 6.50, 60, 12, 20],
            ['BAGO', 'Gastrointestinales', 'Omeprazol Bagó Bolivia', 'Omeprazol', 'Cápsulas 40 mg x 14', 26.00, 100, 20, 24],
            ['LAFAR', 'Gastrointestinales', 'Omeprazol Lafar', 'Omeprazol', 'Cápsulas 20 mg x 14', 14.00, 130, 25, 24],
            ['COFAR', 'Gastrointestinales', 'Ranitidina COFAR', 'Ranitidina', 'Tabletas 150 mg x 20', 10.00, 100, 20, 22],
            ['VITA', 'Gastrointestinales', 'Hidróxido de Aluminio Vita', 'Hidróxido de aluminio y magnesio', 'Suspensión 360 mL', 15.00, 90, 20, 18],
            ['INTI', 'Gastrointestinales', 'Loperamida INTI', 'Loperamida', 'Cápsulas 2 mg x 12', 9.00, 110, 20, 24],
            ['BAGO', 'Gastrointestinales', 'Metoclopramida Bagó Bolivia', 'Metoclopramida', 'Tabletas 10 mg x 20', 8.00, 95, 20, 24],
            ['LAFAR', 'Gastrointestinales', 'Hioscina Lafar', 'Butilbromuro de hioscina', 'Ampollas 20 mg/ml x 3', 14.00, 70, 15, 20],
            ['COFAR', 'Gastrointestinales', 'Domperidona COFAR', 'Domperidona', 'Tabletas 10 mg x 30', 19.00, 80, 15, 24],
            ['VITA', 'Gastrointestinales', 'Lactulosa Vita', 'Lactulosa', 'Jarabe 66,7% x 120 ml', 24.00, 60, 12, 18],
            ['INTI', 'Gastrointestinales', 'Pantoprazol INTI', 'Pantoprazol', 'Tabletas 40 mg x 14', 31.00, 75, 15, 24],
            ['LAFAR', 'Gastrointestinales', 'Suero Oral Lafar', 'Sales de rehidratación oral', 'Sobre 20,5 g', 4.00, 250, 50, 24],
            ['VITA', 'Dermatológicos', 'Betametasona Vita', 'Betametasona', 'Crema 0,05% x 20 g', 14.00, 75, 15, 20],
            ['BAGO', 'Dermatológicos', 'Clotrimazol Bagó Bolivia', 'Clotrimazol', 'Crema vaginal 1% x 35 g', 18.00, 65, 12, 20],
            ['INTI', 'Dermatológicos', 'Ketoconazol INTI', 'Ketoconazol', 'Crema 2% x 20 g', 16.00, 70, 15, 22],
            ['COFAR', 'Dermatológicos', 'Gentamicina Crema COFAR', 'Gentamicina', 'Crema 0,1% x 15 g', 9.00, 80, 15, 20],
            ['LAFAR', 'Dermatológicos', 'Aciclovir Lafar', 'Aciclovir', 'Crema 5% x 5 g', 17.00, 60, 12, 20],
            ['VITA', 'Dermatológicos', 'Óxido de Zinc Vita', 'Óxido de zinc', 'Pomada x 30 g', 8.00, 90, 20, 24],
            ['INTI', 'Dermatológicos', 'Miconazol INTI', 'Miconazol', 'Crema 2% x 20 g', 13.00, 70, 15, 22],
            ['BAGO', 'Dermatológicos', 'Mupirocina Bagó Bolivia', 'Mupirocina', 'Ungüento 2% x 15 g', 38.00, 45, 10, 20],
            ['BAGO', 'Broncodilatadores', 'Salbutamol Bagó Bolivia', 'Salbutamol', 'Jarabe 2 mg/5 ml x 120 ml', 15.00, 80, 15, 18],
            ['INTI', 'Broncodilatadores', 'Ambroxol INTI', 'Ambroxol', 'Tabletas 30 mg x 20', 12.00, 100, 20, 22],
            ['LAFAR', 'Broncodilatadores', 'Bromhexina Lafar', 'Bromhexina', 'Jarabe 4 mg/5 ml x 120 ml', 13.00, 85, 15, 18],
            ['COFAR', 'Broncodilatadores', 'Dextrometorfano COFAR', 'Dextrometorfano', 'Jarabe 15 mg/5 ml x 120 ml', 17.00, 75, 15, 18],
            ['VITA', 'Broncodilatadores', 'Montelukast Vita', 'Montelukast', 'Tabletas 10 mg x 30', 52.00, 40, 10, 24],
            ['BAGO', 'Broncodilatadores', 'Aminofilina Bagó Bolivia', 'Aminofilina', 'Ampollas 240 mg/10 ml x 5', 25.00, 35, 8, 20],
            ['INTI', 'Broncodilatadores', 'Acetilcisteína INTI', 'Acetilcisteína', 'Sobres 600 mg x 10', 33.00, 60, 12, 20],
            ['LAFAR', 'Antiparasitarios', 'Albendazol Lafar', 'Albendazol', 'Suspensión 400 mg/20 ml x 20 ml', 9.00, 120, 25, 22],
            ['COFAR', 'Antiparasitarios', 'Mebendazol COFAR', 'Mebendazol', 'Tabletas 100 mg x 6', 7.00, 130, 25, 24],
            ['BAGO', 'Antiparasitarios', 'Tinidazol Bagó Bolivia', 'Tinidazol', 'Tabletas 500 mg x 4', 11.00, 100, 20, 24],
            ['INTI', 'Antiparasitarios', 'Metronidazol INTI', 'Metronidazol', 'Suspensión 125 mg/5 ml x 120 ml', 12.00, 90, 20, 18],
            ['VITA', 'Antiparasitarios', 'Secnidazol Vita', 'Secnidazol', 'Tabletas 500 mg x 4', 22.00, 70, 15, 22],
            ['COFAR', 'Antiparasitarios', 'Nitazoxanida COFAR', 'Nitazoxanida', 'Tabletas 500 mg x 6', 28.00, 55, 12, 22],
            ['LAFAR', 'Antiparasitarios', 'Praziquantel Lafar', 'Praziquantel', 'Tabletas 600 mg x 4', 24.00, 40, 8, 24],
            ['INTI', 'Antiparasitarios', 'Ivermectina INTI', 'Ivermectina', 'Gotas 6 mg/ml x 5 ml', 15.00, 3, 10, 0],
        ];
    }
}
