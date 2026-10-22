<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Medicamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'administrador', 'activo' => true]);
    }

    public function test_se_puede_crear_un_medicamento(): void
    {
        $categoria = Categoria::factory()->create();
        $laboratorio = Laboratorio::factory()->create();

        $respuesta = $this->actingAs($this->admin())->post('/medicamentos', [
            'categoria_id' => $categoria->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre_comercial' => 'Ibuprofeno Test',
            'principio_activo' => 'Ibuprofeno',
            'presentacion' => 'Tabletas 400mg x 10',
            'precio_venta' => 12.50,
            'stock_minimo' => 10,
        ]);

        $medicamento = Medicamento::firstWhere('nombre_comercial', 'Ibuprofeno Test');

        $this->assertNotNull($medicamento);
        $respuesta->assertRedirect(route('medicamentos.show', $medicamento));
        $this->assertDatabaseHas('medicamentos', ['nombre_comercial' => 'Ibuprofeno Test', 'precio_venta' => 12.50]);
    }

    public function test_no_se_puede_crear_medicamento_con_precio_invalido(): void
    {
        $categoria = Categoria::factory()->create();
        $laboratorio = Laboratorio::factory()->create();

        $respuesta = $this->actingAs($this->admin())->from('/medicamentos/crear')->post('/medicamentos', [
            'categoria_id' => $categoria->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre_comercial' => 'Precio Invalido',
            'principio_activo' => 'Test',
            'presentacion' => 'Caja',
            'precio_venta' => 0,
            'stock_minimo' => 5,
        ]);

        $respuesta->assertRedirect('/medicamentos/crear');
        $respuesta->assertSessionHasErrors('precio_venta');
        $this->assertDatabaseMissing('medicamentos', ['nombre_comercial' => 'Precio Invalido']);
    }

    public function test_se_puede_registrar_un_lote(): void
    {
        $medicamento = Medicamento::factory()->create();

        $respuesta = $this->actingAs($this->admin())->post('/lotes', [
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-TEST-001',
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'cantidad_stock' => 50,
        ]);

        $respuesta->assertRedirect(route('lotes.index'));
        $this->assertDatabaseHas('lotes', ['numero_lote' => 'LOT-TEST-001', 'cantidad_stock' => 50]);
    }

    public function test_no_se_puede_registrar_lote_con_numero_duplicado(): void
    {
        $medicamento = Medicamento::factory()->create();

        $this->actingAs($this->admin())->post('/lotes', [
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-DUPLICADO',
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'cantidad_stock' => 10,
        ]);

        $respuesta = $this->actingAs($this->admin())->from('/lotes/crear')->post('/lotes', [
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-DUPLICADO',
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'cantidad_stock' => 20,
        ]);

        $respuesta->assertRedirect('/lotes/crear');
        $respuesta->assertSessionHasErrors('numero_lote');
    }

    public function test_eliminar_categoria_con_medicamentos_muestra_mensaje_y_no_error_500(): void
    {
        $categoria = Categoria::factory()->create();
        Medicamento::factory()->create(['categoria_id' => $categoria->id]);

        $respuesta = $this->actingAs($this->admin())
            ->from('/categorias')
            ->delete(route('categorias.destroy', $categoria));

        $respuesta->assertRedirect('/categorias');
        $respuesta->assertSessionHas('error');
        $this->assertDatabaseHas('categorias', ['id' => $categoria->id]);
    }

    public function test_eliminar_lote_con_ventas_muestra_mensaje_y_no_error_500(): void
    {
        $medicamento = Medicamento::factory()->create();
        $lote = \App\Models\Lote::create([
            'medicamento_id' => $medicamento->id,
            'numero_lote' => 'LOT-CON-VENTA',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_stock' => 20,
        ]);
        \App\Models\DetalleVenta::create([
            'venta_id' => \App\Models\Venta::create(['fecha_venta' => now(), 'monto_total' => 10])->id,
            'lote_id' => $lote->id,
            'cantidad' => 1,
            'precio_unitario' => 10,
        ]);

        $respuesta = $this->actingAs($this->admin())
            ->from('/lotes')
            ->delete(route('lotes.destroy', $lote));

        $respuesta->assertRedirect('/lotes');
        $respuesta->assertSessionHas('error');
        $this->assertDatabaseHas('lotes', ['id' => $lote->id]);
    }
}
