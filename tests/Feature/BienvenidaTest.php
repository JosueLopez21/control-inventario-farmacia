<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Medicamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BienvenidaTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_redirigido_a_la_portada(): void
    {
        $this->get('/')->assertRedirect(route('bienvenida'));
        $this->get('/medicamentos')->assertRedirect(route('bienvenida'));
    }

    public function test_portada_muestra_modulos_y_estadisticas_sin_demo_por_defecto(): void
    {
        config(['feria.demo_access' => false]);

        $categoria = Categoria::factory()->create(['nombre' => 'Analgésicos']);
        $laboratorio = Laboratorio::factory()->create(['nombre' => 'Genfar']);
        Medicamento::create([
            'categoria_id' => $categoria->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre_comercial' => 'Panadol Feria',
            'principio_activo' => 'Paracetamol',
            'presentacion' => 'Tabletas 500 mg x 20',
            'precio_venta' => 12.50,
            'stock_minimo' => 5,
        ]);

        $respuesta = $this->get('/bienvenida');

        $respuesta->assertOk()
            ->assertSee('La farmacia que se siente')
            ->assertSee('Catálogo visual')
            ->assertSee('POS táctil')
            ->assertSee('data-contador="landingMedicamentos"', false)
            ->assertSee('Iniciar sesión')
            ->assertDontSee('Entra a la demo sin escribir contraseñas');
    }

    public function test_portada_muestra_demo_cuando_esta_habilitada(): void
    {
        config(['feria.demo_access' => true]);

        $respuesta = $this->get('/bienvenida');

        $respuesta->assertOk()
            ->assertSee('Entra a la demo sin escribir contraseñas')
            ->assertSee(route('demo.entrar', ['rol' => 'farmaceutico']), false)
            ->assertSee(route('demo.entrar', ['rol' => 'doctor']), false);
    }

    public function test_usuario_autenticado_no_ve_la_portada(): void
    {
        $usuario = User::factory()->create(['rol' => 'doctor', 'activo' => true]);

        $this->actingAs($usuario)->get('/bienvenida')->assertRedirect(route('dashboard'));
    }

    public function test_demo_deshabilitada_responde_404(): void
    {
        config(['feria.demo_access' => false]);

        $this->post(route('demo.entrar', ['rol' => 'doctor']))->assertNotFound();
    }

    public function test_demo_habilitada_inicia_sesion_con_el_rol_solicitado(): void
    {
        config(['feria.demo_access' => true]);

        $doctor = User::factory()->create(['rol' => 'doctor', 'activo' => true]);

        $respuesta = $this->post(route('demo.entrar', ['rol' => 'doctor']));

        $respuesta->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($doctor);
    }

    public function test_demo_no_permite_roles_fuera_del_alcance(): void
    {
        config(['feria.demo_access' => true]);

        $this->post(route('demo.entrar', ['rol' => 'administrador']))->assertNotFound();
    }
}
