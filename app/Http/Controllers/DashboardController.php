<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, ReporteService $reporte): View
    {
        $usuario = $request->user();

        $datos = [
            'totalMedicamentos' => $reporte->totalMedicamentos(),
            'lotesPorVencer' => $reporte->lotesPorVencer(30),
            'lotesVencidos' => $reporte->lotesVencidos(),
            'stockBajo' => $reporte->stockBajo(),
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
}
