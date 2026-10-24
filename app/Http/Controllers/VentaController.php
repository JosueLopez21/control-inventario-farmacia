<?php

namespace App\Http\Controllers;

use App\Http\Requests\VentaRequest;
use App\Models\Categoria;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function __construct(private readonly VentaService $ventaService) {}

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
        if ($request->query('modo') === 'clasico') {
            return view('ventas.create-clasico', [
                'medicamentos' => Medicamento::bolivianosPrimero()->orderBy('nombre_comercial')->get(['id', 'nombre_comercial']),
            ]);
        }

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
                    ->with(['categoria:id,nombre', 'laboratorio:id,nombre'])
                    ->withSum(['lotes as stock_vigente' => fn ($query) => $query->where('fecha_vencimiento', '>=', now()->toDateString())->where('cantidad_stock', '>', 0)], 'cantidad_stock')
                    ->find($item['medicamento_id'] ?? null);

                if (! $medicamento) {
                    return null;
                }

                $ui = categoria_ui($medicamento->categoria?->nombre);

                return [
                    'id' => $medicamento->id,
                    'nombre_comercial' => $medicamento->nombre_comercial,
                    'presentacion' => $medicamento->presentacion,
                    'precio_venta' => (float) $medicamento->precio_venta,
                    'stock' => (int) ($medicamento->stock_vigente ?? 0),
                    'cantidad' => max(1, (int) ($item['cantidad'] ?? 1)),
                    'categoria' => $medicamento->categoria?->nombre ?? 'General',
                    'categoria_icono' => $ui['icono'],
                    'categoria_color' => $ui['color'],
                    'categoria_suave' => $ui['suave'],
                    'laboratorio' => $medicamento->laboratorio?->nombre,
                ];
            })
            ->filter()
            ->values();

        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);

        return view('ventas.create', compact('itemsIniciales', 'categorias'));
    }

    public function store(VentaRequest $request): RedirectResponse
    {
        try {
            $datos = $request->validated();
            $venta = $this->ventaService->crearVenta($datos['items'], [
                'metodo_pago' => $datos['metodo_pago'] ?? 'efectivo',
                'monto_recibido' => $datos['monto_recibido'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $destino = $request->input('modo') === 'clasico'
                ? route('ventas.create', ['modo' => 'clasico'])
                : route('ventas.create');

            return redirect($destino)
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('ventas.show', $venta)
            ->with('exito', "Venta #{$venta->id} registrada correctamente por un total de ".formato_bs($venta->monto_total).'.')
            ->with('animacion', 'venta')
            ->with('resumen_stock', $venta->resumenStock)
            ->with('animacion_datos', [
                'venta_id' => $venta->id,
                'total' => formato_bs($venta->monto_total),
                'metodo' => $venta->metodo_pago_etiqueta,
                'efectivo' => $venta->metodo_pago === 'efectivo',
                'recibido' => formato_bs($venta->monto_recibido),
                'cambio' => formato_bs($venta->cambio),
                'hay_cambio' => (float) $venta->cambio > 0,
                'stock' => $venta->resumenStock,
                'comprobante' => route('ventas.show', $venta),
                'nueva' => route('ventas.create'),
                'movimientos' => route('movimientos.index', ['venta' => $venta->id]),
            ]);
    }

    public function show(Venta $venta): View
    {
        $venta->load(['detalles.lote.medicamento']);

        // Stock vigente actual de cada medicamento vendido (para ver cómo bajó el inventario).
        $idsMedicamentos = $venta->detalles->pluck('lote.medicamento_id')->filter()->unique()->values();

        $stockActual = Medicamento::query()
            ->whereIn('id', $idsMedicamentos)
            ->withSum(['lotes as stock_vigente' => fn ($query) => $query
                ->where('fecha_vencimiento', '>=', now()->toDateString())], 'cantidad_stock')
            ->get()
            ->mapWithKeys(fn (Medicamento $medicamento) => [$medicamento->id => (int) ($medicamento->stock_vigente ?? 0)]);

        $movimientos = MovimientoInventario::where('venta_id', $venta->id)->orderBy('id')->get();

        return view('ventas.show', compact('venta', 'stockActual', 'movimientos'));
    }

    public function print(Venta $venta): View
    {
        $venta->load(['detalles.lote.medicamento']);

        return view('ventas.print', compact('venta'));
    }
}
