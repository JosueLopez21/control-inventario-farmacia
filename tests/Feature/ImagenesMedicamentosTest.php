<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Medicamento;
use App\Models\User;
use App\Support\ImagenMedicamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImagenesMedicamentosTest extends TestCase
{
    use RefreshDatabase;

    private array $creados = [];

    protected function tearDown(): void
    {
        foreach ($this->creados as $archivo) {
            @unlink($archivo);
        }

        parent::tearDown();
    }

    private function imagenFalsa(string $nombre): void
    {
        $carpeta = public_path('img/medicamentos');
        @mkdir($carpeta, 0777, true);
        $ruta = $carpeta.'/'.$nombre;

        if (! file_exists($ruta)) {
            file_put_contents($ruta, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));
            $this->creados[] = $ruta;
        }
    }

    private function medicamento(string $laboratorio, string $pais, string $presentacion): Medicamento
    {
        return Medicamento::factory()->create([
            'categoria_id' => Categoria::factory()->create()->id,
            'laboratorio_id' => Laboratorio::factory()->create(['nombre' => $laboratorio, 'pais' => $pais])->id,
            'presentacion' => $presentacion,
        ]);
    }

    public function test_clasifica_la_forma_por_la_presentacion(): void
    {
        $this->assertSame('tabletas', ImagenMedicamento::forma('Tabletas 500 mg x 100'));
        $this->assertSame('capsulas', ImagenMedicamento::forma('Cápsulas 20 mg x 28'));
        $this->assertSame('jarabe', ImagenMedicamento::forma('Suspensión 250 mg/5 ml x 60 ml'));
        $this->assertSame('crema', ImagenMedicamento::forma('Gel 1% x 50 g'));
        $this->assertSame('ampollas', ImagenMedicamento::forma('Ampollas 1 g/2 ml x 5'));
        $this->assertSame('frasco-ampolla', ImagenMedicamento::forma('Frasco ampolla 1.200.000 UI'));
        $this->assertSame('inhalador', ImagenMedicamento::forma('Inhalador 100 mcg x 200 dosis'));
        $this->assertSame('sobre', ImagenMedicamento::forma('Sobre 20,5 g'));
        $this->assertSame('gotas', ImagenMedicamento::forma('Gotas 100 mg/ml x 15 ml'));
        $this->assertNull(ImagenMedicamento::forma('Presentación rara'));
    }

    public function test_solo_los_bolivianos_con_archivo_muestran_imagen(): void
    {
        $this->imagenFalsa('tabletas_inti.png');

        $boliviano = $this->medicamento('Laboratorios INTI', 'Bolivia', 'Tabletas 500 mg x 100');
        $extranjero = $this->medicamento('Laboratorios Bagó', 'Argentina', 'Tabletas 500 mg x 100');
        $sinArchivo = $this->medicamento('COFAR', 'Bolivia', 'Presentación rara');

        $this->assertStringContainsString('img/medicamentos/tabletas_inti.png', (string) imagen_medicamento($boliviano));
        $this->assertNull(ImagenMedicamento::foto($extranjero));
        $this->assertNull(ImagenMedicamento::foto($sinArchivo));
    }

    public function test_todos_los_medicamentos_tienen_imagen_aunque_no_haya_foto(): void
    {
        $extranjero = $this->medicamento('Laboratorios Bagó', 'Argentina', 'Solución inyectable 10 mg/ml x 5 ampollas');
        $sinArchivo = $this->medicamento('Laboratorio Chileno', 'Chile', 'Tabletas 10 mg x 20');
        $raro = $this->medicamento('Lab Raro', 'Chile', 'Presentación rara');

        foreach ([$extranjero, $sinArchivo, $raro] as $medicamento) {
            $url = imagen_medicamento($medicamento);
            $this->assertNotNull($url);
            $this->assertStringContainsString('img/formas/', $url);
        }

        $this->assertStringContainsString('ampollas_', imagen_medicamento($extranjero));
        $this->assertStringContainsString('tabletas_', imagen_medicamento($sinArchivo));
        $this->assertStringContainsString('caja_', imagen_medicamento($raro));
    }

    public function test_existen_las_ilustraciones_de_todas_las_formas_y_categorias(): void
    {
        $formas = ['tabletas', 'capsulas', 'jarabe', 'gotas', 'crema', 'ampollas', 'frasco-ampolla', 'inhalador', 'sobre', 'supositorios', 'ovulos', 'pluma', 'locion', 'spray', 'caja'];

        foreach (array_keys(config('feria.categorias')) as $categoria) {
            foreach ($formas as $forma) {
                $this->assertFileExists(public_path("img/formas/{$forma}_{$categoria}.svg"));
            }
        }
    }

    public function test_clasifica_todas_las_presentaciones_del_catalogo_base(): void
    {
        $this->assertSame('ampollas', ImagenMedicamento::formaIlustracion('Solución inyectable 10 mg/ml x 5 ampollas'));
        $this->assertSame('jarabe', ImagenMedicamento::formaIlustracion('Solución oral 5 mg/5 ml x 120 ml'));
        $this->assertSame('spray', ImagenMedicamento::formaIlustracion('Solución nasal 100 mcg'));
        $this->assertSame('inhalador', ImagenMedicamento::formaIlustracion('Polvo para inhalación 200 mcg'));
        $this->assertSame('sobre', ImagenMedicamento::formaIlustracion('Polvo oral x 16 sobres'));
        $this->assertSame('pluma', ImagenMedicamento::formaIlustracion('Pluma precargada 100 UI/ml'));
        $this->assertSame('locion', ImagenMedicamento::formaIlustracion('Loción 1% x 60 ml'));
        $this->assertSame('supositorios', ImagenMedicamento::formaIlustracion('Supositorios 100 mg x 6'));
        $this->assertSame('ovulos', ImagenMedicamento::formaIlustracion('Óvulos vaginales 500 mg'));
    }

    public function test_catalogo_buscador_y_detalle_usan_la_imagen(): void
    {
        $this->imagenFalsa('jarabe_vita.png');
        $medicamento = $this->medicamento('Laboratorios Vita', 'Bolivia', 'Jarabe 15 mg/5 ml x 120 ml');
        $medicamento->update(['nombre_comercial' => 'Ambroxol Foto Prueba']);
        $medicamento->lotes()->create([
            'numero_lote' => 'LOT-IMG-1',
            'fecha_vencimiento' => now()->addDays(200)->toDateString(),
            'cantidad_stock' => 10,
        ]);

        $this->actingAs(User::factory()->create(['rol' => 'farmaceutico', 'activo' => true]));

        $this->get(route('medicamentos.index'))->assertOk()->assertSee('med-card__foto', false)->assertSee('jarabe_vita.png', false);
        $this->get(route('medicamentos.show', $medicamento))->assertOk()->assertSee('med-show-foto', false);
        $this->getJson(route('medicamentos.buscar', ['q' => 'foto prueba']))
            ->assertOk()
            ->assertJsonPath('resultados.0.imagen', fn ($url) => str_contains((string) $url, 'jarabe_vita.png'));
    }
}
