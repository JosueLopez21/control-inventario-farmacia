<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Medicamento;
use App\Support\PreguntasJuego;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JuegoController extends Controller
{
    public function index(): View
    {
        $categorias = Categoria::query()
            ->whereHas('medicamentos')
            ->withCount('medicamentos')
            ->orderBy('nombre')
            ->get()
            ->map(function (Categoria $categoria): array {
                $ui = categoria_ui($categoria->nombre);

                return [
                    'id' => $categoria->id,
                    'nombre' => $categoria->nombre,
                    'icono' => $ui['icono'],
                    'color' => $ui['color'],
                    'suave' => $ui['suave'],
                    'total' => $categoria->medicamentos_count,
                ];
            })
            ->values();

        return view('juego.index', compact('categorias'));
    }

    public function pregunta(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'vistas' => ['nullable', 'string', 'max:300'],
        ]);

        $categoria = Categoria::query()->findOrFail($datos['categoria_id']);

        abort_unless(
            Medicamento::where('categoria_id', $categoria->id)->exists(),
            404,
            'Esta categoría todavía no tiene medicamentos para jugar.'
        );

        $banco = PreguntasJuego::banco();
        $clave = Str::slug($categoria->nombre);
        $clave = isset($banco[$clave]) ? $clave : 'general';

        // Evita repetir las preguntas ya hechas en la partida (si aún quedan otras).
        $vistas = collect(explode(',', (string) ($datos['vistas'] ?? '')))->filter()->all();
        $candidatas = collect($banco[$clave])
            ->map(fn (array $fila, int $indice) => ['id' => $clave.':'.$indice, 'fila' => $fila]);
        $disponibles = $candidatas->reject(fn (array $item) => in_array($item['id'], $vistas, true));
        $elegida = ($disponibles->isNotEmpty() ? $disponibles : $candidatas)->random();

        [$enunciado, $correcta, $incorrectas, $explicacion] = $elegida['fila'];

        $opciones = collect($incorrectas)->push($correcta)->shuffle()->values();
        $ui = categoria_ui($categoria->nombre);

        return response()->json([
            'id' => $elegida['id'],
            'categoria' => [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
                'icono' => $ui['icono'],
                'color' => $ui['color'],
                'suave' => $ui['suave'],
            ],
            'pregunta' => $enunciado,
            'opciones' => $opciones->all(),
            'respuesta' => (int) $opciones->search($correcta, true),
            'explicacion' => $explicacion,
        ]);
    }
}
