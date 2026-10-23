<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoVisualTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol): User
    {
        return User::factory()->create(['rol' => $rol, 'activo' => true]);
    }

    private function medicamento(string $nombre = 'Panadol Feria'): Medicamento
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Analgésicos']);
        $laboratorio = Laboratorio::factory()->create(['nombre' => 'Genfar']);

        $medicamento = Medicamento::create([
            'categoria_id' => $categoria->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre_comercial' => $nombre,
            'principio_activo' => 'Paracetamol',
            'presentacion' => 'Tabletas 500 mg x 20',
            'precio_venta' => 12.50,
            'stock_minimo' => 10,
        ]);

        Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-FERIA-001',
            'fecha_vencimiento' => now()->addDays(20)->toDateString(),
            'cantidad_stock' => 30,
        ]);

        return $medicamento;
    }

    public function test_catalogo_muestra_tarjetas_y_datos_para_el_modal(): void
    {
        $medicamento = $this->medicamento();

        $respuesta = $this->actingAs($this->usuario('administrador'))->get('/medicamentos');

        $respuesta->assertOk()
            ->assertSee('med-card', false)
            ->assertSee($medicamento->nombre_comercial)
            ->assertSee('data-abrir-medicamento', false)
            ->assertSee('modalMedicamento', false)
            ->assertSee('Vence en', false);
    }

    public function test_catalogo_responde_json_con_el_html_filtrado(): void
    {
        $medicamento = $this->medicamento();

        $respuesta = $this->actingAs($this->usuario('doctor'))
            ->get('/medicamentos?q=Panadol', ['Accept' => 'application/json']);

        $respuesta->assertOk()->assertJsonStructure(['html', 'total']);
        $this->assertSame(1, $respuesta->json('total'));
        $this->assertStringContainsString($medicamento->nombre_comercial, $respuesta->json('html'));
    }

    public function test_doctor_ve_el_catalogo_sin_acciones_de_gestion(): void
    {
        $this->medicamento();

        $respuesta = $this->actingAs($this->usuario('doctor'))->get('/medicamentos');

        $respuesta->assertOk()
            ->assertDontSee('modalMedicamentoVender', false)
            ->assertDontSee('modalMedicamentoEditar', false)
            ->assertDontSee('modalMedicamentoEliminar', false);
    }

    public function test_farmaceutico_puede_vender_y_editar_desde_el_catalogo(): void
    {
        $medicamento = $this->medicamento();

        $respuesta = $this->actingAs($this->usuario('farmaceutico'))->get('/medicamentos');

        $respuesta->assertOk()
            ->assertSee('modalMedicamentoVender', false)
            ->assertSee('modalMedicamentoEditar', false)
            ->assertDontSee('modalMedicamentoEliminar', false)
            ->assertSee(route('ventas.create', ['medicamento_id' => $medicamento->id]), false);
    }
}
