<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Medicamento;
use App\Models\User;
use App\Support\PreguntasJuego;
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
            ->assertSee('data-telon', false)
            ->assertSee('Tienes 20 segundos')
            ->assertSee('juegoCategorias', false)
            ->assertSee('juego.js', false);
    }

    public function test_endpoint_genera_preguntas_sencillas_con_cuatro_opciones(): void
    {
        $categoria = $this->crearCatalogo();
        $usuario = $this->usuario();
        $enunciados = collect(PreguntasJuego::banco()['analgesicos'])->pluck(0)->all();

        for ($intento = 0; $intento < 12; $intento++) {
            $respuesta = $this->actingAs($usuario)->getJson(
                route('juego.pregunta', ['categoria_id' => $categoria->id])
            );

            $respuesta->assertOk()
                ->assertJsonStructure([
                    'id',
                    'categoria' => ['id', 'nombre', 'icono', 'color', 'suave'],
                    'pregunta',
                    'opciones',
                    'respuesta',
                    'explicacion',
                ]);

            $datos = $respuesta->json();

            $this->assertCount(4, $datos['opciones']);
            $this->assertCount(4, array_unique($datos['opciones']));
            $this->assertGreaterThanOrEqual(0, $datos['respuesta']);
            $this->assertLessThan(4, $datos['respuesta']);
            $this->assertSame($categoria->id, $datos['categoria']['id']);
            $this->assertContains($datos['pregunta'], $enunciados);
        }
    }

    public function test_la_respuesta_indicada_es_la_correcta_del_banco(): void
    {
        $categoria = $this->crearCatalogo();
        $usuario = $this->usuario();
        $banco = collect(PreguntasJuego::banco()['analgesicos'])->keyBy(0);

        for ($intento = 0; $intento < 12; $intento++) {
            $datos = $this->actingAs($usuario)
                ->getJson(route('juego.pregunta', ['categoria_id' => $categoria->id]))
                ->json();

            $this->assertSame($banco[$datos['pregunta']][1], $datos['opciones'][$datos['respuesta']]);
        }
    }

    public function test_no_repite_preguntas_ya_vistas_en_la_partida(): void
    {
        $categoria = $this->crearCatalogo();
        $usuario = $this->usuario();
        $total = count(PreguntasJuego::banco()['analgesicos']);
        $vistas = [];

        for ($i = 0; $i < $total; $i++) {
            $datos = $this->actingAs($usuario)
                ->getJson(route('juego.pregunta', ['categoria_id' => $categoria->id, 'vistas' => implode(',', $vistas)]))
                ->json();

            $this->assertNotContains($datos['id'], $vistas);
            $vistas[] = $datos['id'];
        }
    }

    public function test_categoria_sin_banco_usa_preguntas_generales(): void
    {
        $this->crearCatalogo();
        $propia = Categoria::factory()->create(['nombre' => 'Categoría Nueva Sin Banco']);
        Medicamento::factory()->create(['categoria_id' => $propia->id]);

        $datos = $this->actingAs($this->usuario())
            ->getJson(route('juego.pregunta', ['categoria_id' => $propia->id]))
            ->assertOk()
            ->json();

        $this->assertStringStartsWith('general:', $datos['id']);
    }

    public function test_el_banco_esta_bien_formado_para_todas_las_categorias(): void
    {
        foreach (PreguntasJuego::banco() as $tema => $preguntas) {
            $this->assertNotEmpty($preguntas, $tema);

            foreach ($preguntas as [$enunciado, $correcta, $incorrectas, $explicacion]) {
                $opciones = array_merge($incorrectas, [$correcta]);
                $this->assertCount(3, $incorrectas, "{$tema}: {$enunciado}");
                $this->assertCount(4, array_unique($opciones), "{$tema}: {$enunciado}");
                $this->assertNotSame('', trim($explicacion), "{$tema}: {$enunciado}");
            }
        }

        foreach (['analgesicos', 'antibioticos', 'antiinflamatorios', 'antihipertensivos', 'antihistaminicos', 'antidiabeticos', 'vitaminas-y-suplementos', 'gastrointestinales', 'dermatologicos', 'broncodilatadores', 'antiparasitarios'] as $tema) {
            $this->assertGreaterThanOrEqual(5, count(PreguntasJuego::banco()[$tema]), $tema);
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
