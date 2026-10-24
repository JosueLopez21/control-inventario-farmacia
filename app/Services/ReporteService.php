<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\Venta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    /**
     * Ventas de hoy (conteo).
     */
    public function ventasHoy(): int
    {
        return Venta::whereDate('fecha_venta', now()->toDateString())->count();
    }

    /**
     * Ingresos de hoy.
     */
    public function ingresosHoy(): float
    {
        return (float) Venta::whereDate('fecha_venta', now()->toDateString())->sum('monto_total');
    }

    public function totalMedicamentos(): int
    {
        return Medicamento::count();
    }

    public function lotesPorVencer(int $dias = 30): int
    {
        return Lote::porVencer($dias)->count();
    }

    public function lotesVencidos(): int
    {
        return Lote::vencidos()->count();
    }

    /**
     * Unidades (no lotes) que hay en los lotes por vencer.
     */
    public function unidadesPorVencer(int $dias = 30): int
    {
        return (int) Lote::porVencer($dias)->sum('cantidad_stock');
    }

    /**
     * Unidades (no lotes) que hay en lotes ya vencidos.
     */
    public function unidadesVencidas(): int
    {
        return (int) Lote::vencidos()->sum('cantidad_stock');
    }

    /**
     * Unidades que faltan para que los medicamentos con stock bajo lleguen a su mínimo.
     */
    public function deficitStockBajo(Collection $stockBajo): int
    {
        return (int) $stockBajo->sum(fn (Medicamento $medicamento) => max(0, $medicamento->stock_minimo - (int) ($medicamento->stock_vigente ?? 0)));
    }

    public function ultimasVentas(int $cantidad = 5): Collection
    {
        return Venta::withCount('detalles')->orderByDesc('fecha_venta')->limit($cantidad)->get();
    }

    /**
     * Ventas agrupadas por día para el gráfico del dashboard.
     *
     * @return array{etiquetas: array<int, string>, totales: array<int, int>, montos: array<int, float>}
     */
    public function ventasUltimosDias(int $dias = 7): array
    {
        $desde = now()->subDays($dias - 1)->startOfDay();

        $filas = Venta::query()
            ->where('fecha_venta', '>=', $desde)
            ->selectRaw('DATE(fecha_venta) as dia, COUNT(*) as total, COALESCE(SUM(monto_total), 0) as monto')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->keyBy(fn ($fila) => (string) $fila->dia);

        $etiquetas = [];
        $totales = [];
        $montos = [];

        for ($i = 0; $i < $dias; $i++) {
            $fecha = now()->subDays($dias - 1 - $i)->startOfDay();
            $clave = $fecha->toDateString();
            $fila = $filas->get($clave);

            $etiquetas[] = $fecha->format('d/m');
            $totales[] = (int) ($fila->total ?? 0);
            $montos[] = (float) ($fila->monto ?? 0);
        }

        return ['etiquetas' => $etiquetas, 'totales' => $totales, 'montos' => $montos];
    }

    /**
     * Top de medicamentos más vendidos (unidades e ingresos).
     */
    public function topMedicamentos(int $limite = 10, ?string $desde = null, ?string $hasta = null): Collection
    {
        return DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.venta_id', '=', 'ventas.id')
            ->join('lotes', 'detalles_venta.lote_id', '=', 'lotes.id')
            ->join('medicamentos', 'lotes.medicamento_id', '=', 'medicamentos.id')
            ->when($desde, fn ($query) => $query->whereDate('ventas.fecha_venta', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('ventas.fecha_venta', '<=', $hasta))
            ->select(
                'medicamentos.nombre_comercial',
                DB::raw('SUM(detalles_venta.cantidad) as unidades'),
                DB::raw('SUM(detalles_venta.cantidad * detalles_venta.precio_unitario) as ingresos')
            )
            ->groupBy('medicamentos.id', 'medicamentos.nombre_comercial')
            ->orderByDesc('unidades')
            ->limit($limite)
            ->get();
    }

    /**
     * Ingresos por categoría terapéutica.
     */
    public function ingresosPorCategoria(?string $desde = null, ?string $hasta = null): Collection
    {
        return DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.venta_id', '=', 'ventas.id')
            ->join('lotes', 'detalles_venta.lote_id', '=', 'lotes.id')
            ->join('medicamentos', 'lotes.medicamento_id', '=', 'medicamentos.id')
            ->join('categorias', 'medicamentos.categoria_id', '=', 'categorias.id')
            ->when($desde, fn ($query) => $query->whereDate('ventas.fecha_venta', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('ventas.fecha_venta', '<=', $hasta))
            ->select(
                'categorias.nombre',
                DB::raw('SUM(detalles_venta.cantidad) as unidades'),
                DB::raw('SUM(detalles_venta.cantidad * detalles_venta.precio_unitario) as ingresos')
            )
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderByDesc('ingresos')
            ->get();
    }

    /**
     * Cantidad de ventas e ingresos por método de pago (efectivo, tarjeta, QR).
     */
    public function ventasPorMetodoPago(?string $desde = null, ?string $hasta = null): Collection
    {
        $filas = Venta::query()
            ->when($desde, fn ($query) => $query->whereDate('fecha_venta', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('fecha_venta', '<=', $hasta))
            ->selectRaw('metodo_pago, COUNT(*) as ventas, COALESCE(SUM(monto_total), 0) as ingresos')
            ->groupBy('metodo_pago')
            ->get()
            ->keyBy('metodo_pago');

        return collect(Venta::METODOS_PAGO)->map(fn (string $etiqueta, string $clave) => (object) [
            'metodo' => $clave,
            'etiqueta' => $etiqueta,
            'ventas' => (int) ($filas->get($clave)->ventas ?? 0),
            'ingresos' => (float) ($filas->get($clave)->ingresos ?? 0),
        ])->values();
    }

    /**
     * Ventas agrupadas por mes (últimos N meses).
     */
    public function ventasPorMes(int $meses = 12): Collection
    {
        $desde = now()->subMonths($meses - 1)->startOfMonth();

        $filas = Venta::query()
            ->where('fecha_venta', '>=', $desde)
            ->selectRaw("to_char(fecha_venta, 'YYYY-MM') as periodo, COUNT(*) as total, COALESCE(SUM(monto_total), 0) as monto")
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get()
            ->keyBy('periodo');

        $resultado = collect();

        for ($i = 0; $i < $meses; $i++) {
            $fecha = now()->subMonths($meses - 1 - $i)->startOfMonth();
            $periodo = $fecha->format('Y-m');
            $fila = $filas->get($periodo);

            $resultado->push((object) [
                'periodo' => $periodo,
                'etiqueta' => $fecha->translatedFormat('M Y'),
                'total' => (int) ($fila->total ?? 0),
                'monto' => (float) ($fila->monto ?? 0),
            ]);
        }

        return $resultado;
    }

    /**
     * Ticket promedio por venta.
     */
    public function ticketPromedio(): float
    {
        return (float) Venta::avg('monto_total');
    }

    /**
     * Medicamentos con stock vigente por debajo del mínimo.
     */
    public function stockBajo(): Collection
    {
        return Medicamento::query()
            ->with(['categoria:id,nombre', 'laboratorio:id,nombre'])
            ->withSum(['lotes as stock_vigente' => fn ($query) => $query->where('fecha_vencimiento', '>=', now()->toDateString())], 'cantidad_stock')
            ->get()
            ->filter(fn (Medicamento $medicamento) => (int) ($medicamento->stock_vigente ?? 0) < $medicamento->stock_minimo)
            ->sortBy(fn (Medicamento $medicamento) => (int) ($medicamento->stock_vigente ?? 0) - $medicamento->stock_minimo)
            ->values();
    }
}
