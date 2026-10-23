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

class PosVisualTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol = 'farmaceutico'): User
    {
        return User::factory()->create(['rol' => $rol, 'activo' => true]);
    }

    private function medicamento(): Medicamento
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
            'stock_minimo' => 5,
        ]);

        Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-POS-001',
            'fecha_vencimiento' => now()->addDays(20)->toDateString(),
            'cantidad_stock' => 15,
        ]);

        return $medicamento;
    }

    public function test_pos_muestra_interfaz_tactil_y_enlace_clasico(): void
    {
        $medicamento = $this->medicamento();

        $respuesta = $this->actingAs($this->usuario())
            ->get(route('ventas.create', ['medicamento_id' => $medicamento->id]));

        $respuesta->assertOk()
            ->assertSee('posApp', false)
            ->assertSee('Formulario clásico')
            ->assertSee($medicamento->nombre_comercial)
            ->assertSee('data-items', false)
            ->assertSee('sugerencias', false)
            ->assertSee('data-categoria', false)
            ->assertSee('posGrid', false);
    }

    public function test_buscador_pos_incluye_metadatos_de_categoria(): void
    {
        $this->medicamento();

        $respuesta = $this->actingAs($this->usuario())
            ->getJson(route('medicamentos.buscar', ['q' => 'Panadol']));

        $respuesta->assertOk()
            ->assertJsonPath('resultados.0.nombre_comercial', 'Panadol Feria')
            ->assertJsonPath('resultados.0.categoria', 'Analgésicos')
            ->assertJsonStructure([
                'resultados' => [
                    ['id', 'stock', 'categoria_icono', 'categoria_color', 'categoria_suave'],
                ],
            ]);
    }

    public function test_formulario_clasico_registra_venta_ignorando_filas_vacias(): void
    {
        $medicamento = $this->medicamento();

        $respuesta = $this->actingAs($this->usuario())->post('/ventas', [
            'modo' => 'clasico',
            'items' => [
                ['medicamento_id' => '', 'cantidad' => 1],
                ['medicamento_id' => $medicamento->id, 'cantidad' => 2],
                ['medicamento_id' => '', 'cantidad' => 1],
            ],
        ]);

        $venta = Venta::first();

        $this->assertNotNull($venta);
        $respuesta->assertRedirect(route('ventas.show', $venta));
        $this->assertSame(25.0, (float) $venta->monto_total);
    }

    public function test_error_de_venta_clasica_vuelve_al_formulario_clasico(): void
    {
        $medicamento = $this->medicamento();

        $respuesta = $this->actingAs($this->usuario())->post('/ventas', [
            'modo' => 'clasico',
            'items' => [
                ['medicamento_id' => $medicamento->id, 'cantidad' => 99],
            ],
        ]);

        $respuesta->assertRedirect(route('ventas.create', ['modo' => 'clasico']));
        $respuesta->assertSessionHas('error');
        $this->assertDatabaseCount('ventas', 0);
    }
}
