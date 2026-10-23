<?php

namespace Tests\Feature;

use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaTest extends TestCase
{
    use RefreshDatabase;

    private function farmaceutico(): User
    {
        return User::factory()->create(['rol' => 'farmaceutico', 'activo' => true]);
    }

    public function test_venta_aplica_fefo_y_descuenta_del_lote_que_vence_primero(): void
    {
        $medicamento = Medicamento::factory()->create(['precio_venta' => 10.00]);

        $loteCercano = Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'FEFO-CERCA',
            'fecha_vencimiento' => now()->addDays(20)->toDateString(),
            'cantidad_stock' => 5,
        ]);

        $loteLejano = Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'FEFO-LEJOS',
            'fecha_vencimiento' => now()->addMonths(10)->toDateString(),
            'cantidad_stock' => 10,
        ]);

        $respuesta = $this->actingAs($this->farmaceutico())->post('/ventas', [
            'items' => [
                ['medicamento_id' => $medicamento->id, 'cantidad' => 7],
            ],
        ]);

        $venta = Venta::first();

        $this->assertNotNull($venta);
        $respuesta->assertRedirect(route('ventas.show', $venta));
        $this->assertEquals(0, $loteCercano->fresh()->cantidad_stock);
        $this->assertEquals(8, $loteLejano->fresh()->cantidad_stock);
        $this->assertEquals(70.0, (float) $venta->monto_total);
    }

    public function test_venta_exitosa_muestra_overlay_y_no_repite_al_recargar(): void
    {
        $medicamento = Medicamento::factory()->create(['precio_venta' => 12.50]);

        Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-OVERLAY-001',
            'fecha_vencimiento' => now()->addMonths(4)->toDateString(),
            'cantidad_stock' => 10,
        ]);

        $usuario = $this->farmaceutico();

        $respuesta = $this->actingAs($usuario)->post('/ventas', [
            'items' => [
                ['medicamento_id' => $medicamento->id, 'cantidad' => 2],
            ],
        ]);

        $venta = Venta::first();
        $respuesta->assertRedirect(route('ventas.show', $venta));
        $respuesta->assertSessionHas('animacion', 'venta');

        $destino = $this->actingAs($usuario)->get(route('ventas.show', $venta));

        $destino->assertOk()
            ->assertSee('data-animacion-exito', false)
            ->assertSee('¡Venta exitosa!', false)
            ->assertSee('Ver comprobante')
            ->assertSee('Nueva venta')
            ->assertSee(formato_bs($venta->monto_total));

        $recarga = $this->actingAs($usuario)->get(route('ventas.show', $venta));

        $recarga->assertOk()
            ->assertDontSee('data-animacion-exito', false)
            ->assertDontSee('¡Venta exitosa!', false);
    }

    public function test_venta_rechazada_cuando_el_unico_lote_esta_vencido(): void
    {
        $medicamento = Medicamento::factory()->create(['precio_venta' => 10.00]);

        $loteVencido = Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'FEFO-VENCIDO',
            'fecha_vencimiento' => now()->subDays(5)->toDateString(),
            'cantidad_stock' => 50,
        ]);

        $respuesta = $this->actingAs($this->farmaceutico())->from('/ventas/nueva')->post('/ventas', [
            'items' => [
                ['medicamento_id' => $medicamento->id, 'cantidad' => 1],
            ],
        ]);

        $respuesta->assertRedirect('/ventas/nueva');
        $respuesta->assertSessionHas('error');
        $this->assertDatabaseCount('ventas', 0);
        $this->assertEquals(50, $loteVencido->fresh()->cantidad_stock);
    }

    public function test_venta_rechazada_por_stock_insuficiente(): void
    {
        $medicamento = Medicamento::factory()->create(['precio_venta' => 10.00]);

        Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'FEFO-POCO',
            'fecha_vencimiento' => now()->addMonths(3)->toDateString(),
            'cantidad_stock' => 2,
        ]);

        $respuesta = $this->actingAs($this->farmaceutico())->from('/ventas/nueva')->post('/ventas', [
            'items' => [
                ['medicamento_id' => $medicamento->id, 'cantidad' => 10],
            ],
        ]);

        $respuesta->assertRedirect('/ventas/nueva');
        $respuesta->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'Stock insuficiente'));
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_venta_rechazada_muestra_mensaje_y_conserva_el_carrito(): void
    {
        $medicamento = Medicamento::factory()->create([
            'nombre_comercial' => 'Carrito Conservado',
            'precio_venta' => 10.00,
        ]);

        Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-CARRITO-001',
            'fecha_vencimiento' => now()->addMonths(3)->toDateString(),
            'cantidad_stock' => 2,
        ]);

        $respuesta = $this->actingAs($this->farmaceutico())
            ->followingRedirects()
            ->from('/ventas/nueva')
            ->post('/ventas', [
                'items' => [
                    ['medicamento_id' => $medicamento->id, 'cantidad' => 9],
                ],
            ]);

        $respuesta->assertOk()
            ->assertSee('Stock insuficiente')
            ->assertSee('Carrito Conservado')
            ->assertSee('data-items', false);
    }

    public function test_no_se_puede_registrar_una_venta_sin_items(): void
    {
        $respuesta = $this->actingAs($this->farmaceutico())->from('/ventas/nueva')->post('/ventas', [
            'items' => [],
        ]);

        $respuesta->assertRedirect('/ventas/nueva');
        $respuesta->assertSessionHasErrors('items');
        $this->assertDatabaseCount('ventas', 0);
    }
}
