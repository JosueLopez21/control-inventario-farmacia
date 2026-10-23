<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Medicamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelectorLoteTest extends TestCase
{
    use RefreshDatabase;

    private function farmaceutico(): User
    {
        return User::factory()->create(['rol' => 'farmaceutico', 'activo' => true]);
    }

    private function medicamento(array $atributos = []): Medicamento
    {
        return Medicamento::create(array_merge([
            'categoria_id' => Categoria::factory()->create()->id,
            'laboratorio_id' => Laboratorio::factory()->create(['nombre' => 'Laboratorio Andino'])->id,
            'nombre_comercial' => 'Jarabe Infantil',
            'principio_activo' => 'Ibuprofeno',
            'presentacion' => 'Jarabe 120 ml',
            'precio_venta' => 25.50,
            'stock_minimo' => 5,
        ], $atributos));
    }

    public function test_formulario_de_lote_incluye_buscador_y_preselecciona_el_medicamento(): void
    {
        $medicamento = $this->medicamento();

        $respuesta = $this->actingAs($this->farmaceutico())
            ->get(route('lotes.create', ['medicamento_id' => $medicamento->id]));

        $respuesta->assertOk()
            ->assertSee('data-selector-lote', false)
            ->assertSee('data-selector-url="'.route('medicamentos.buscar').'"', false)
            ->assertSee('role="combobox"', false)
            ->assertSee('value="'.$medicamento->id.'" selected', false)
            ->assertSee('Medicamento seleccionado')
            ->assertSee($medicamento->nombre_comercial)
            ->assertSee('Ibuprofeno')
            ->assertSee('Jarabe 120 ml')
            ->assertSee('Laboratorio Andino');
    }

    public function test_endpoint_encuentra_por_presentacion_y_laboratorio(): void
    {
        $medicamento = $this->medicamento();

        $porPresentacion = $this->actingAs($this->farmaceutico())
            ->getJson(route('medicamentos.buscar', ['q' => 'jarabe', 'incluir_agotados' => 1]));

        $porPresentacion->assertOk()
            ->assertJsonPath('resultados.0.id', $medicamento->id)
            ->assertJsonPath('resultados.0.presentacion', 'Jarabe 120 ml')
            ->assertJsonPath('resultados.0.vendible', false);

        $porLaboratorio = $this->actingAs($this->farmaceutico())
            ->getJson(route('medicamentos.buscar', ['q' => 'andino', 'incluir_agotados' => 1]));

        $porLaboratorio->assertOk()
            ->assertJsonPath('resultados.0.id', $medicamento->id)
            ->assertJsonPath('resultados.0.laboratorio', 'Laboratorio Andino');
    }

    public function test_endpoint_encuentra_presentacion_con_tildes_y_mayusculas(): void
    {
        $medicamento = $this->medicamento([
            'nombre_comercial' => 'Solución Pediátrica',
            'presentacion' => 'Solución oral 150 ml',
        ]);

        $respuesta = $this->actingAs($this->farmaceutico())
            ->getJson(route('medicamentos.buscar', ['q' => 'SOLUCION', 'incluir_agotados' => 1]));

        $respuesta->assertOk()
            ->assertJsonPath('resultados.0.id', $medicamento->id);
    }

    public function test_doctor_no_puede_acceder_al_formulario_de_lote(): void
    {
        $doctor = User::factory()->create(['rol' => 'doctor', 'activo' => true]);

        $this->actingAs($doctor)
            ->get(route('lotes.create'))
            ->assertForbidden();
    }
}
