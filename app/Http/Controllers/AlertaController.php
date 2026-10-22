<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Services\ReporteService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;

class AlertaController extends Controller
{
    public function index(ReporteService $reporte): View
    {
        $porVencer = Lote::porVencer(30)
            ->with('medicamento:id,nombre_comercial')
            ->orderBy('fecha_vencimiento')
            ->paginate(10, ['*'], 'page_por_vencer')
            ->withQueryString();

        $vencidos = Lote::vencidos()
            ->with('medicamento:id,nombre_comercial')
            ->orderByDesc('fecha_vencimiento')
            ->paginate(10, ['*'], 'page_vencidos')
            ->withQueryString();

        $stockBajoTodos = $reporte->stockBajo();
        $pagina = Paginator::resolveCurrentPage('page_stock');

        $stockBajo = new LengthAwarePaginator(
            $stockBajoTodos->forPage($pagina, 10)->values(),
            $stockBajoTodos->count(),
            10,
            $pagina,
            ['path' => route('alertas.index'), 'pageName' => 'page_stock']
        );

        $stockBajo->withQueryString();

        return view('alertas.index', [
            'porVencer' => $porVencer,
            'vencidos' => $vencidos,
            'stockBajo' => $stockBajo,
            'totalPorVencer' => Lote::porVencer(30)->count(),
            'totalVencidos' => Lote::vencidos()->count(),
            'totalStockBajo' => $stockBajoTodos->count(),
        ]);
    }
}
