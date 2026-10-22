<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccesoPorRolTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol): User
    {
        return User::factory()->create(['rol' => $rol, 'activo' => true]);
    }

    public function test_doctor_no_puede_entrar_a_ventas_ni_usuarios(): void
    {
        $doctor = $this->usuario('doctor');

        $this->actingAs($doctor)->get('/ventas')->assertForbidden();
        $this->actingAs($doctor)->get('/ventas/nueva')->assertForbidden();
        $this->actingAs($doctor)->get('/usuarios')->assertForbidden();
        $this->actingAs($doctor)->get('/reportes')->assertForbidden();
    }

    public function test_doctor_puede_ver_dashboard_catalogo_y_alertas(): void
    {
        $doctor = $this->usuario('doctor');

        $this->actingAs($doctor)->get('/')->assertOk();
        $this->actingAs($doctor)->get('/medicamentos')->assertOk();
        $this->actingAs($doctor)->get('/alertas')->assertOk();
    }

    public function test_farmaceutico_no_puede_gestionar_usuarios(): void
    {
        $farmaceutico = $this->usuario('farmaceutico');

        $this->actingAs($farmaceutico)->get('/usuarios')->assertForbidden();
    }

    public function test_farmaceutico_puede_acceder_a_ventas_y_reportes(): void
    {
        $farmaceutico = $this->usuario('farmaceutico');

        $this->actingAs($farmaceutico)->get('/ventas')->assertOk();
        $this->actingAs($farmaceutico)->get('/ventas/nueva')->assertOk();
        $this->actingAs($farmaceutico)->get('/reportes')->assertOk();
        $this->actingAs($farmaceutico)->get('/medicamentos/crear')->assertOk();
    }

    public function test_administrador_puede_gestionar_usuarios(): void
    {
        $admin = $this->usuario('administrador');

        $this->actingAs($admin)->get('/usuarios')->assertOk();
    }
}
