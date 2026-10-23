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
            $this->assertDatabaseCount('medicamentos', 300);
            $this->assertDatabaseCount('lotes', 750);
            $this->assertDatabaseCount('ventas', 500);
            $this->assertSame(60, Medicamento::query()->distinct()->count('principio_activo'));

            $medicamentos = Medicamento::query()->withCount('lotes')->get();

            $this->assertSame([2, 3], $medicamentos->pluck('lotes_count')->unique()->sort()->values()->all());
            $this->assertFalse($medicamentos->contains(
                fn (Medicamento $medicamento) => preg_match('/-\d{3}$/', $medicamento->nombre_comercial) === 1
            ));

            $this->assertSame(75, Lote::vencidos()->count());
            $this->assertSame(75, Lote::porVencer(30)->count());
            $this->assertSame(15, Medicamento::agotados()->count());
            $this->assertSame(10, Venta::whereDate('fecha_venta', now()->toDateString())->count());

            $conStockBajo = Medicamento::query()
                ->withSum(['lotes as stock_vigente' => fn ($query) => $query->where('fecha_vencimiento', '>=', now()->toDateString())], 'cantidad_stock')
                ->get()
                ->filter(fn (Medicamento $medicamento) => (int) ($medicamento->stock_vigente ?? 0) > 0
                    && (int) ($medicamento->stock_vigente ?? 0) < $medicamento->stock_minimo);

            $this->assertCount(24, $conStockBajo);
            $this->assertSame(39, app(ReporteService::class)->stockBajo()->count());
            $this->assertSame(0, Lote::query()->where('cantidad_stock', '<', 0)->count());
        } finally {
            Carbon::setTestNow();
        }
    }
}
