<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuscadorPosTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol = 'farmaceutico'): User
    {
        return User::factory()->create(['rol' => $rol, 'activo' => true]);
    }

    private function medicamento(string $nombre, string $principio, int $stock, ?Categoria $categoria = null, int $dias = 60): Medicamento
    {
        $categoria ??= Categoria::factory()->create();
        $laboratorio = Laboratorio::factory()->create();

        $medicamento = Medicamento::create([
            'categoria_id' => $categoria->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre_comercial' => $nombre,
            'principio_activo' => $principio,
            'presentacion' => 'Cápsulas x 30',
            'precio_venta' => 25.50,
            'stock_minimo' => 5,
        ]);

        Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-'.strtoupper(str_replace(' ', '-', $nombre)),
            'fecha_vencimiento' => now()->addDays($dias)->toDateString(),
            'cantidad_stock' => $stock,
        ]);

        return $medicamento;
    }

    public function test_busqueda_ignora_mayusculas_y_tildes_y_encuentra_omeg(): void
    {
        $this->medicamento('Omega 3 Premium', 'Omega 3', 20);
        $this->medicamento('Ácido Fólico Forte', 'Ácido Fólico', 12);

        $this->actingAs($this->usuario())
            ->getJson(route('medicamentos.buscar', ['q' => 'OMEG']))
            ->assertOk()
            ->assertJsonPath('resultados.0.nombre_comercial', 'Omega 3 Premium');

        $this->actingAs($this->usuario())
            ->getJson(route('medicamentos.buscar', ['q' => 'acido folico']))
            ->assertOk()
            ->assertJsonPath('resultados.0.nombre_comercial', 'Ácido Fólico Forte');
    }

    public function test_endpoint_solo_devuelve_productos_vendibles_con_stock_vigente(): void
    {
        $this->medicamento('Omega 3 Con Stock', 'Omega 3', 8);
        $this->medicamento('Omega 3 Agotado', 'Omega 3', 0);
        $this->medicamento('Omega 3 Vencido', 'Omega 3', 15, null, -3);

        $respuesta = $this->actingAs($this->usuario())
            ->getJson(route('medicamentos.buscar', ['q' => 'omega']));

        $respuesta->assertOk()
            ->assertJsonCount(1, 'resultados')
            ->assertJsonPath('resultados.0.nombre_comercial', 'Omega 3 Con Stock')
            ->assertJsonPath('resultados.0.vendible', true);

        $this->assertGreaterThan(0, $respuesta->json('resultados.0.stock'));
    }

    public function test_ordena_primero_los_que_empiezan_con_el_texto(): void
    {
        $this->medicamento('Plus Ibuprofeno', 'Ibuprofeno', 10);
        $this->medicamento('Ibuprofeno Forte', 'Ibuprofeno', 10);

        $respuesta = $this->actingAs($this->usuario())
            ->getJson(route('medicamentos.buscar', ['q' => 'ibuprofeno']));

        $respuesta->assertOk()
            ->assertJsonPath('resultados.0.nombre_comercial', 'Ibuprofeno Forte')
            ->assertJsonPath('resultados.1.nombre_comercial', 'Plus Ibuprofeno');
    }

    public function test_filtra_por_categoria_y_respeta_el_limite(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Vitaminas POS']);
        $otraCategoria = Categoria::factory()->create(['nombre' => 'Otra POS']);

        $this->medicamento('Omega 3 Uno', 'Omega 3', 10, $categoria);
        $this->medicamento('Omega 3 Dos', 'Omega 3', 10, $categoria);
        $this->medicamento('Omega 3 Tres', 'Omega 3', 10, $categoria);
        $this->medicamento('Omega 3 Ajena', 'Omega 3', 10, $otraCategoria);

        $respuesta = $this->actingAs($this->usuario())
            ->getJson(route('medicamentos.buscar', [
                'categoria_id' => $categoria->id,
                'limit' => 2,
            ]));

        $respuesta->assertOk()
            ->assertJsonCount(2, 'resultados')
            ->assertJsonPath('total', 3)
            ->assertJsonPath('hay_mas', true);

        foreach ($respuesta->json('resultados') as $resultado) {
            $this->assertSame('Vitaminas POS', $resultado['categoria']);
        }
    }

    public function test_doctor_no_puede_usar_el_buscador_de_ventas(): void
    {
        $this->actingAs($this->usuario('doctor'))
            ->getJson(route('medicamentos.buscar', ['q' => 'omega']))
            ->assertForbidden();
    }
}
