<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertasVisualTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol): User
    {
        return User::factory()->create(['rol' => $rol, 'activo' => true]);
    }

    private function escenario(): array
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

        $porVencer = Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-CERCA',
            'fecha_vencimiento' => now()->addDays(12)->toDateString(),
            'cantidad_stock' => 3,
        ]);

        $vencido = Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-VENCIDO',
            'fecha_vencimiento' => now()->subDays(5)->toDateString(),
            'cantidad_stock' => 4,
        ]);

        return [$medicamento, $porVencer, $vencido];
    }

    public function test_alertas_muestra_semaforo_resumen_y_acciones_de_inventario(): void
    {
        [$medicamento, $porVencer] = $this->escenario();

        $respuesta = $this->actingAs($this->usuario('farmaceutico'))->get('/alertas');

        $respuesta->assertOk()
            ->assertSee('Semáforo de inventario')
            ->assertSee('alerta-resumen--rojo', false)
            ->assertSee('data-alerta-resumen', false)
            ->assertSee($porVencer->numero_lote)
            ->assertSee(route('lotes.create', ['medicamento_id' => $medicamento->id]), false)
            ->assertSee(route('lotes.edit', $porVencer), false);
    }

    public function test_doctor_no_ve_acciones_de_gestion_en_alertas(): void
    {
        [$medicamento, $porVencer] = $this->escenario();

        $respuesta = $this->actingAs($this->usuario('doctor'))->get('/alertas');

        $respuesta->assertOk()
            ->assertDontSee(route('lotes.create', ['medicamento_id' => $medicamento->id]), false)
            ->assertDontSee(route('lotes.edit', $porVencer), false)
            ->assertDontSee('Registrar lote');
    }

    public function test_registrar_lote_desde_alerta_preselecciona_el_medicamento(): void
    {
        [$medicamento] = $this->escenario();

        $respuesta = $this->actingAs($this->usuario('farmaceutico'))
            ->get(route('lotes.create', ['medicamento_id' => $medicamento->id]));

        $respuesta->assertOk()
            ->assertSee('value="' . $medicamento->id . '" selected', false);
    }
}
