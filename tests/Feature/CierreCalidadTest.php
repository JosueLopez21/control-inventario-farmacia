<?php

namespace Tests\Feature;

use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Barrido de cierre (Fase 5): ninguna pantalla de la aplicación debe
 * fallar (500) para ninguno de los tres roles.
 */
class CierreCalidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrador_accede_a_todas_las_pantallas_sin_errores(): void
    {
        [$venta, $lote, $medicamento] = $this->preparar();

        $this->actingAs(User::factory()->create(['rol' => 'administrador', 'activo' => true]));

        $pantallas = [
            '/',
            '/perfil/password',
            '/medicamentos',
            '/medicamentos/crear',
            "/medicamentos/{$medicamento->id}/editar",
            "/medicamentos/{$medicamento->id}",
            '/lotes',
            '/lotes/crear',
            "/lotes/{$lote->id}/editar",
            '/alertas',
            '/ventas',
            '/ventas/nueva',
            "/ventas/{$venta->id}",
            "/ventas/{$venta->id}/imprimir",
            '/reportes',
            '/categorias',
            '/categorias/create',
            '/laboratorios',
            '/laboratorios/create',
            '/usuarios',
            '/usuarios/create',
            '/juego',
            '/juego/pregunta',
            '/asesor',
        ];

        foreach ($pantallas as $pantalla) {
            $respuesta = $this->get($pantalla);

            $this->assertTrue(
                $respuesta->isSuccessful() || $respuesta->isRedirect(),
                "La ruta {$pantalla} respondió {$respuesta->getStatusCode()} para administrador."
            );
        }
    }

    public function test_farmaceutico_accede_a_sus_pantallas_y_doctor_ve_solo_lectura(): void
    {
        $this->preparar();

        $farmaceutico = User::factory()->create(['rol' => 'farmaceutico', 'activo' => true]);
        $doctor = User::factory()->create(['rol' => 'doctor', 'activo' => true]);

        $deFarmaceutico = [
            '/', '/medicamentos', '/medicamentos/crear', '/lotes', '/lotes/crear',
            '/alertas', '/ventas', '/ventas/nueva', '/reportes',
            '/categorias', '/laboratorios',
            '/medicamentos-buscar/json?q=para&limit=8',
        ];

        foreach ($deFarmaceutico as $pantalla) {
            $respuesta = $this->actingAs($farmaceutico)->get($pantalla);

            $this->assertTrue(
                $respuesta->isSuccessful(),
                "La ruta {$pantalla} respondió {$respuesta->getStatusCode()} para farmacéutico."
            );
        }

        $pregunta = $this->actingAs($farmaceutico)->get('/juego/pregunta');

        $this->assertTrue(
            $pregunta->isSuccessful() || $pregunta->isRedirect(),
            "La ruta /juego/pregunta respondió {$pregunta->getStatusCode()} para farmacéutico."
        );

        $this->actingAs($farmaceutico)->get('/usuarios')->assertForbidden();

        $deDoctor = [
            '/', '/medicamentos', '/lotes', '/alertas', '/juego', '/asesor',
        ];

        foreach ($deDoctor as $pantalla) {
            $respuesta = $this->actingAs($doctor)->get($pantalla);

            $this->assertTrue(
                $respuesta->isSuccessful(),
                "La ruta {$pantalla} respondió {$respuesta->getStatusCode()} para doctor."
            );
        }

        foreach (['/medicamentos/crear', '/lotes/crear', '/ventas', '/ventas/nueva', '/reportes', '/usuarios'] as $pantalla) {
            $this->actingAs($doctor)->get($pantalla)->assertForbidden();
        }
    }

    /**
     * @return array{0: Venta, 1: Lote, 2: Medicamento}
     */
    private function preparar(): array
    {
        $lote = Lote::factory()->create();
        $venta = Venta::factory()->create(['monto_total' => 10.00]);

        return [$venta, $lote, $lote->medicamento()->first()];
    }
}
