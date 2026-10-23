<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsesorTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol = 'doctor'): User
    {
        return User::factory()->create(['rol' => $rol, 'activo' => true]);
    }

    /**
     * @param  array<int, array{0: string, 1: int, 2: int}>  $lotes
     */
    private function crearMedicamento(
        string $nombre,
        string $principio,
        array $lotes,
        string $categoria = 'Analgésicos',
        string $laboratorio = 'Genfar'
    ): Medicamento {
        $categoriaModel = Categoria::firstOrCreate(
            ['nombre' => $categoria],
            ['descripcion' => 'Categoría de prueba para el asesor.']
        );

        $laboratorioModel = Laboratorio::firstOrCreate(
            ['nombre' => $laboratorio],
            [
                'pais' => 'Bolivia',
                'direccion' => 'Ciudad de prueba',
                'telefono' => '70000000',
                'email' => strtolower(str_replace(' ', '', $laboratorio)).'@asesor.test',
            ]
        );

        $medicamento = Medicamento::create([
            'categoria_id' => $categoriaModel->id,
            'laboratorio_id' => $laboratorioModel->id,
            'nombre_comercial' => $nombre,
            'principio_activo' => $principio,
            'presentacion' => 'Tabletas 500 mg x 20',
            'precio_venta' => 12.50,
            'stock_minimo' => 5,
        ]);

        foreach ($lotes as [$numero, $dias, $stock]) {
            Lote::create([
                'medicamento_id' => $medicamento->id,
                'numero_lote' => $numero,
                'fecha_vencimiento' => now()->addDays($dias)->toDateString(),
                'cantidad_stock' => $stock,
            ]);
        }

        return $medicamento;
    }

    public function test_invitado_no_puede_entrar_al_asesor(): void
    {
        $this->get(route('asesor.index'))->assertRedirect(route('bienvenida'));
    }

    public function test_todos_los_roles_pueden_consultar_el_asesor(): void
    {
        foreach (['administrador', 'farmaceutico', 'doctor'] as $rol) {
            $this->actingAs($this->usuario($rol))
                ->get(route('asesor.index'))
                ->assertOk();
        }
    }

    public function test_configuracion_tiene_entre_15_y_20_sintomas_sin_antibioticos(): void
    {
        $sintomas = config('asesor_sintomas.sintomas');

        $this->assertGreaterThanOrEqual(15, count($sintomas));
        $this->assertLessThanOrEqual(20, count($sintomas));

        foreach ($sintomas as $sintoma) {
            $this->assertArrayHasKey('nombre', $sintoma);
            $this->assertArrayHasKey('principios', $sintoma);
            $this->assertArrayHasKey('alerta', $sintoma);
            $this->assertNotEmpty($sintoma['principios']);
        }

        $principios = collect($sintomas)
            ->flatMap(fn (array $sintoma) => $sintoma['principios'])
            ->unique();

        foreach (['Amoxicilina', 'Azitromicina', 'Ciprofloxacino', 'Metformina', 'Losartán potásico', 'Salbutamol', 'Tramadol'] as $noPermitido) {
            $this->assertNotContains($noPermitido, $principios, "El asesor no debe recomendar {$noPermitido}.");
        }
    }

    public function test_doctor_consulta_solo_productos_con_stock_y_sin_boton_de_venta(): void
    {
        $panadol = $this->crearMedicamento('Panadol Asesor', 'Paracetamol', [
            ['LOT-CERCA', 10, 12],
        ]);
        $this->crearMedicamento('Amoxil Oculto', 'Amoxicilina', [
            ['LOT-ANTIBIOTICO', 20, 20],
        ], 'Antibióticos', 'Bayer');
        $this->crearMedicamento('Vencido Oculto', 'Paracetamol', [
            ['LOT-VENCIDO', -5, 30],
        ]);

        $respuesta = $this->actingAs($this->usuario())
            ->get(route('asesor.index', ['sintoma' => 'dolor-cabeza']));

        $respuesta->assertOk()
            ->assertSee('Asesor de síntomas')
            ->assertSee('Señales de alarma')
            ->assertSee('no recomienda antibióticos')
            ->assertSee($panadol->nombre_comercial)
            ->assertSee('Paracetamol')
            ->assertSee('LOT-CERCA')
            ->assertSee('Próximo lote')
            ->assertSee('Solo consulta')
            ->assertDontSee('Amoxil Oculto')
            ->assertDontSee('Vencido Oculto')
            ->assertDontSee('Agregar a venta');

        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_farmaceutico_y_administrador_pueden_agregar_al_pos(): void
    {
        $panadol = $this->crearMedicamento('Panadol POS', 'Paracetamol', [
            ['LOT-POS', 15, 8],
        ]);
        $urlPos = route('ventas.create', ['medicamento_id' => $panadol->id]);

        foreach (['farmaceutico', 'administrador'] as $rol) {
            $this->actingAs($this->usuario($rol))
                ->get(route('asesor.index', ['sintoma' => 'dolor-cabeza']))
                ->assertOk()
                ->assertSee('Agregar a venta')
                ->assertSee($urlPos, false)
                ->assertDontSee('Solo consulta');
        }
    }

    public function test_resultado_muestra_el_lote_fefo_y_oculta_vencidos(): void
    {
        $medicamento = $this->crearMedicamento('Ibuprofeno FEFO', 'Ibuprofeno', [
            ['LOT-TARDE', 120, 15],
            ['LOT-PRONTO', 8, 6],
            ['LOT-VENCIDO-FEFO', -2, 50],
        ]);

        $this->actingAs($this->usuario())
            ->get(route('asesor.index', ['sintoma' => 'dolor-cabeza']))
            ->assertOk()
            ->assertSee($medicamento->nombre_comercial)
            ->assertSee('LOT-PRONTO')
            ->assertSee(now()->addDays(8)->format('d/m/Y'))
            ->assertDontSee('LOT-TARDE')
            ->assertDontSee('LOT-VENCIDO-FEFO');
    }

    public function test_limita_los_resultados_a_ocho_productos(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->crearMedicamento('Paracetamol '.$i, 'Paracetamol', [
                ['LOT-LIMITE-'.$i, 30, 10],
            ]);
        }

        $respuesta = $this->actingAs($this->usuario())
            ->get(route('asesor.index', ['sintoma' => 'dolor-cabeza']));

        $respuesta->assertOk();
        $this->assertSame(8, substr_count($respuesta->getContent(), 'class="asesor-resultado"'));
    }

    public function test_sintoma_desconocido_muestra_estado_inicial(): void
    {
        $this->actingAs($this->usuario())
            ->get(route('asesor.index', ['sintoma' => 'no-existe']))
            ->assertOk()
            ->assertSee('Empieza seleccionando un síntoma')
            ->assertDontSee('Señales de alarma');
    }
}
