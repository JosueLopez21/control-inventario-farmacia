<?php

namespace Tests\Feature;

use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\Venta;
use App\Services\ReporteService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CatalogoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_seeder_genera_el_catalogo_de_feria(): void
    {
        Carbon::setTestNow('2026-10-23 12:00:00');

        try {
            $this->seed(DatabaseSeeder::class);

            $this->assertDatabaseCount('categorias', 10);
            $this->assertDatabaseCount('laboratorios', 12);
            $this->assertDatabaseCount('medicamentos', 1000);
            $this->assertDatabaseCount('lotes', 2000);
            $this->assertDatabaseCount('ventas', 1000);
            $this->assertSame(60, Medicamento::query()->distinct()->count('principio_activo'));

            $medicamentos = Medicamento::query()->withCount('lotes')->get();

            $this->assertSame([1, 2, 3], $medicamentos->pluck('lotes_count')->unique()->sort()->values()->all());
            $this->assertFalse($medicamentos->contains(
                fn (Medicamento $medicamento) => preg_match('/\s-\s?\d+$/', $medicamento->nombre_comercial) === 1
            ));

            $duplicados = Medicamento::query()
                ->selectRaw('nombre_comercial, presentacion, laboratorio_id, count(*) as total')
                ->groupBy('nombre_comercial', 'presentacion', 'laboratorio_id')
                ->havingRaw('count(*) > 1')
                ->count();

            $this->assertSame(0, $duplicados);

            $this->assertSame(200, Lote::vencidos()->count());
            $this->assertSame(200, Lote::porVencer(30)->count());
            $this->assertSame(30, Medicamento::agotados()->count());
            $this->assertSame(10, Venta::whereDate('fecha_venta', now()->toDateString())->count());

            $conStockBajo = Medicamento::query()
                ->withSum(['lotes as stock_vigente' => fn ($query) => $query->where('fecha_vencimiento', '>=', now()->toDateString())], 'cantidad_stock')
                ->get()
                ->filter(fn (Medicamento $medicamento) => (int) ($medicamento->stock_vigente ?? 0) > 0
                    && (int) ($medicamento->stock_vigente ?? 0) < $medicamento->stock_minimo);

            $this->assertCount(80, $conStockBajo);
            $this->assertSame(110, app(ReporteService::class)->stockBajo()->count());
            $this->assertSame(0, Lote::query()->where('cantidad_stock', '<', 0)->count());

            $this->assertGreaterThanOrEqual(
                now()->subDays(180)->startOfDay(),
                Carbon::parse(Venta::min('fecha_venta'))
            );
            $this->assertLessThanOrEqual(now(), Carbon::parse(Venta::max('fecha_venta')));

            foreach ([
                'Paracetamol',
                'Ibuprofeno',
                'Naproxeno sódico',
                'Diclofenaco sódico',
                'Loratadina',
                'Cetirizina',
                'Omeprazol',
                'Hidróxido de aluminio y magnesio',
                'Loperamida',
                'Sales de rehidratación oral',
                'Ambroxol',
                'Dextrometorfano',
                'Ácido ascórbico',
                'Complejo vitamínico B',
                'Butilbromuro de hioscina',
                'Clotrimazol',
                'Hidrocortisona',
            ] as $principio) {
                $this->assertGreaterThanOrEqual(
                    15,
                    Medicamento::where('principio_activo', $principio)->count(),
                    "Faltan presentaciones de {$principio}."
                );
            }
        } finally {
            Carbon::setTestNow();
        }
    }
}
