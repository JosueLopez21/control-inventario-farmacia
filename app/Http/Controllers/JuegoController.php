<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Medicamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        ]);

        $categoria = Categoria::query()->findOrFail($datos['categoria_id']);

        $medicamento = Medicamento::query()
            ->where('categoria_id', $categoria->id)
            ->with('laboratorio:id,nombre')
            ->inRandomOrder()
            ->first();

        abort_unless($medicamento, 404, 'Esta categoría todavía no tiene medicamentos para jugar.');

        foreach (collect([1, 2, 3])->shuffle() as $tipo) {
            $pregunta = match ($tipo) {
                1 => $this->preguntaDeCategoria($categoria, $medicamento),
                2 => $this->preguntaDeLaboratorio($medicamento),
                3 => $this->preguntaDePrincipioActivo($medicamento),
                default => null,
            };

            if ($pregunta) {
                $ui = categoria_ui($categoria->nombre);

                return response()->json([
                    'categoria' => [
                        'id' => $categoria->id,
                        'nombre' => $categoria->nombre,
                        'icono' => $ui['icono'],
                        'color' => $ui['color'],
                        'suave' => $ui['suave'],
                    ],
                    'pregunta' => $pregunta['pregunta'],
                    'opciones' => $pregunta['opciones'],
                    'respuesta' => $pregunta['respuesta'],
                    'explicacion' => $pregunta['explicacion'],
                ]);
            }
        }

        abort(404, 'No hay suficientes datos para generar una pregunta.');
    }

    /**
     * @return array{pregunta: string, opciones: array<int, string>, respuesta: int, explicacion: string}|null
     */
    private function preguntaDeCategoria(Categoria $categoria, Medicamento $medicamento): ?array
    {
        $correcta = $medicamento->nombre_comercial;

        $distractores = Medicamento::query()
            ->where('categoria_id', '!=', $categoria->id)
            ->where('nombre_comercial', '!=', $correcta)
            ->inRandomOrder()
            ->limit(12)
            ->pluck('nombre_comercial')
            ->unique()
            ->take(3)
            ->values();

        if ($distractores->count() < 3) {
            return null;
        }

        return $this->armarPregunta(
            '¿Cuál de estos medicamentos pertenece a la categoría '.$categoria->nombre.'?',
            $distractores,
            $correcta,
            $correcta.' es parte de '.$categoria->nombre.'.'
        );
    }

    /**
     * @return array{pregunta: string, opciones: array<int, string>, respuesta: int, explicacion: string}|null
     */
    private function preguntaDeLaboratorio(Medicamento $medicamento): ?array
    {
        $laboratorio = $medicamento->laboratorio;

        if (! $laboratorio) {
            return null;
        }

        $correcta = $laboratorio->nombre;

        $distractores = Laboratorio::query()
            ->whereKeyNot($laboratorio->id)
            ->inRandomOrder()
            ->limit(10)
            ->pluck('nombre')
            ->unique()
            ->take(3)
            ->values();

        if ($distractores->count() < 3) {
            return null;
        }

        return $this->armarPregunta(
            '¿Qué laboratorio fabrica '.$medicamento->nombre_comercial.'?',
            $distractores,
            $correcta,
            $medicamento->nombre_comercial.' es elaborado por '.$correcta.'.'
        );
    }

    /**
     * @return array{pregunta: string, opciones: array<int, string>, respuesta: int, explicacion: string}|null
     */
    private function preguntaDePrincipioActivo(Medicamento $medicamento): ?array
    {
        $correcta = trim((string) $medicamento->principio_activo);

        if ($correcta === '') {
            return null;
        }

        $distractores = Medicamento::query()
            ->where('id', '!=', $medicamento->id)
            ->whereNotNull('principio_activo')
            ->where('principio_activo', '!=', $medicamento->principio_activo)
            ->inRandomOrder()
            ->limit(20)
            ->pluck('principio_activo')
            ->map(fn (string $principio) => trim($principio))
            ->filter()
            ->unique()
            ->take(3)
            ->values();

        if ($distractores->count() < 3) {
            return null;
        }

        return $this->armarPregunta(
            '¿Qué principio activo contiene '.$medicamento->nombre_comercial.'?',
            $distractores,
            $correcta,
            $medicamento->nombre_comercial.' contiene '.$correcta.'.'
        );
    }

    /**
     * @param  Collection<int, string>  $distractores
     * @return array{pregunta: string, opciones: array<int, string>, respuesta: int, explicacion: string}
     */
    private function armarPregunta(string $pregunta, Collection $distractores, string $correcta, string $explicacion): array
    {
        $opciones = $distractores
            ->push($correcta)
            ->unique()
            ->shuffle()
            ->values();

        return [
            'pregunta' => $pregunta,
            'opciones' => $opciones->all(),
            'respuesta' => (int) $opciones->search($correcta, true),
            'explicacion' => $explicacion,
        ];
    }
}
