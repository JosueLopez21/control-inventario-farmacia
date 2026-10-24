<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(Request $request, ReporteService $reporte): View
    {
        $desde = $request->query('fecha_inicio');
        $hasta = $request->query('fecha_fin');

        return view('reportes.index', [
            'ventasPorMes' => $reporte->ventasPorMes(12),
            'ingresosPorCategoria' => $reporte->ingresosPorCategoria($desde, $hasta),
            'ventasPorMetodoPago' => $reporte->ventasPorMetodoPago($desde, $hasta),
            'ticketPromedio' => $reporte->ticketPromedio(),
            'stockBajo' => $reporte->stockBajo(),
            'topMedicamentos' => $reporte->topMedicamentos(10, $desde, $hasta),
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }
}
