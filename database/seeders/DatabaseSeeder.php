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
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Siembra un catálogo realista y liviano para la demostración de feria.
     *
     * El escenario es determinista: 1000 medicamentos, 2000 lotes y 1000 ventas.
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
            $cantidadLaboratorios = $base['otc'] ? 4 : 3;

            foreach ($base['formas'] as $formacion => $forma) {
                for ($posicionLaboratorio = 0; $posicionLaboratorio < $cantidadLaboratorios; $posicionLaboratorio++) {
                    $categoria = $categorias[$base['categoria']];
                    $laboratorio = $laboratorios[($indice + $formacion + $posicionLaboratorio) % $laboratorios->count()];
                    $nombre = trim($base['marca'].' '.Str::before($forma, ' x '));
                    $precio = round(
                        $catalogo['precios_base'][$base['categoria']]
                        + $formacion * 1.75
                        + (($indice + $posicionLaboratorio) % 6) * 0.35,
                        2
                    );

                    $medicamentosData[] = [
                        'categoria_id' => $categoria->id,
                        'laboratorio_id' => $laboratorio->id,
                        'nombre_comercial' => $nombre,
                        'principio_activo' => $base['principio_activo'],
                        'presentacion' => $forma,
                        'precio_venta' => $precio,
                        'stock_minimo' => 5 + (($indice * 3 + $formacion + $posicionLaboratorio) % 16),
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }
            }
        }

        if (count($medicamentosData) !== 1000) {
            throw new \RuntimeException('El catálogo debe generar exactamente 1000 medicamentos.');
        }

        foreach (array_chunk($medicamentosData, 500) as $bloque) {
            Medicamento::insert($bloque);
        }

        $medicamentos = Medicamento::orderBy('id')->get();

        $this->command->info('Creando 2000 lotes...');

        $lotesData = [];
        $numeroLote = 1;

        foreach ($medicamentos as $indice => $medicamento) {
            $cantidadLotes = $indice < 300 ? 1 : ($indice < 700 ? 2 : 3);

            for ($posicion = 0; $posicion < $cantidadLotes; $posicion++) {
                $tipo = 'futuro';

                if ($indice >= 300 && $indice < 500 && $posicion === 1) {
                    $tipo = 'vencido';
                } elseif ($indice >= 500 && $indice < 700 && $posicion === 1) {
                    $tipo = 'por_vencer';
                }

                $fecha = match ($tipo) {
                    'vencido' => $ahora->copy()->subDays(mt_rand(20, 240)),
                    'por_vencer' => $ahora->copy()->addDays(mt_rand(2, 28)),
                    default => $ahora->copy()->addMonths(
                        mt_rand($posicion === 0 ? 6 : 4, $posicion === 0 ? 24 : 20)
                    ),
                };

                $stock = match ($tipo) {
                    'vencido' => mt_rand(10, 60),
                    'por_vencer' => mt_rand(10, 40),
                    default => mt_rand($posicion === 0 ? 50 : 20, $posicion === 0 ? 160 : 100),
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

        if (count($lotesData) !== 2000) {
            throw new \RuntimeException('El catálogo debe generar exactamente 2000 lotes.');
        }

        foreach (array_chunk($lotesData, 500) as $bloque) {
            Lote::insert($bloque);
        }

        $lotes = Lote::with('medicamento:id,precio_venta')->get();

        $this->command->info('Creando 1000 ventas de demostración...');

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
        $ventasCreadas = 0;
        $totalVentas = 1000;
        $intentosVenta = 0;

        while ($ventasCreadas < $totalVentas) {
            if (++$intentosVenta > $totalVentas * 3) {
                throw new \RuntimeException('No se pudieron generar las 1000 ventas del escenario.');
            }

            $fechaVenta = $ventasCreadas < 10
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

                continue;
            }

            $ventasCreadas++;
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

        // Métodos de pago de demostración: ~60 % efectivo, ~20 % tarjeta, ~20 % QR.
        DB::statement("UPDATE ventas SET metodo_pago = CASE
            WHEN random() < 0.6 THEN 'efectivo'
            WHEN random() < 0.5 THEN 'tarjeta'
            ELSE 'qr' END");
        DB::statement("UPDATE ventas SET monto_recibido = CASE
            WHEN metodo_pago = 'efectivo' THEN CEIL(monto_total / 10.0) * 10
            ELSE monto_total END");
        DB::statement('UPDATE ventas SET cambio = monto_recibido - monto_total');

        $this->command->info('Ajustando escenarios de alertas...');

        $agotados = $medicamentos->slice(80, 30);
        $stockBajo = $medicamentos->slice(0, 80);

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
