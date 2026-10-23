<?php

namespace App\Http\Controllers;

use App\Http\Requests\VentaRequest;
use App\Models\Medicamento;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function __construct(private readonly VentaService $ventaService)
    {
    }

    public function index(Request $request): View
    {
        $desde = $request->query('fecha_inicio');
        $hasta = $request->query('fecha_fin');

        $ventas = Venta::query()
            ->withCount('detalles')
            ->when($desde, fn ($query) => $query->whereDate('fecha_venta', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('fecha_venta', '<=', $hasta))
            ->orderByDesc('fecha_venta')
            ->paginate(15)
            ->withQueryString();

        return view('ventas.index', compact('ventas', 'desde', 'hasta'));
    }

    public function create(Request $request): View
    {
        $itemsBase = old('items', []);

        if (blank($itemsBase) && $request->filled('medicamento_id')) {
            $itemsBase = [
                ['medicamento_id' => $request->integer('medicamento_id'), 'cantidad' => 1],
            ];
        }

        $itemsIniciales = collect($itemsBase)
            ->map(function ($item) {
                if (! is_array($item)) {
                    return null;
                }

                $medicamento = Medicamento::query()
                    ->withSum(['lotes as stock_vigente' => fn ($query) => $query->where('fecha_vencimiento', '>=', now()->toDateString())->where('cantidad_stock', '>', 0)], 'cantidad_stock')
                    ->find($item['medicamento_id'] ?? null);

                if (! $medicamento) {
                    return null;
                }

                return [
                    'id' => $medicamento->id,
                    'nombre_comercial' => $medicamento->nombre_comercial,
                    'precio_venta' => (float) $medicamento->precio_venta,
                    'stock' => (int) ($medicamento->stock_vigente ?? 0),
                    'cantidad' => max(1, (int) ($item['cantidad'] ?? 1)),
                ];
            })
            ->filter()
            ->values();

        return view('ventas.create', compact('itemsIniciales'));
    }

    public function store(VentaRequest $request): RedirectResponse
    {
        try {
            $venta = $this->ventaService->crearVenta($request->validated()['items']);
        } catch (\Throwable $e) {
            return redirect()
                ->route('ventas.create')
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('ventas.show', $venta)
            ->with('exito', "Venta #{$venta->id} registrada correctamente por un total de ".formato_bs($venta->monto_total).'.');
    }

    public function show(Venta $venta): View
    {
        $venta->load(['detalles.lote.medicamento']);

        return view('ventas.show', compact('venta'));
    }

    public function print(Venta $venta): View
    {
        $venta->load(['detalles.lote.medicamento']);

        return view('ventas.print', compact('venta'));
    }
}
