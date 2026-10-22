<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_correcto_redirige_al_dashboard(): void
    {
        $usuario = User::factory()->create([
            'email' => 'admin@farmacia.test',
            'password' => '1234',
            'rol' => 'administrador',
            'activo' => true,
        ]);

        $respuesta = $this->post('/login', [
            'email' => 'admin@farmacia.test',
            'password' => '1234',
        ]);

        $respuesta->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_login_funciona_con_las_tres_credenciales_de_demostracion(): void
    {
        $this->seed(\Database\Seeders\UserSeeder::class);

        $credenciales = [
            ['admin@farmacia.test', '1234'],
            ['farmaceutica@farmacia.test', 'password'],
            ['doctor@farmacia.test', 'password'],
        ];

        foreach ($credenciales as [$email, $password]) {
            $respuesta = $this->post('/login', [
                'email' => $email,
                'password' => $password,
            ]);

            $respuesta->assertRedirect(route('dashboard'));
            $this->assertAuthenticated();
            $this->assertSame($email, auth()->user()->email);

            $this->post('/logout');
            $this->assertGuest();
        }
    }

    public function test_login_incorrecto_muestra_credenciales_incorrectas(): void
    {
        User::factory()->create([
            'email' => 'admin@farmacia.test',
            'password' => '1234',
            'activo' => true,
        ]);

        $respuesta = $this->from('/login')->post('/login', [
            'email' => 'admin@farmacia.test',
            'password' => 'clave-equivocada',
        ]);

        $respuesta->assertRedirect('/login');
        $respuesta->assertSessionHasErrors(['email' => 'Credenciales incorrectas.']);
        $this->assertGuest();
    }

    public function test_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        User::factory()->create([
            'email' => 'inactivo@farmacia.test',
            'password' => 'Clave2026*',
            'rol' => 'farmaceutico',
            'activo' => false,
        ]);

        $respuesta = $this->from('/login')->post('/login', [
            'email' => 'inactivo@farmacia.test',
            'password' => 'Clave2026*',
        ]);

        $respuesta->assertRedirect('/login');
        $respuesta->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_cierra_la_sesion(): void
    {
        $usuario = User::factory()->create(['activo' => true]);

        $respuesta = $this->actingAs($usuario)->post('/logout');

        $respuesta->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
