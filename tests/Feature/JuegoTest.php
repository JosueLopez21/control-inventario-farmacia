<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Medicamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JuegoTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol = 'doctor'): User
    {
        return User::factory()->create(['rol' => $rol, 'activo' => true]);
    }

    private function crearCatalogo(): Categoria
    {
        $categorias = collect(['Analgésicos', 'Antibióticos', 'Vitaminas', 'Dermatológicos'])
            ->map(fn (string $nombre) => Categoria::factory()->create(['nombre' => $nombre]))
            ->values();

        $laboratorios = collect(['Genfar', 'Bayer', 'Pfizer', 'Roche'])
            ->map(fn (string $nombre) => Laboratorio::factory()->create(['nombre' => $nombre]))
            ->values();

        $principios = ['Paracetamol', 'Amoxicilina', 'Vitamina C', 'Hidrocortisona'];

        foreach ($categorias as $indice => $categoria) {
            Medicamento::create([
                'categoria_id' => $categoria->id,
                'laboratorio_id' => $laboratorios[$indice]->id,
                'nombre_comercial' => 'Medicamento Juego '.$indice,
                'principio_activo' => $principios[$indice],
                'presentacion' => 'Tabletas x 10',
                'precio_venta' => 10 + $indice,
                'stock_minimo' => 5,
            ]);
        }

        return $categorias->first();
    }

    public function test_invitado_no_puede_entrar_al_juego(): void
    {
        $this->get(route('juego.index'))->assertRedirect(route('bienvenida'));
        $this->getJson(route('juego.pregunta', ['categoria_id' => 1]))->assertUnauthorized();
    }

    public function test_todos_los_roles_pueden_entrar_al_juego(): void
    {
        $this->crearCatalogo();

        foreach (['administrador', 'farmaceutico', 'doctor'] as $rol) {
            $this->actingAs($this->usuario($rol))
                ->get(route('juego.index'))
                ->assertOk();
        }
    }

    public function test_pagina_muestra_ruleta_y_reto_de_lotes(): void
    {
        $categoria = $this->crearCatalogo();

        $respuesta = $this->actingAs($this->usuario())
            ->get(route('juego.index'));

        $respuesta->assertOk()
            ->assertSee('La ruleta del catálogo')
            ->assertSee('Girar la ruleta')
            ->assertSee('de 5')
            ->assertSee('¿Qué lote sale primero?')
            ->assertSee('Mejor puntaje')
            ->assertSee($categoria->nombre)
            ->assertSee('data-juego-ruleta', false)
            ->assertSee('data-juego-fefo', false)
            ->assertSee('juegoCategorias', false)
            ->assertSee('juego.js', false);
    }

    public function test_endpoint_genera_preguntas_reales_con_cuatro_opciones(): void
    {
        $categoria = $this->crearCatalogo();
        $usuario = $this->usuario();

        for ($intento = 0; $intento < 12; $intento++) {
            $respuesta = $this->actingAs($usuario)->getJson(
                route('juego.pregunta', ['categoria_id' => $categoria->id])
            );

            $respuesta->assertOk()
                ->assertJsonStructure([
                    'categoria' => ['id', 'nombre', 'icono', 'color', 'suave'],
                    'pregunta',
                    'opciones',
                    'respuesta',
                    'explicacion',
                ]);

            $datos = $respuesta->json();
            $correcta = $datos['opciones'][$datos['respuesta']];

            $this->assertCount(4, $datos['opciones']);
            $this->assertCount(4, array_unique($datos['opciones']));
            $this->assertGreaterThanOrEqual(0, $datos['respuesta']);
            $this->assertLessThan(4, $datos['respuesta']);
            $this->assertSame($categoria->id, $datos['categoria']['id']);

            if (str_contains($datos['pregunta'], 'categoría')) {
                $this->assertSame('Medicamento Juego 0', $correcta);
            } elseif (str_contains($datos['pregunta'], 'laboratorio')) {
                $this->assertSame('Genfar', $correcta);
            } else {
                $this->assertSame('Paracetamol', $correcta);
            }
        }
    }

    public function test_endpoint_rechaza_categoria_invalida_o_vacia(): void
    {
        $this->crearCatalogo();
        $usuario = $this->usuario();
        $vacia = Categoria::factory()->create(['nombre' => 'Categoría sin stock']);

        $this->actingAs($usuario)
            ->getJson(route('juego.pregunta'))
            ->assertStatus(422);

        $this->actingAs($usuario)
            ->getJson(route('juego.pregunta', ['categoria_id' => 999999]))
            ->assertStatus(422);

        $this->actingAs($usuario)
            ->getJson(route('juego.pregunta', ['categoria_id' => $vacia->id]))
            ->assertNotFound();
    }
}
