<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AsesorController extends Controller
{
    public function index(Request $request): View
    {
        $sintomas = config('asesor_sintomas.sintomas', []);
        $clave = trim((string) $request->query('sintoma'));
        $seleccionado = $clave !== '' && array_key_exists($clave, $sintomas) ? $clave : null;
        $sintoma = $seleccionado ? $sintomas[$seleccionado] : null;

        $resultados = $sintoma
            ? $this->buscarOpciones($sintoma['principios'] ?? [])
            : collect();

        return view('asesor.index', compact('sintomas', 'seleccionado', 'sintoma', 'resultados'));
    }

    /**
     * @param  array<int, string>  $principios
     * @return Collection<int, array<string, mixed>>
     */
    private function buscarOpciones(array $principios): Collection
    {
        $limite = max(1, (int) config('asesor_sintomas.limite_resultados', 8));
        $principios = array_values(array_unique($principios));
        $limitePorPrincipio = max(1, min(
            (int) config('asesor_sintomas.limite_por_principio', 8),
            (int) ceil($limite / max(1, count($principios)))
        ));
        $resultados = collect();

        foreach ($principios as $principio) {
            $resultados = $resultados->concat($this->consultarPrincipio($principio, $limitePorPrincipio));
        }

        if ($resultados->count() < $limite) {
            foreach ($principios as $principio) {
                $faltan = $limite - $resultados->count();

                if ($faltan <= 0) {
                    break;
                }

                $excluir = $resultados->pluck('id')->all();
                $resultados = $resultados->concat($this->consultarPrincipio($principio, $faltan, $excluir));
            }
        }

        return $resultados
            ->take($limite)
            ->map(function (Medicamento $medicamento): array {
                $ui = categoria_ui($medicamento->categoria?->nombre);
                $lote = $medicamento->lotes->first();

                return [
                    'id' => $medicamento->id,
                    'nombre' => $medicamento->nombre_comercial,
                    'presentacion' => $medicamento->presentacion,
                    'principio' => $medicamento->principio_activo,
                    'precio' => (float) $medicamento->precio_venta,
                    'stock' => (int) ($medicamento->stock_vigente ?? 0),
                    'categoria' => $medicamento->categoria?->nombre ?? 'General',
                    'icono' => $ui['icono'],
                    'color' => $ui['color'],
                    'suave' => $ui['suave'],
                    'lote_numero' => $lote?->numero_lote,
                    'lote_vence' => $lote?->fecha_vencimiento,
                ];
            })
            ->values();
    }

    /**
     * @param  array<int, int>  $excluir
     * @return Collection<int, Medicamento>
     */
    private function consultarPrincipio(string $principio, int $limite, array $excluir = []): Collection
    {
        return Medicamento::query()
            ->with([
                'categoria:id,nombre',
                'lotes' => fn ($query) => $query
                    ->where('fecha_vencimiento', '>=', now()->toDateString())
                    ->where('cantidad_stock', '>', 0)
                    ->orderBy('fecha_vencimiento'),
            ])
            ->where('principio_activo', $principio)
            ->when($excluir !== [], fn ($query) => $query->whereNotIn('id', $excluir))
            ->whereHas('lotes', fn ($query) => $query
                ->where('fecha_vencimiento', '>=', now()->toDateString())
                ->where('cantidad_stock', '>', 0))
            ->withSum(['lotes as stock_vigente' => fn ($query) => $query
                ->where('fecha_vencimiento', '>=', now()->toDateString())
                ->where('cantidad_stock', '>', 0)], 'cantidad_stock')
            ->orderBy('nombre_comercial')
            ->limit($limite)
            ->get();
    }
}
