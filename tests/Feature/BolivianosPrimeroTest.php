<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\User;
use Database\Seeders\MedicamentosBolivianosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BolivianosPrimeroTest extends TestCase
{
    use RefreshDatabase;

    private Medicamento $extranjero;

    private Medicamento $boliviano;

    protected function setUp(): void
    {
        parent::setUp();

        $categoria = Categoria::factory()->create(['nombre' => 'Analgésicos']);
        $afuera = Laboratorio::factory()->create(['nombre' => 'Lab Extranjero', 'pais' => 'Suiza']);
        $aqui = Laboratorio::factory()->create(['nombre' => 'Lab Boliviano', 'pais' => 'Bolivia']);

        // "Aaa" va antes alfabéticamente, pero el boliviano debe salir primero.
        $this->extranjero = Medicamento::factory()->create([
            'nombre_comercial' => 'Aaa Paracetamol Importado', 'categoria_id' => $categoria->id, 'laboratorio_id' => $afuera->id,
        ]);
        $this->boliviano = Medicamento::factory()->create([
            'nombre_comercial' => 'Zzz Paracetamol Boliviano', 'categoria_id' => $categoria->id, 'laboratorio_id' => $aqui->id,
        ]);

        foreach ([$this->extranjero, $this->boliviano] as $i => $medicamento) {
            Lote::create([
                'medicamento_id' => $medicamento->id,
                'numero_lote' => 'LOT-ORD-'.$i,
                'fecha_vencimiento' => now()->addDays($i === 0 ? 100 : 200)->toDateString(),
                'cantidad_stock' => 20,
            ]);
        }

        $this->actingAs(User::factory()->create(['rol' => 'farmaceutico', 'activo' => true]));
    }

    public function test_el_catalogo_muestra_primero_los_bolivianos_tambien_filtrado_por_categoria(): void
    {
        $this->get(route('medicamentos.index'))
            ->assertOk()
            ->assertSeeInOrder(['Zzz Paracetamol Boliviano', 'Aaa Paracetamol Importado']);

        $this->get(route('medicamentos.index', ['categoria_id' => $this->boliviano->categoria_id]))
            ->assertSeeInOrder(['Zzz Paracetamol Boliviano', 'Aaa Paracetamol Importado']);
    }

    public function test_el_buscador_de_ventas_devuelve_primero_los_bolivianos(): void
    {
        $respuesta = $this->getJson(route('medicamentos.buscar', ['q' => 'paracetamol']))->assertOk();
        $this->assertSame($this->boliviano->id, $respuesta->json('resultados.0.id'));

        $sinTexto = $this->getJson(route('medicamentos.buscar'))->assertOk();
        $this->assertSame($this->boliviano->id, $sinTexto->json('resultados.0.id'));
    }

    public function test_lotes_alertas_y_selectores_muestran_primero_los_bolivianos(): void
    {
        $this->get(route('lotes.index'))->assertSeeInOrder(['LOT-ORD-1', 'LOT-ORD-0']);
        $this->get(route('lotes.create'))->assertOk();
        $this->get(route('ventas.create', ['modo' => 'clasico']))
            ->assertSeeInOrder(['Zzz Paracetamol Boliviano', 'Aaa Paracetamol Importado']);
    }

    public function test_el_catalogo_boliviano_ampliado_cubre_todas_las_categorias(): void
    {
        $this->seed(MedicamentosBolivianosSeeder::class);

        $bolivianos = Medicamento::whereHas('laboratorio', fn ($q) => $q->where('pais', 'Bolivia'));
        $this->assertGreaterThanOrEqual(120, (clone $bolivianos)->count());
        $this->assertSame(11, (clone $bolivianos)->distinct()->count('categoria_id'));
        $this->assertSame(6, Laboratorio::where('pais', 'Bolivia')->count());
        $this->assertSame(0, Lote::vencidos()->where('numero_lote', 'like', 'BOL-%')->count());
    }
}
