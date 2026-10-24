<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use App\Services\SimuladorVentaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function index(Request $request, ReporteService $reporte): View
    {
        $usuario = $request->user();

        $stockBajo = $reporte->stockBajo();

        $datos = [
            'totalMedicamentos' => $reporte->totalMedicamentos(),
            'lotesPorVencer' => $reporte->lotesPorVencer(30),
            'unidadesPorVencer' => $reporte->unidadesPorVencer(30),
            'lotesVencidos' => $reporte->lotesVencidos(),
            'unidadesVencidas' => $reporte->unidadesVencidas(),
            'stockBajo' => $stockBajo,
            'deficitStockBajo' => $reporte->deficitStockBajo($stockBajo),
            'puedeVerVentas' => $usuario->puedeOperarVentas(),
        ];

        if ($usuario->puedeOperarVentas()) {
            $datos += [
                'ventasHoy' => $reporte->ventasHoy(),
                'ingresosHoy' => $reporte->ingresosHoy(),
                'ticketPromedio' => $reporte->ticketPromedio(),
                'grafico' => $reporte->ventasUltimosDias(7),
                'topMedicamentos' => $reporte->topMedicamentos(5),
                'ultimasVentas' => $reporte->ultimasVentas(5),
            ];
        }

        return view('dashboard.index', $datos);
    }

    public function simular(ReporteService $reporte, SimuladorVentaService $simulador): JsonResponse
    {
        try {
            $venta = $simulador->crear();
        } catch (Throwable $excepcion) {
            return response()->json([
                'ok' => false,
                'message' => $excepcion->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Venta #' . $venta->id . ' registrada. Se descontó primero el lote que vence antes.',
            'venta' => [
                'id' => $venta->id,
                'monto_total' => (float) $venta->monto_total,
                'fecha' => $venta->fecha_venta->format('d/m/Y H:i'),
                'detalles' => $venta->detalles->map(fn ($detalle) => [
                    'medicamento' => $detalle->lote->medicamento->nombre_comercial,
                    'lote' => $detalle->lote->numero_lote,
                    'cantidad' => $detalle->cantidad,
                    'fecha_vencimiento' => $detalle->lote->fecha_vencimiento->format('d/m/Y'),
                    'precio_unitario' => (float) $detalle->precio_unitario,
                ])->values(),
            ],
            'metricas' => [
                'ventasHoy' => $reporte->ventasHoy(),
                'ingresosHoy' => $reporte->ingresosHoy(),
                'ticketPromedio' => $reporte->ticketPromedio(),
                'totalMedicamentos' => $reporte->totalMedicamentos(),
                'lotesPorVencer' => $reporte->lotesPorVencer(30),
                'lotesVencidos' => $reporte->lotesVencidos(),
                'stockBajo' => $reporte->stockBajo()->count(),
            ],
            'grafico' => $reporte->ventasUltimosDias(7),
        ]);
    }
}
