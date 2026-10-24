<?php

namespace Tests\Feature;

use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\User;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaPagoTest extends TestCase
{
    use RefreshDatabase;

    private function farmaceutico(): User
    {
        return User::factory()->create(['rol' => 'farmaceutico', 'activo' => true]);
    }

    /**
     * Medicamento de Bs 10 con un lote vigente de $stock unidades.
     */
    private function medicamentoConLote(int $stock = 20, int $diasParaVencer = 200, float $precio = 10.00, int $minimo = 10): array
    {
        $medicamento = Medicamento::factory()->create([
            'precio_venta' => $precio,
            'stock_minimo' => $minimo,
        ]);

        $lote = Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-'.strtoupper(uniqid()),
            'fecha_vencimiento' => now()->addDays($diasParaVencer)->toDateString(),
            'cantidad_stock' => $stock,
        ]);

        return [$medicamento, $lote];
    }

    private function vender(Medicamento $medicamento, int $cantidad, array $pago = [])
    {
        return $this->actingAs($this->farmaceutico())->post('/ventas', array_merge([
            'items' => [['medicamento_id' => $medicamento->id, 'cantidad' => $cantidad]],
        ], $pago));
    }

    /* ---------------- Stock visible tras la venta (Fase 1) ---------------- */

    public function test_vender_de_un_lote_por_vencer_descuenta_las_unidades_y_el_contador_de_unidades_baja(): void
    {
        [$medicamento, $lote] = $this->medicamentoConLote(stock: 8, diasParaVencer: 10);
        $admin = $this->farmaceutico();

        $antes = $this->actingAs($admin)->get('/alertas');
        $this->assertSame(1, $antes->viewData('totalPorVencer'));
        $this->assertSame(8, $antes->viewData('unidadesPorVencer'));

        $this->actingAs($admin)->post('/ventas', [
            'items' => [['medicamento_id' => $medicamento->id, 'cantidad' => 3]],
        ]);

        $this->assertSame(5, $lote->fresh()->cantidad_stock);

        $despues = $this->actingAs($admin)->get('/alertas');
        $this->assertSame(1, $despues->viewData('totalPorVencer'), 'El lote sigue por vencer: el contador cuenta lotes.');
        $this->assertSame(5, $despues->viewData('unidadesPorVencer'), 'Las unidades sí deben bajar.');
    }

    public function test_al_agotar_un_lote_por_vencer_deja_de_contarse_en_la_alerta(): void
    {
        [$medicamento] = $this->medicamentoConLote(stock: 4, diasParaVencer: 5);
        $admin = $this->farmaceutico();

        $this->actingAs($admin)->post('/ventas', [
            'items' => [['medicamento_id' => $medicamento->id, 'cantidad' => 4]],
        ]);

        $alertas = $this->actingAs($admin)->get('/alertas');
        $this->assertSame(0, $alertas->viewData('totalPorVencer'));
        $this->assertSame(0, $alertas->viewData('unidadesPorVencer'));
    }

    public function test_vender_un_medicamento_con_stock_bajo_reduce_su_stock_vigente_en_alertas(): void
    {
        [$medicamento] = $this->medicamentoConLote(stock: 6, minimo: 10);
        $admin = $this->farmaceutico();

        $antes = $this->actingAs($admin)->get('/alertas')->viewData('stockBajo');
        $this->assertSame(6, (int) $antes->first()->stock_vigente);

        $this->actingAs($admin)->post('/ventas', [
            'items' => [['medicamento_id' => $medicamento->id, 'cantidad' => 2]],
        ]);

        $despues = $this->actingAs($admin)->get('/alertas');
        $this->assertSame(4, (int) $despues->viewData('stockBajo')->first()->stock_vigente);
        $this->assertSame(1, $despues->viewData('totalStockBajo'), 'Sigue bajo el mínimo: el contador cuenta medicamentos.');
        $this->assertSame(6, $despues->viewData('deficitStockBajo'), 'Faltan 10 - 4 = 6 unidades para el mínimo.');
    }

    public function test_el_resumen_de_la_venta_muestra_stock_antes_y_despues(): void
    {
        [$medicamento] = $this->medicamentoConLote(stock: 8);

        $respuesta = $this->followingRedirects()->vender($medicamento, 3);

        $respuesta->assertOk();
        $respuesta->assertSee('Stock antes');
        $respuesta->assertSee('Stock después');
        $respuesta->assertSee('data-resumen-stock', false);
    }

    public function test_el_dashboard_muestra_unidades_junto_a_los_contadores_de_lotes(): void
    {
        $this->medicamentoConLote(stock: 8, diasParaVencer: 10);

        $this->actingAs($this->farmaceutico())->get('/')
            ->assertOk()
            ->assertSee('unidades');
    }

    /* ---------------- Métodos de pago y cambio (Fase 3) ---------------- */

    public function test_efectivo_con_billete_mayor_calcula_el_cambio(): void
    {
        [$medicamento] = $this->medicamentoConLote(precio: 10.00);

        $this->vender($medicamento, 2, ['metodo_pago' => 'efectivo', 'monto_recibido' => 50]);

        $venta = Venta::firstOrFail();
        $this->assertSame('efectivo', $venta->metodo_pago);
        $this->assertEquals(20.00, (float) $venta->monto_total);
        $this->assertEquals(50.00, (float) $venta->monto_recibido);
        $this->assertEquals(30.00, (float) $venta->cambio, 'Si dan 50 y cuesta 20, el cambio es 30.');
    }

    public function test_efectivo_exacto_no_genera_cambio(): void
    {
        [$medicamento] = $this->medicamentoConLote(precio: 10.00);

        $this->vender($medicamento, 2, ['metodo_pago' => 'efectivo', 'monto_recibido' => 20]);

        $venta = Venta::firstOrFail();
        $this->assertEquals(20.00, (float) $venta->monto_recibido);
        $this->assertEquals(0.00, (float) $venta->cambio);
    }

    public function test_efectivo_insuficiente_se_rechaza_y_no_registra_nada(): void
    {
        [$medicamento, $lote] = $this->medicamentoConLote(stock: 20, precio: 10.00);

        $respuesta = $this->vender($medicamento, 2, ['metodo_pago' => 'efectivo', 'monto_recibido' => 15]);

        $respuesta->assertRedirect(route('ventas.create'));
        $respuesta->assertSessionHas('error');
        $this->assertStringContainsString('menor al total', session('error'));
        $this->assertSame(0, Venta::count());
        $this->assertSame(20, $lote->fresh()->cantidad_stock, 'El stock no debe cambiar si el pago es insuficiente.');
    }

    public function test_tarjeta_cobra_el_total_exacto_y_no_da_cambio(): void
    {
        [$medicamento] = $this->medicamentoConLote(precio: 10.00);

        $this->vender($medicamento, 3, ['metodo_pago' => 'tarjeta', 'monto_recibido' => 100]);

        $venta = Venta::firstOrFail();
        $this->assertSame('tarjeta', $venta->metodo_pago);
        $this->assertEquals(30.00, (float) $venta->monto_recibido, 'El servidor ignora el monto del cliente en tarjeta.');
        $this->assertEquals(0.00, (float) $venta->cambio);
    }

    public function test_qr_cobra_el_total_exacto_y_no_da_cambio(): void
    {
        [$medicamento] = $this->medicamentoConLote(precio: 12.50);

        $this->vender($medicamento, 2, ['metodo_pago' => 'qr']);

        $venta = Venta::firstOrFail();
        $this->assertSame('qr', $venta->metodo_pago);
        $this->assertEquals(25.00, (float) $venta->monto_recibido);
        $this->assertEquals(0.00, (float) $venta->cambio);
    }

    public function test_un_metodo_de_pago_invalido_se_rechaza(): void
    {
        [$medicamento] = $this->medicamentoConLote();

        $respuesta = $this->vender($medicamento, 1, ['metodo_pago' => 'bitcoin']);

        $respuesta->assertSessionHasErrors('metodo_pago');
        $this->assertSame(0, Venta::count());
    }

    public function test_sin_datos_de_pago_se_asume_efectivo_exacto_por_compatibilidad(): void
    {
        [$medicamento] = $this->medicamentoConLote(precio: 10.00);

        $this->vender($medicamento, 2);

        $venta = Venta::firstOrFail();
        $this->assertSame('efectivo', $venta->metodo_pago);
        $this->assertEquals(20.00, (float) $venta->monto_recibido);
        $this->assertEquals(0.00, (float) $venta->cambio);
    }

    public function test_el_servicio_crear_venta_sin_segundo_parametro_sigue_funcionando(): void
    {
        [$medicamento] = $this->medicamentoConLote(precio: 10.00);

        $venta = app(VentaService::class)->crearVenta([
            ['medicamento_id' => $medicamento->id, 'cantidad' => 1],
        ]);

        $this->assertSame('efectivo', $venta->metodo_pago);
        $this->assertEquals(10.00, (float) $venta->monto_recibido);
        $this->assertEquals(0.00, (float) $venta->cambio);
    }

    public function test_la_pantalla_de_exito_muestra_el_cambio_a_devolver_en_efectivo(): void
    {
        [$medicamento] = $this->medicamentoConLote(precio: 10.00);

        $respuesta = $this->followingRedirects()->vender($medicamento, 2, ['metodo_pago' => 'efectivo', 'monto_recibido' => 50]);

        $respuesta->assertOk();
        $respuesta->assertSee('data-cambio-venta', false);
        $respuesta->assertSee('Cambio a devolver');
        $respuesta->assertSee('30,00');
    }

    public function test_el_listado_y_el_comprobante_muestran_el_metodo_de_pago(): void
    {
        [$medicamento] = $this->medicamentoConLote(precio: 10.00);
        $this->vender($medicamento, 1, ['metodo_pago' => 'tarjeta']);

        $venta = Venta::firstOrFail();
        $usuario = $this->farmaceutico();

        $this->actingAs($usuario)->get('/ventas')->assertOk()->assertSee('Tarjeta');
        $this->actingAs($usuario)->get(route('ventas.print', $venta))->assertOk()->assertSee('Tarjeta');
    }

    public function test_el_reporte_agrupa_las_ventas_por_metodo_de_pago(): void
    {
        [$medicamento] = $this->medicamentoConLote(stock: 50, precio: 10.00);
        $this->vender($medicamento, 1, ['metodo_pago' => 'efectivo', 'monto_recibido' => 10]);
        $this->vender($medicamento, 2, ['metodo_pago' => 'qr']);

        $respuesta = $this->actingAs($this->farmaceutico())->get('/reportes');

        $respuesta->assertOk()->assertSee('Ventas por método de pago');
        $porMetodo = $respuesta->viewData('ventasPorMetodoPago')->keyBy('metodo');
        $this->assertSame(1, $porMetodo['efectivo']->ventas);
        $this->assertSame(1, $porMetodo['qr']->ventas);
        $this->assertSame(0, $porMetodo['tarjeta']->ventas);
        $this->assertEquals(20.0, $porMetodo['qr']->ingresos);
    }

    public function test_el_punto_de_venta_incluye_selector_de_cantidad_y_pago(): void
    {
        $this->actingAs($this->farmaceutico())->get('/ventas/nueva')
            ->assertOk()
            ->assertSee('id="modalCantidad"', false)
            ->assertSee('id="montoRecibido"', false)
            ->assertSee('name="metodo_pago"', false)
            ->assertSee('Método de pago');
    }

    public function test_el_doctor_no_puede_registrar_ventas_con_pago(): void
    {
        [$medicamento] = $this->medicamentoConLote();
        $doctor = User::factory()->create(['rol' => 'doctor', 'activo' => true]);

        $this->actingAs($doctor)->post('/ventas', [
            'items' => [['medicamento_id' => $medicamento->id, 'cantidad' => 1]],
            'metodo_pago' => 'efectivo',
            'monto_recibido' => 10,
        ])->assertForbidden();

        $this->assertSame(0, Venta::count());
    }
}
