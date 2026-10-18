<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReglasNegocioFarmaciaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Regla 1: Alertar sobre lotes que vencen en los próximos 30 días.
     */
    public function test_scope_por_vencer_identifica_lotes_dentro_de_30_dias(): void
    {
        $categoria = Categoria::factory()->create();
        $lab = Laboratorio::factory()->create();
        $medicamento = Medicamento::factory()->create([
            'categoria_id' => $categoria->id,
            'laboratorio_id' => $lab->id,
        ]);

        // Lote que vence en 15 días (debe alertar)
        Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'TEST-PROXIMO',
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
            'cantidad_stock' => 20,
        ]);

        // Lote que vence en 60 días (no debe alertar)
        Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'TEST-LEJANO',
            'fecha_vencimiento' => now()->addDays(60)->toDateString(),
            'cantidad_stock' => 20,
        ]);

        $porVencer = Lote::porVencer(30)->pluck('numero_lote');

        $this->assertTrue($porVencer->contains('TEST-PROXIMO'));
        $this->assertFalse($porVencer->contains('TEST-LEJANO'));
    }

    /**
     * Regla 2: Alertar sobre medicamentos con stock por debajo del mínimo.
     */
    public function test_alerta_medicamentos_por_debajo_del_stock_minimo(): void
    {
        $categoria = Categoria::factory()->create();
        $lab = Laboratorio::factory()->create();

        $medBajo = Medicamento::create([
            'categoria_id' => $categoria->id,
            'laboratorio_id' => $lab->id,
            'nombre_comercial' => 'Medicamento Bajo Stock',
            'principio_activo' => 'Principio',
            'presentacion' => 'Caja',
            'precio_venta' => 10.00,
            'stock_minimo' => 20,
        ]);

        Lote::create([
            'medicamento_id' => $medBajo->id,
            'numero_lote' => 'TEST-BAJO',
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'cantidad_stock' => 5,
        ]);

        $medicamentoEncontrado = Medicamento::withSum(['lotes as stock_vigente' => fn ($q) => $q->where('fecha_vencimiento', '>=', now()->toDateString())], 'cantidad_stock')
            ->find($medBajo->id);

        $this->assertTrue($medicamentoEncontrado->stock_vigente < $medicamentoEncontrado->stock_minimo);
    }

    /**
     * Regla 3: Bloquear estrictamente la venta de lotes vencidos y respetar FIFO.
     */
    public function test_venta_fifo_descuenta_del_lote_mas_proximo_y_bloquea_vencidos(): void
    {
        $categoria = Categoria::factory()->create();
        $lab = Laboratorio::factory()->create();
        $medicamento = Medicamento::create([
            'categoria_id' => $categoria->id,
            'laboratorio_id' => $lab->id,
            'nombre_comercial' => 'Medicamento FIFO',
            'principio_activo' => 'Principio',
            'presentacion' => 'Caja',
            'precio_venta' => 15.00,
            'stock_minimo' => 5,
        ]);

        // Lote vencido (no debe tocarse)
        $loteVencido = Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOTE-VENCIDO',
            'fecha_vencimiento' => now()->subDays(10)->toDateString(),
            'cantidad_stock' => 10,
        ]);

        // Lote 1 vigente: vence en 2 meses (sale primero por FIFO)
        $lote1 = Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOTE-FIFO-1',
            'fecha_vencimiento' => now()->addMonths(2)->toDateString(),
            'cantidad_stock' => 5,
        ]);

        // Lote 2 vigente: vence en 6 meses (sale después por FIFO)
        $lote2 = Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOTE-FIFO-2',
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'cantidad_stock' => 10,
        ]);

        $servicio = new VentaService();

        // Vendemos 7 unidades: 5 de lote1 y 2 de lote2
        $venta = $servicio->crearVenta([
            ['medicamento_id' => $medicamento->id, 'cantidad' => 7],
        ]);

        $this->assertEquals(7 * 15.00, $venta->monto_total);
        $this->assertEquals(0, $lote1->fresh()->cantidad_stock);
        $this->assertEquals(8, $lote2->fresh()->cantidad_stock);
        $this->assertEquals(10, $loteVencido->fresh()->cantidad_stock);
    }
}
