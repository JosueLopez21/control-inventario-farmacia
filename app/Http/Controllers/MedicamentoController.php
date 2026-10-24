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
    private const CARACTERES_ACENTUADOS = 'áàäâãéèëêíìïîóòöôõúùüûñç';

    private const CARACTERES_SIN_ACENTO = 'aaaaaeeeeiiiiooooouuuunc';

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
            ->bolivianosPrimero()
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
     * Endpoint JSON para el buscador del punto de venta y el selector de lotes.
     */
    public function buscar(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q'));
        $terminoNormalizado = $this->normalizarTexto($q);
        $categoriaId = $request->query('categoria_id');
        $limite = max(1, min(50, (int) $request->integer('limit', 8)));
        $desplazamiento = max(0, (int) $request->integer('offset', 0));
        $incluirAgotados = $request->boolean('incluir_agotados');
        $hoy = now()->toDateString();

        if ($q !== '' && mb_strlen($q) < 2) {
            return response()->json([
                'resultados' => [],
                'total' => 0,
                'hay_mas' => false,
            ]);
        }

        $consulta = Medicamento::query()
            ->with(['categoria:id,nombre', 'laboratorio:id,nombre'])
            ->withSum(
                ['lotes as stock_vigente' => fn ($query) => $query
                    ->where('fecha_vencimiento', '>=', $hoy)
                    ->where('cantidad_stock', '>', 0)],
                'cantidad_stock'
            )
            ->when($categoriaId, fn ($query) => $query->where('categoria_id', $categoriaId))
            ->when(! $incluirAgotados, fn ($query) => $query->whereHas('lotes', fn ($sub) => $sub
                ->where('fecha_vencimiento', '>=', $hoy)
                ->where('cantidad_stock', '>', 0)));

        // Los laboratorios bolivianos siempre se muestran primero.
        $consulta->bolivianosPrimero();

        if ($terminoNormalizado !== '') {
            $nombreNormalizado = "translate(lower(nombre_comercial), '".self::CARACTERES_ACENTUADOS."', '".self::CARACTERES_SIN_ACENTO."')";
            $principioNormalizado = "translate(lower(principio_activo), '".self::CARACTERES_ACENTUADOS."', '".self::CARACTERES_SIN_ACENTO."')";
            $presentacionNormalizada = "translate(lower(presentacion), '".self::CARACTERES_ACENTUADOS."', '".self::CARACTERES_SIN_ACENTO."')";
            $laboratorioNormalizado = "translate(lower(nombre), '".self::CARACTERES_ACENTUADOS."', '".self::CARACTERES_SIN_ACENTO."')";
            $contiene = '%'.$this->escaparLike($terminoNormalizado).'%';
            $empieza = $this->escaparLike($terminoNormalizado).'%';

            $consulta->where(function ($sub) use ($nombreNormalizado, $principioNormalizado, $presentacionNormalizada, $laboratorioNormalizado, $contiene) {
                $sub->whereRaw("{$nombreNormalizado} LIKE ?", [$contiene])
                    ->orWhereRaw("{$principioNormalizado} LIKE ?", [$contiene])
                    ->orWhereRaw("{$presentacionNormalizada} LIKE ?", [$contiene])
                    ->orWhereHas('laboratorio', fn ($laboratorio) => $laboratorio->whereRaw("{$laboratorioNormalizado} LIKE ?", [$contiene]));
            })->orderByRaw(
                "CASE WHEN {$nombreNormalizado} LIKE ? OR {$principioNormalizado} LIKE ? THEN 0 ELSE 1 END",
                [$empieza, $empieza]
            );
        }

        $total = (clone $consulta)->count();

        $resultados = $consulta
            ->orderBy('nombre_comercial')
            ->skip($desplazamiento)
            ->take($limite)
            ->get([
                'id',
                'categoria_id',
                'laboratorio_id',
                'nombre_comercial',
                'principio_activo',
                'presentacion',
                'precio_venta',
                'stock_minimo',
            ]);

        return response()->json([
            'resultados' => $resultados->map(function ($medicamento) {
                $ui = categoria_ui($medicamento->categoria?->nombre);
                $stock = (int) ($medicamento->stock_vigente ?? 0);

                return [
                    'id' => $medicamento->id,
                    'nombre_comercial' => $medicamento->nombre_comercial,
                    'principio_activo' => $medicamento->principio_activo,
                    'presentacion' => $medicamento->presentacion,
                    'precio_venta' => (float) $medicamento->precio_venta,
                    'stock' => $stock,
                    'stock_minimo' => (int) $medicamento->stock_minimo,
                    'vendible' => $stock > 0,
                    'categoria' => $medicamento->categoria?->nombre ?? 'General',
                    'categoria_icono' => $ui['icono'],
                    'categoria_color' => $ui['color'],
                    'categoria_suave' => $ui['suave'],
                    'laboratorio' => $medicamento->laboratorio?->nombre ?? 'Sin laboratorio',
                ];
            }),
            'total' => $total,
            'hay_mas' => $desplazamiento + $resultados->count() < $total,
        ]);
    }

    private function normalizarTexto(string $texto): string
    {
        return strtr(mb_strtolower($texto), [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c',
        ]);
    }

    private function escaparLike(string $texto): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $texto);
    }
}
