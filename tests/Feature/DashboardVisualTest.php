<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardVisualTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol): User
    {
        return User::factory()->create(['rol' => $rol, 'activo' => true]);
    }

    private function medicamentoConLote(int $stock = 20, int $dias = 25): Medicamento
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Analgésicos']);
        $laboratorio = Laboratorio::factory()->create(['nombre' => 'Genfar']);

        $medicamento = Medicamento::create([
            'categoria_id' => $categoria->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre_comercial' => 'Panadol Feria',
            'principio_activo' => 'Paracetamol',
            'presentacion' => 'Tabletas 500 mg x 20',
            'precio_venta' => 12.50,
            'stock_minimo' => 10,
        ]);

        Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-DASH-001',
            'fecha_vencimiento' => now()->addDays($dias)->toDateString(),
            'cantidad_stock' => $stock,
        ]);

        return $medicamento;
    }

    public function test_dashboard_muestra_hero_contadores_y_simulador_para_farmacia(): void
    {
        $this->medicamentoConLote();

        $respuesta = $this->actingAs($this->usuario('farmaceutico'))->get('/');

        $respuesta->assertOk()
            ->assertSee('Simular venta')
            ->assertSee('data-contador="ventasHoy"', false)
            ->assertSee('Presentación')
            ->assertSee('hero-visual', false)
            ->assertSee('graficoVentas', false);
    }

    public function test_doctor_ve_dashboard_de_consulta_sin_simulador(): void
    {
        $this->medicamentoConLote();

        $respuesta = $this->actingAs($this->usuario('doctor'))->get('/');

        $respuesta->assertOk()
            ->assertSee('Vista de consulta médica')
            ->assertDontSee('btnSimularVenta', false)
            ->assertDontSee('graficoVentas', false);
    }

    public function test_simulador_crea_venta_fefo_y_actualiza_metricas(): void
    {
        $medicamento = $this->medicamentoConLote(20);

        $respuesta = $this->actingAs($this->usuario('administrador'))
            ->postJson(route('simulador.venta'));

        $respuesta->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure([
                'message',
                'venta' => ['id', 'monto_total', 'detalles'],
                'metricas' => ['ventasHoy', 'ingresosHoy', 'stockBajo'],
                'grafico' => ['etiquetas', 'totales', 'montos'],
            ]);

        $this->assertSame(1, Venta::count());
        $this->assertSame(1, $respuesta->json('metricas.ventasHoy'));
        $this->assertGreaterThan(0, $respuesta->json('metricas.ingresosHoy'));
        $this->assertLessThan(20, $medicamento->lotes()->first()->cantidad_stock);
    }

    public function test_simulador_solo_esta_disponible_para_farmacia(): void
    {
        $this->medicamentoConLote();

        $this->actingAs($this->usuario('doctor'))
            ->postJson(route('simulador.venta'))
            ->assertForbidden();
    }

    public function test_simulador_responde_422_si_no_hay_stock_vigente(): void
    {
        $this->medicamentoConLote(10, -2);

        $this->actingAs($this->usuario('farmaceutico'))
            ->postJson(route('simulador.venta'))
            ->assertStatus(422)
            ->assertJsonPath('ok', false);
    }
}
