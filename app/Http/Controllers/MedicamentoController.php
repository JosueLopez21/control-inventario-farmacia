<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicamentoRequest;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Medicamento;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicamentoController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $q = trim((string) $request->query('q'));
        $categoriaId = $request->query('categoria_id');
        $laboratorioId = $request->query('laboratorio_id');

        $medicamentos = Medicamento::query()
            ->with([
                'categoria:id,nombre',
                'laboratorio:id,nombre',
                'lotes' => fn ($query) => $query->orderBy('fecha_vencimiento'),
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre_comercial', 'ilike', "%{$q}%")
                        ->orWhere('principio_activo', 'ilike', "%{$q}%");
                });
            })
            ->when($categoriaId, fn ($query) => $query->where('categoria_id', $categoriaId))
            ->when($laboratorioId, fn ($query) => $query->where('laboratorio_id', $laboratorioId))
            ->orderBy('nombre_comercial')
            ->paginate(24)
            ->withQueryString();

        $categorias = Categoria::query()
            ->withCount(['medicamentos as total' => function ($query) use ($q, $laboratorioId) {
                $query->when($q !== '', function ($subquery) use ($q) {
                    $subquery->where(function ($search) use ($q) {
                        $search->where('nombre_comercial', 'ilike', "%{$q}%")
                            ->orWhere('principio_activo', 'ilike', "%{$q}%");
                    });
                })->when($laboratorioId, fn ($subquery) => $subquery->where('laboratorio_id', $laboratorioId));
            }])
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $laboratorios = Laboratorio::orderBy('nombre')->get(['id', 'nombre']);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('medicamentos._resultados', compact('medicamentos', 'categorias', 'q', 'categoriaId', 'laboratorioId'))->render(),
                'total' => $medicamentos->total(),
            ]);
        }

        return view('medicamentos.index', compact('medicamentos', 'categorias', 'laboratorios', 'q', 'categoriaId', 'laboratorioId'));
    }

    public function create(): View
    {
        return view('medicamentos.create', [
            'categorias' => Categoria::orderBy('nombre')->get(['id', 'nombre']),
            'laboratorios' => Laboratorio::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(MedicamentoRequest $request): RedirectResponse
    {
        $medicamento = Medicamento::create($request->validated());

        return redirect()->route('medicamentos.show', $medicamento)->with('exito', 'Medicamento registrado correctamente.');
    }

    public function show(Medicamento $medicamento): View
    {
        $medicamento->load([
            'categoria',
            'laboratorio',
            'lotes' => fn ($query) => $query->orderBy('fecha_vencimiento'),
        ]);

        $stockVigente = $medicamento->lotes
            ->filter(fn ($lote) => $lote->fecha_vencimiento->gte(now()->startOfDay()) && $lote->cantidad_stock > 0)
            ->sum('cantidad_stock');

        return view('medicamentos.show', compact('medicamento', 'stockVigente'));
    }

    public function edit(Medicamento $medicamento): View
    {
        return view('medicamentos.edit', [
            'medicamento' => $medicamento,
            'categorias' => Categoria::orderBy('nombre')->get(['id', 'nombre']),
            'laboratorios' => Laboratorio::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function update(MedicamentoRequest $request, Medicamento $medicamento): RedirectResponse
    {
        $medicamento->update($request->validated());

        return redirect()->route('medicamentos.show', $medicamento)->with('exito', 'Medicamento actualizado correctamente.');
    }

    public function destroy(Medicamento $medicamento): RedirectResponse
    {
        $lotes = $medicamento->lotes()->count();

        if ($lotes > 0) {
            return back()->with('error', "No se puede eliminar \"{$medicamento->nombre_comercial}\": tiene {$lotes} lotes asociados (y posiblemente ventas).");
        }

        try {
            $medicamento->delete();
        } catch (QueryException) {
            return back()->with('error', 'No se puede eliminar el medicamento porque tiene registros asociados.');
        }

        return redirect()->route('medicamentos.index')->with('exito', 'Medicamento eliminado correctamente.');
    }

    /**
     * Endpoint JSON para el buscador del punto de venta.
     */
    public function buscar(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q'));

        if (mb_strlen($q) < 2) {
            return response()->json(['resultados' => []]);
        }

        $resultados = Medicamento::query()
            ->withSum(['lotes as stock_vigente' => fn ($query) => $query->where('fecha_vencimiento', '>=', now()->toDateString())->where('cantidad_stock', '>', 0)], 'cantidad_stock')
            ->where(function ($sub) use ($q) {
                $sub->where('nombre_comercial', 'ilike', "%{$q}%")
                    ->orWhere('principio_activo', 'ilike', "%{$q}%");
            })
            ->orderBy('nombre_comercial')
            ->limit(15)
            ->get(['id', 'nombre_comercial', 'principio_activo', 'presentacion', 'precio_venta']);

        return response()->json([
            'resultados' => $resultados->map(fn ($medicamento) => [
                'id' => $medicamento->id,
                'nombre_comercial' => $medicamento->nombre_comercial,
                'principio_activo' => $medicamento->principio_activo,
                'presentacion' => $medicamento->presentacion,
                'precio_venta' => (float) $medicamento->precio_venta,
                'stock' => (int) ($medicamento->stock_vigente ?? 0),
            ]),
        ]);
    }
}
