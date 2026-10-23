<?php

namespace Tests\Feature;

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

    public function test_portada_muestra_un_solo_acceso_y_contenido_breve(): void
    {
        config(['feria.demo_access' => false]);

        $respuesta = $this->get('/bienvenida');
        $contenido = $respuesta->getContent();

        $respuesta->assertOk()
            ->assertSee('Control de inventario')
            ->assertSee('Entrar al sistema')
            ->assertSee('Inventario y lotes bajo control')
            ->assertSee('Ventas seguras, primero lo que vence antes')
            ->assertSee('Alertas de vencimiento y stock bajo')
            ->assertSee('Profesional de la salud')
            ->assertSee('img/doctora.webp', false)
            ->assertDontSee('Iniciar sesión')
            ->assertDontSee('Mini reto')
            ->assertDontSee('¿Listo para recorrer la farmacia?')
            ->assertDontSee('data-contador', false)
            ->assertDontSee('Entra a la demo sin escribir contraseñas');

        $this->assertSame(1, substr_count($contenido, route('login')));
        $this->assertFileExists(public_path('img/doctora.webp'));
    }

    public function test_portada_muestra_demo_cuando_esta_habilitada(): void
    {
        config(['feria.demo_access' => true]);

        $respuesta = $this->get('/bienvenida');
        $contenido = $respuesta->getContent();

        $respuesta->assertOk()
            ->assertSee('Acceso rápido demo')
            ->assertSee(route('demo.entrar', ['rol' => 'farmaceutico']), false)
            ->assertSee(route('demo.entrar', ['rol' => 'doctor']), false)
            ->assertSee('Entrar al sistema')
            ->assertDontSee('Iniciar sesión');

        $this->assertSame(1, substr_count($contenido, route('login')));
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
