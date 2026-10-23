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
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Siembra un catálogo realista y liviano para la demostración de feria.
     *
     * El escenario es determinista: 300 medicamentos, 750 lotes y 500 ventas.
     * Se garantizan lotes vencidos, próximos a vencer, stock bajo y agotados
     * sin recurrir a consultas por registro ni a VentaService.
     */
    public function run(): void
    {
        mt_srand(20261023);

        $this->command->info('Sembrando catálogo de feria...');

        $this->call(UserSeeder::class);

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
        ])->mapWithKeys(function (array $data): array {
            $categoria = Categoria::create($data);

            return [$categoria->nombre => $categoria];
        });

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
        ])->map(fn (array $data) => Laboratorio::create($data));

        $catalogo = require database_path('seeders/data/catalogo_farmacia.php');
        $ahora = now();
        $medicamentosData = [];

        foreach ($catalogo['medicamentos'] as $indice => $base) {
            foreach ($catalogo['variantes'] as $variante => $sufijo) {
                $categoria = $categorias[$base['categoria']];
                $laboratorio = $laboratorios[($indice + $variante * 3) % $laboratorios->count()];
                $nombre = trim($base['marca'].' '.$sufijo);
                $precio = round(
                    $catalogo['precios_base'][$base['categoria']]
                    + ($indice % 7) * 0.75
                    + $variante * 2.25,
                    2
                );

                $medicamentosData[] = [
                    'categoria_id' => $categoria->id,
                    'laboratorio_id' => $laboratorio->id,
                    'nombre_comercial' => $nombre,
                    'principio_activo' => $base['principio_activo'],
                    'presentacion' => $catalogo['presentaciones'][$base['categoria']][$variante],
                    'precio_venta' => $precio,
                    'stock_minimo' => 5 + (($indice * 3 + $variante) % 21),
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }
        }

        Medicamento::insert($medicamentosData);
        $medicamentos = Medicamento::orderBy('id')->get();

        $this->command->info('Creando 750 lotes...');

        $lotesData = [];
        $numeroLote = 1;
        $extra = 0;
        $totalLotes = 750;

        foreach ($medicamentos as $indice => $medicamento) {
            $cantidadLotes = $indice % 2 === 0 ? 3 : 2;

            for ($posicion = 0; $posicion < $cantidadLotes; $posicion++) {
                $esBase = $posicion === 0;
                $tipo = 'futuro';

                if (! $esBase) {
                    $tipo = $extra < 75 ? 'vencido' : ($extra < 150 ? 'por_vencer' : 'futuro');
                    $extra++;
                }

                $fecha = match ($tipo) {
                    'vencido' => $ahora->copy()->subDays(mt_rand(15, 180)),
                    'por_vencer' => $ahora->copy()->addDays(mt_rand(2, 28)),
                    default => $ahora->copy()->addMonths(mt_rand($esBase ? 4 : 3, $esBase ? 24 : 18)),
                };

                $stock = match ($tipo) {
                    'vencido' => mt_rand(10, 80),
                    'por_vencer' => mt_rand(10, 45),
                    default => mt_rand($esBase ? 60 : 30, $esBase ? 180 : 120),
                };

                $lotesData[] = [
                    'medicamento_id' => $medicamento->id,
                    'numero_lote' => 'LOT-FAR-'.str_pad((string) $numeroLote++, 5, '0', STR_PAD_LEFT),
                    'fecha_vencimiento' => $fecha->toDateString(),
                    'cantidad_stock' => $stock,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }
        }

        if (count($lotesData) !== $totalLotes) {
            throw new \RuntimeException('El catálogo debe generar exactamente '.$totalLotes.' lotes.');
        }

        Lote::insert($lotesData);
        $lotes = Lote::with('medicamento:id,precio_venta')->get();

        $this->command->info('Creando 500 ventas de demostración...');

        $hoy = $ahora->copy()->startOfDay();
        $limitePorVencer = $hoy->copy()->addDays(30);
        $lotesVendibles = $lotes->filter(function (Lote $lote) use ($hoy, $limitePorVencer): bool {
            return $lote->cantidad_stock > 0
                && $lote->fecha_vencimiento->gte($hoy)
                && $lote->fecha_vencimiento->gt($limitePorVencer);
        })->values();

        $stockTemporal = $lotesVendibles->mapWithKeys(fn (Lote $lote) => [$lote->id => $lote->cantidad_stock]);
        $detallesData = [];
        $minutosTranscurridos = max(1, $hoy->diffInMinutes($ahora));

        for ($indiceVenta = 0; $indiceVenta < 500; $indiceVenta++) {
            $fechaVenta = $indiceVenta < 10
                ? $ahora->copy()->subMinutes(mt_rand(0, min(600, $minutosTranscurridos)))
                : $ahora->copy()->subDays(mt_rand(1, 180))->setTime(mt_rand(8, 20), mt_rand(0, 59));

            $venta = Venta::create([
                'fecha_venta' => $fechaVenta,
                'monto_total' => 0,
            ]);

            $detallesVenta = mt_rand(1, 4);
            $detallesCreados = 0;

            for ($detalle = 0; $detalle < $detallesVenta; $detalle++) {
                for ($intento = 0; $intento < 30; $intento++) {
                    $lote = $lotesVendibles[mt_rand(0, $lotesVendibles->count() - 1)];
                    $disponible = (int) ($stockTemporal[$lote->id] ?? 0);

                    if ($disponible <= 0) {
                        continue;
                    }

                    $cantidad = mt_rand(1, min(5, $disponible));

                    $detallesData[] = [
                        'venta_id' => $venta->id,
                        'lote_id' => $lote->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $lote->medicamento->precio_venta,
                        'created_at' => $fechaVenta,
                        'updated_at' => $fechaVenta,
                    ];

                    $stockTemporal[$lote->id] = $disponible - $cantidad;
                    $detallesCreados++;

                    break;
                }
            }

            if ($detallesCreados === 0) {
                $venta->delete();
            }
        }

        foreach (array_chunk($detallesData, 1000) as $bloque) {
            DetalleVenta::insert($bloque);
        }

        DB::statement(
            'UPDATE ventas SET monto_total = COALESCE((
                SELECT SUM(cantidad * precio_unitario)
                FROM detalles_venta
                WHERE detalles_venta.venta_id = ventas.id
            ), 0)'
        );

        $this->command->info('Ajustando escenarios de alertas...');

        $medicamentosConPorVencer = Lote::query()
            ->whereBetween('fecha_vencimiento', [$hoy->toDateString(), $limitePorVencer->toDateString()])
            ->where('cantidad_stock', '>', 0)
            ->pluck('medicamento_id')
            ->unique();

        $candidatos = $medicamentos
            ->whereNotIn('id', $medicamentosConPorVencer)
            ->sortBy('id')
            ->values();

        $agotados = $candidatos->take(15);
        $stockBajo = $candidatos->slice(15, 24);

        foreach ($agotados as $medicamento) {
            Lote::query()
                ->where('medicamento_id', $medicamento->id)
                ->where('fecha_vencimiento', '>=', $hoy->toDateString())
                ->update(['cantidad_stock' => 0]);
        }

        foreach ($stockBajo as $medicamento) {
            $lotesVigentes = Lote::query()
                ->where('medicamento_id', $medicamento->id)
                ->where('fecha_vencimiento', '>=', $hoy->toDateString())
                ->orderBy('fecha_vencimiento')
                ->get();

            $lotePrincipal = $lotesVigentes->first();

            if (! $lotePrincipal) {
                continue;
            }

            $lotePrincipal->update(['cantidad_stock' => max(1, $medicamento->stock_minimo - 1)]);

            Lote::query()
                ->whereKey($lotesVigentes->slice(1)->modelKeys())
                ->update(['cantidad_stock' => 0]);
        }

        $this->command->info('');
        $this->command->info('Seeding completado:');
        $this->command->info('   Categorías:      '.Categoria::count());
        $this->command->info('   Laboratorios:    '.Laboratorio::count());
        $this->command->info('   Medicamentos:    '.Medicamento::count());
        $this->command->info('   Lotes:           '.Lote::count());
        $this->command->info('   Ventas:          '.Venta::count());
        $this->command->info('   Detalles:        '.DetalleVenta::count());
        $this->command->info('   Lotes vencidos:  '.Lote::vencidos()->count());
        $this->command->info('   Por vencer:      '.Lote::porVencer(30)->count());
    }
}
