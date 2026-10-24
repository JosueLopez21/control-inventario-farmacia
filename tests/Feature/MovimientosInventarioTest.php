<?php

namespace Tests\Feature;

use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use App\Models\User;
use App\Services\VentaService;
use Database\Seeders\MedicamentosBolivianosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovimientosInventarioTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol): User
    {
        return User::factory()->create(['rol' => $rol, 'activo' => true]);
    }

    private function lote(string $nombre, string $numero, int $stock, int $dias): Lote
    {
        $medicamento = Medicamento::factory()->create(['nombre_comercial' => $nombre, 'precio_venta' => 10]);

        return Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => $numero,
            'fecha_vencimiento' => now()->addDays($dias)->toDateString(),
            'cantidad_stock' => $stock,
        ]);
    }

    public function test_una_venta_registra_que_lote_bajo_y_cuanto(): void
    {
        $proximo = $this->lote('Amoxicilina Prueba', 'LOT-A', 5, 20);
        $lejano = Lote::create([
            'medicamento_id' => $proximo->medicamento_id,
            'numero_lote' => 'LOT-B',
            'fecha_vencimiento' => now()->addDays(200)->toDateString(),
            'cantidad_stock' => 30,
        ]);

        $venta = app(VentaService::class)->crearVenta([
            ['medicamento_id' => $proximo->medicamento_id, 'cantidad' => 8],
        ]);

        $movimientos = MovimientoInventario::where('venta_id', $venta->id)->orderBy('id')->get();

        $this->assertCount(2, $movimientos);
        $this->assertSame(['LOT-A', -5, 5, 0], [$movimientos[0]->numero_lote, $movimientos[0]->cantidad, $movimientos[0]->stock_antes, $movimientos[0]->stock_despues]);
        $this->assertSame(['LOT-B', -3, 30, 27], [$movimientos[1]->numero_lote, $movimientos[1]->cantidad, $movimientos[1]->stock_antes, $movimientos[1]->stock_despues]);
        $this->assertSame('venta', $movimientos[0]->tipo);
        $this->assertSame('Amoxicilina Prueba', $movimientos[0]->medicamento_nombre);
        $this->assertSame(27, $lejano->fresh()->cantidad_stock);
        $this->assertSame('LOT-A', $venta->resumenStock[0]['lotes'][0]['numero_lote']);
    }

    public function test_una_venta_rechazada_no_deja_movimientos(): void
    {
        $lote = $this->lote('Poco Stock', 'LOT-C', 2, 100);

        try {
            app(VentaService::class)->crearVenta([['medicamento_id' => $lote->medicamento_id, 'cantidad' => 9]]);
            $this->fail('Debió rechazar la venta.');
        } catch (\Exception) {
        }

        $this->assertSame(0, MovimientoInventario::count());
        $this->assertSame(2, $lote->fresh()->cantidad_stock);
    }

    public function test_registrar_y_editar_un_lote_deja_ingreso_y_ajuste(): void
    {
        $admin = $this->usuario('administrador');
        $medicamento = Medicamento::factory()->create();

        $this->actingAs($admin)->post(route('lotes.store'), [
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-NUEVO',
            'fecha_vencimiento' => now()->addDays(120)->toDateString(),
            'cantidad_stock' => 40,
        ])->assertRedirect(route('lotes.index'));

        $ingreso = MovimientoInventario::where('numero_lote', 'LOT-NUEVO')->first();
        $this->assertSame(['ingreso', 40, 0, 40, $admin->id], [$ingreso->tipo, $ingreso->cantidad, $ingreso->stock_antes, $ingreso->stock_despues, $ingreso->user_id]);

        $lote = Lote::where('numero_lote', 'LOT-NUEVO')->first();
        $this->actingAs($admin)->put(route('lotes.update', $lote), [
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-NUEVO',
            'fecha_vencimiento' => now()->addDays(120)->toDateString(),
            'cantidad_stock' => 35,
        ])->assertRedirect(route('lotes.index'));

        $ajuste = MovimientoInventario::where('tipo', 'ajuste')->first();
        $this->assertSame([-5, 40, 35], [$ajuste->cantidad, $ajuste->stock_antes, $ajuste->stock_despues]);
    }

    public function test_editar_un_lote_sin_cambiar_el_stock_no_crea_ajuste(): void
    {
        $lote = $this->lote('Sin Cambio', 'LOT-D', 10, 90);

        $this->actingAs($this->usuario('farmaceutico'))->put(route('lotes.update', $lote), [
            'medicamento_id' => $lote->medicamento_id,
            'numero_lote' => 'LOT-D',
            'fecha_vencimiento' => now()->addDays(95)->toDateString(),
            'cantidad_stock' => 10,
        ])->assertRedirect();

        $this->assertSame(0, MovimientoInventario::count());
    }

    public function test_el_modulo_busca_por_medicamento_sin_tildes_y_por_lote(): void
    {
        $lote = $this->lote('Ácido Fólico Prueba', 'LOT-ZZ9', 12, 100);
        $otro = $this->lote('Otro Producto', 'LOT-YY1', 12, 100);
        $this->actingAs($this->usuario('farmaceutico'));
        app(VentaService::class)->crearVenta([['medicamento_id' => $lote->medicamento_id, 'cantidad' => 3]]);
        app(VentaService::class)->crearVenta([['medicamento_id' => $otro->medicamento_id, 'cantidad' => 1]]);

        $this->get(route('movimientos.index', ['q' => 'acido folico']))
            ->assertOk()
            ->assertSee('Ácido Fólico Prueba')
            ->assertSee('LOT-ZZ9')
            ->assertDontSee('LOT-YY1');

        $this->get(route('movimientos.index', ['q' => 'yy1']))
            ->assertOk()
            ->assertSee('Otro Producto')
            ->assertDontSee('LOT-ZZ9');

        $this->get(route('movimientos.index', ['tipo' => 'venta']))->assertOk()->assertSee('Venta');
    }

    public function test_el_modulo_filtra_por_venta_y_la_venta_muestra_sus_lotes(): void
    {
        $lote = $this->lote('Filtro Venta', 'LOT-F1', 20, 100);
        $this->actingAs($this->usuario('administrador'));
        $venta = app(VentaService::class)->crearVenta([['medicamento_id' => $lote->medicamento_id, 'cantidad' => 4]]);
        app(VentaService::class)->crearVenta([['medicamento_id' => $lote->medicamento_id, 'cantidad' => 2]]);

        $this->get(route('movimientos.index', ['venta' => $venta->id]))
            ->assertOk()
            ->assertSee('Solo la venta #'.$venta->id)
            ->assertSee('−4')
            ->assertDontSee('−2');

        $this->get(route('ventas.show', $venta))
            ->assertOk()
            ->assertSee('Lotes que bajaron con esta venta')
            ->assertSee('LOT-F1');
    }

    public function test_fechas_invalidas_no_rompen_el_modulo(): void
    {
        $this->actingAs($this->usuario('administrador'))
            ->get(route('movimientos.index', ['desde' => 'no-es-fecha', 'hasta' => '2026-13-45', 'q' => '%_']))
            ->assertOk();
    }

    public function test_permisos_del_modulo_por_rol(): void
    {
        $this->get(route('movimientos.index'))->assertRedirect();
        $this->actingAs($this->usuario('doctor'))->get(route('movimientos.index'))->assertForbidden();
        $this->actingAs($this->usuario('farmaceutico'))->get(route('movimientos.index'))->assertOk()->assertSee('Movimientos de inventario');
        $this->actingAs($this->usuario('administrador'))->get(route('movimientos.index'))->assertOk();
    }

    public function test_la_venta_muestra_el_enlace_a_movimientos_en_la_animacion(): void
    {
        $lote = $this->lote('Con Animacion', 'LOT-G1', 10, 100);

        $this->actingAs($this->usuario('farmaceutico'))
            ->followingRedirects()
            ->post(route('ventas.store'), [
                'items' => [['medicamento_id' => $lote->medicamento_id, 'cantidad' => 2]],
                'metodo_pago' => 'efectivo',
            ])
            ->assertOk()
            ->assertSee('data-ver-movimientos', false)
            ->assertSee('Lote LOT-G1', false);
    }

    public function test_el_lote_de_medicamentos_bolivianos_es_idempotente_y_registra_ingresos(): void
    {
        $this->seed(MedicamentosBolivianosSeeder::class);
        $this->seed(MedicamentosBolivianosSeeder::class);

        $this->assertDatabaseHas('laboratorios', ['nombre' => 'Laboratorios INTI', 'pais' => 'Bolivia']);
        $this->assertSame(26, Medicamento::whereHas('laboratorio', fn ($q) => $q->where('pais', 'Bolivia'))->count());
        $this->assertSame(26, Lote::where('numero_lote', 'like', 'BOL-%')->count());
        $this->assertSame(26, MovimientoInventario::where('tipo', 'ingreso')->count());
        $this->assertSame(0, Lote::vencidos()->count());
        $this->assertDatabaseHas('categorias', ['nombre' => 'Antiparasitarios']);

        $this->actingAs($this->usuario('farmaceutico'))
            ->get(route('medicamentos.buscar', ['q' => 'albendazol']))
            ->assertOk()
            ->assertJsonPath('resultados.0.laboratorio', 'Laboratorios INTI');
    }
}
