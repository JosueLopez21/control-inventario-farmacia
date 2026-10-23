<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Services\ReporteService;
use Illuminate\View\View;

class BienvenidaController extends Controller
{
    public function index(ReporteService $reporte): View
    {
        return view('bienvenida', [
            'totalMedicamentos' => $reporte->totalMedicamentos(),
            'totalCategorias' => Categoria::count(),
            'totalLaboratorios' => Laboratorio::count(),
            'totalLotes' => Lote::count(),
            'lotesPorVencer' => $reporte->lotesPorVencer(30),
            'demoAccess' => (bool) config('feria.demo_access'),
        ]);
    }
}
