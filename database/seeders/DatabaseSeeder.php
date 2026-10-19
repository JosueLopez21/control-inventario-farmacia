<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\Venta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database con ~1000 datos representativos de farmacia en español.
     */
    public function run(): void
    {
        $this->command->info('🏥 Sembrando datos de farmacia (~1000 registros por tabla)...');

        // 1. Categorías (10)
        $this->command->info('📦 Creando categorías...');
        $categorias = collect([
            ['nombre' => 'Analgésicos', 'descripcion' => 'Medicamentos para aliviar el dolor, como paracetamol e ibuprofeno.'],
            ['nombre' => 'Antibióticos', 'descripcion' => 'Medicamentos para combatir infecciones bacterianas.'],
            ['nombre' => 'Antiinflamatorios', 'descripcion' => 'Medicamentos que reducen la inflamación y la hinchazón.'],
            ['nombre' => 'Antihipertensivos', 'descripcion' => 'Medicamentos para controlar la presión arterial alta.'],
            ['nombre' => 'Vitaminas y Suplementos', 'descripcion' => 'Suplementos nutricionales y complejos vitamínicos.'],
            ['nombre' => 'Antihistamínicos', 'descripcion' => 'Medicamentos para tratar alergias y reacciones alérgicas.'],
            ['nombre' => 'Antidiabéticos', 'descripcion' => 'Medicamentos para el control de la glucosa en sangre.'],
            ['nombre' => 'Gastrointestinales', 'descripcion' => 'Medicamentos para trastornos digestivos y estomacales.'],
            ['nombre' => 'Dermatológicos', 'descripcion' => 'Medicamentos de uso tópico para afecciones de la piel.'],
            ['nombre' => 'Broncodilatadores', 'descripcion' => 'Medicamentos para enfermedades respiratorias y asma.'],
        ])->map(fn ($data) => Categoria::create($data));

        // 2. Laboratorios (12)
        $this->command->info('🏭 Creando laboratorios...');
        $laboratorios = collect([
            ['nombre' => 'Pfizer', 'pais' => 'Estados Unidos', 'email' => 'contacto@pfizer.com'],
            ['nombre' => 'Bayer', 'pais' => 'Alemania', 'email' => 'contacto@bayer.com'],
            ['nombre' => 'Roche', 'pais' => 'Suiza', 'email' => 'contacto@roche.com'],
            ['nombre' => 'Novartis', 'pais' => 'Suiza', 'email' => 'contacto@novartis.com'],
            ['nombre' => 'Sanofi', 'pais' => 'Francia', 'email' => 'contacto@sanofi.com'],
            ['nombre' => 'Johnson & Johnson', 'pais' => 'Estados Unidos', 'email' => 'contacto@jnj.com'],
            ['nombre' => 'AstraZeneca', 'pais' => 'Reino Unido', 'email' => 'contacto@astrazeneca.com'],
            ['nombre' => 'GlaxoSmithKline', 'pais' => 'Reino Unido', 'email' => 'contacto@gsk.com'],
            ['nombre' => 'Abbott Laboratories', 'pais' => 'Estados Unidos', 'email' => 'contacto@abbott.com'],
            ['nombre' => 'Genfar', 'pais' => 'Colombia', 'email' => 'contacto@genfar.com'],
            ['nombre' => 'MK (Tecnoquímicas)', 'pais' => 'Colombia', 'email' => 'contacto@tecnoquimicas.com'],
            ['nombre' => 'Laboratorios Bagó', 'pais' => 'Argentina', 'email' => 'contacto@bago.com'],
        ])->map(fn ($data) => Laboratorio::create($data));

        // 3. Medicamentos: 30 reales fijos + 970 por factory = 1000 total
        $this->command->info('💊 Creando medicamentos (1000)...');
        $datosMedicamentos = [
            ['Panadol', 'Paracetamol', 'Tabletas 500mg x 20', 0, 5, 8.50, 15],
            ['Advil', 'Ibuprofeno', 'Cápsulas 400mg x 10', 0, 0, 12.00, 10],
            ['Aspirina', 'Ácido acetilsalicílico', 'Tabletas 500mg x 20', 0, 1, 6.50, 20],
            ['Amoxil', 'Amoxicilina', 'Cápsulas 500mg x 21', 1, 7, 25.00, 10],
            ['Azitromicina MK', 'Azitromicina', 'Tabletas 500mg x 3', 1, 10, 18.50, 8],
            ['Ciprofloxacino Genfar', 'Ciprofloxacino', 'Tabletas 500mg x 10', 1, 9, 22.00, 10],
            ['Claritromicina MK', 'Claritromicina', 'Tabletas 500mg x 14', 1, 10, 35.00, 8],
            ['Diclofenaco Genfar', 'Diclofenaco sódico', 'Tabletas 50mg x 20', 2, 9, 7.50, 15],
            ['Naproxeno MK', 'Naproxeno sódico', 'Tabletas 275mg x 10', 2, 10, 9.00, 12],
            ['Dexametasona Genfar', 'Dexametasona', 'Tabletas 0.5mg x 30', 2, 9, 11.00, 10],
            ['Losartán MK', 'Losartán potásico', 'Tabletas 50mg x 30', 3, 10, 15.00, 20],
            ['Enalapril Genfar', 'Enalapril maleato', 'Tabletas 20mg x 30', 3, 9, 12.50, 20],
            ['Amlodipino MK', 'Amlodipino besilato', 'Tabletas 5mg x 30', 3, 10, 14.00, 15],
            ['Vitamina C Bayer', 'Ácido ascórbico', 'Tabletas efervescentes 1g x 10', 4, 1, 10.00, 15],
            ['Centrum', 'Multivitamínico', 'Tabletas x 30', 4, 0, 45.00, 10],
            ['Complejo B MK', 'Complejo vitamínico B', 'Tabletas x 30', 4, 10, 12.00, 15],
            ['Loratadina Genfar', 'Loratadina', 'Tabletas 10mg x 10', 5, 9, 5.50, 10],
            ['Cetirizina MK', 'Cetirizina', 'Tabletas 10mg x 10', 5, 10, 6.00, 10],
            ['Metformina Genfar', 'Metformina clorhidrato', 'Tabletas 850mg x 30', 6, 9, 8.00, 25],
            ['Omeprazol MK', 'Omeprazol', 'Cápsulas 20mg x 14', 7, 10, 9.50, 15],
            ['Ranitidina MK', 'Ranitidina', 'Tabletas 150mg x 20', 7, 10, 7.00, 12],
            ['Metoclopramida MK', 'Metoclopramida', 'Tabletas 10mg x 20', 7, 10, 6.50, 10],
            ['Clotrimazol Genfar', 'Clotrimazol', 'Crema 1% x 20g', 8, 9, 8.50, 8],
            ['Hidrocortisona Genfar', 'Hidrocortisona', 'Crema 1% x 15g', 8, 9, 10.00, 8],
            ['Salbutamol MK', 'Salbutamol', 'Inhalador 100mcg x 200 dosis', 9, 10, 35.00, 10],
            ['Ibuprofeno Genfar', 'Ibuprofeno', 'Suspensión 100mg/5mL x 120mL', 0, 9, 11.00, 12],
            ['Acetaminofén Genfar', 'Acetaminofén', 'Jarabe 150mg/5mL x 120mL', 0, 9, 9.00, 15],
            ['Glucosamina Bagó', 'Glucosamina sulfato', 'Sobres 1500mg x 15', 4, 11, 28.00, 8],
            ['Simvastatina Genfar', 'Simvastatina', 'Tabletas 20mg x 30', 3, 9, 16.00, 15],
            ['Fluoxetina Genfar', 'Fluoxetina', 'Cápsulas 20mg x 14', 7, 9, 14.00, 10],
        ];

        $medicamentos = collect($datosMedicamentos)->map(function ($med) use ($categorias, $laboratorios) {
            return Medicamento::create([
                'categoria_id'     => $categorias[$med[3]]->id,
                'laboratorio_id'   => $laboratorios[$med[4]]->id,
                'nombre_comercial' => $med[0],
                'principio_activo' => $med[1],
                'presentacion'     => $med[2],
                'precio_venta'     => $med[5],
                'stock_minimo'     => $med[6],
            ]);
        });

        // 970 medicamentos adicionales con factory = 1000 total
        $medicamentosFactory = Medicamento::factory(970)->create();
        $medicamentos = $medicamentos->merge($medicamentosFactory);

        // 4. Lotes: 1 por medicamento = 1000 lotes total
        $this->command->info('📋 Creando lotes (1000)...');
        $contadorLotes = 1;

        $medicamentos->each(function (Medicamento $medicamento, $index) use (&$contadorLotes) {
            // Distribuir los 1000 lotes:
            // - Primeros 150 medicamentos: lote vencido (para probar regla #3 y scope vencidos)
            // - Siguientes 100 medicamentos: lote por vencer <30 días (para probar regla #1 FEFO)
            // - Restantes 750 medicamentos: lote vigente (3 a 24 meses)
            if ($index < 150) {
                $fecha = now()->subDays(rand(15, 180))->format('Y-m-d');
            } elseif ($index < 250) {
                $fecha = now()->addDays(rand(3, 25))->format('Y-m-d');
            } else {
                $fecha = now()->addMonths(rand(3, 24))->format('Y-m-d');
            }

            Lote::create([
                'medicamento_id'    => $medicamento->id,
                'numero_lote'       => 'LOT-' . str_pad($contadorLotes++, 6, '0', STR_PAD_LEFT),
                'fecha_vencimiento' => $fecha,
                'cantidad_stock'    => rand(30, 200),
            ]);
        });

        // Poner 50 medicamentos vigentes con stock bajo mínimo - Regla #2
        $medicamentos->skip(250)->take(50)->each(function (Medicamento $medicamento) {
            $medicamento->lotes()
                ->where('fecha_vencimiento', '>=', now()->toDateString())
                ->update(['cantidad_stock' => rand(1, 3)]);
        });

        // 5. Ventas con Detalles: 1000 ventas
        $this->command->info('🛒 Creando ventas (1000)...');

        for ($s = 0; $s < 1000; $s++) {
            $venta = Venta::create([
                'fecha_venta' => fake()->dateTimeBetween('-12 months', 'now'),
                'monto_total' => 0,
            ]);

            $montoTotal = 0;
            $detalles   = rand(1, 6);

            for ($d = 0; $d < $detalles; $d++) {
                $loteDisponible = Lote::where('fecha_vencimiento', '>=', now()->toDateString())
                    ->where('cantidad_stock', '>', 0)
                    ->inRandomOrder()
                    ->first();

                if (! $loteDisponible) {
                    continue;
                }

                $maxQty        = min($loteDisponible->cantidad_stock, 8);
                $qty           = rand(1, max(1, $maxQty));
                $precioUnitario = $loteDisponible->medicamento->precio_venta;

                DetalleVenta::create([
                    'venta_id'       => $venta->id,
                    'lote_id'        => $loteDisponible->id,
                    'cantidad'       => $qty,
                    'precio_unitario' => $precioUnitario,
                ]);

                $loteDisponible->decrement('cantidad_stock', $qty);
                $montoTotal += $qty * $precioUnitario;
            }

            $venta->update(['monto_total' => $montoTotal]);
        }

        $this->command->info('');
        $this->command->info('✅ Seeding completado exitosamente (~1000 por tabla):');
        $this->command->info("   📦 Categorías:      " . Categoria::count());
        $this->command->info("   🏭 Laboratorios:    " . Laboratorio::count());
        $this->command->info("   💊 Medicamentos:    " . Medicamento::count());
        $this->command->info("   📋 Lotes:            " . Lote::count());
        $this->command->info("   🛒 Ventas:           " . Venta::count());
        $this->command->info("   📝 Detalles:         " . DetalleVenta::count());
        $this->command->info("   ⚠️  Lotes vencidos:  " . Lote::vencidos()->count());
        $this->command->info("   ⏰ Lotes por vencer: " . Lote::porVencer()->count());
        $this->command->info("   📊 TOTAL registros:  " . (Categoria::count() + Laboratorio::count() + Medicamento::count() + Lote::count() + Venta::count() + DetalleVenta::count()));
    }
}
