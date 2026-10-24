<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoteRequest;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoteController extends Controller
{
    public function index(Request $request): View
    {
        $estado = (string) $request->query('estado');
        $medicamentoId = $request->query('medicamento_id');
        $q = trim((string) $request->query('q'));
        $hoy = now()->toDateString();
        $limite = now()->addDays(30)->toDateString();

        $lotes = Lote::query()
            ->with('medicamento:id,nombre_comercial')
            ->when($q !== '', fn ($query) => $query->where('numero_lote', 'ilike', "%{$q}%"))
            ->when($medicamentoId, fn ($query) => $query->where('medicamento_id', $medicamentoId))
            ->when($estado === 'vencido', fn ($query) => $query->where('fecha_vencimiento', '<', $hoy))
            ->when($estado === 'por_vencer', fn ($query) => $query->whereBetween('fecha_vencimiento', [$hoy, $limite])->where('cantidad_stock', '>', 0))
            ->when($estado === 'agotado', fn ($query) => $query->where('cantidad_stock', '<=', 0))
            ->when($estado === 'vigente', fn ($query) => $query->where('fecha_vencimiento', '>', $limite)->where('cantidad_stock', '>', 0))
            ->bolivianosPrimero()
            ->orderBy('fecha_vencimiento')
            ->paginate(15)
            ->withQueryString();

        $medicamentos = Medicamento::bolivianosPrimero()->orderBy('nombre_comercial')->get(['id', 'nombre_comercial']);

        return view('lotes.index', compact('lotes', 'medicamentos', 'estado', 'medicamentoId', 'q'));
    }

    public function create(Request $request): View
    {
        $medicamentoSeleccionado = (int) old('medicamento_id', $request->integer('medicamento_id')) ?: null;
        $medicamentoInicial = $medicamentoSeleccionado
            ? Medicamento::with('laboratorio:id,nombre')->find($medicamentoSeleccionado, [
                'id', 'laboratorio_id', 'nombre_comercial', 'principio_activo', 'presentacion',
            ])
            : null;

        return view('lotes.create', [
            'medicamentos' => Medicamento::bolivianosPrimero()->orderBy('nombre_comercial')->get(['id', 'nombre_comercial']),
            'medicamentoSeleccionado' => $medicamentoSeleccionado,
            'medicamentoInicial' => $medicamentoInicial,
        ]);
    }

    public function store(LoteRequest $request): RedirectResponse
    {
        $lote = Lote::create($request->validated());
        $lote->loadMissing('medicamento:id,nombre_comercial');

        if ($lote->cantidad_stock > 0) {
            MovimientoInventario::registrar($lote, 'ingreso', (int) $lote->cantidad_stock, 0, null, 'Ingreso de lote');
        }

        return redirect()
            ->route('lotes.index')
            ->with('exito', 'Lote registrado correctamente.')
            ->with('animacion', 'lote')
            ->with('animacion_datos', [
                'numero_lote' => $lote->numero_lote,
                'medicamento' => $lote->medicamento?->nombre_comercial ?? 'Medicamento',
                'ver_lotes' => route('lotes.index'),
                'otro' => route('lotes.create'),
            ]);
    }

    public function edit(Lote $lote): View
    {
        return view('lotes.edit', [
            'lote' => $lote,
            'medicamentos' => Medicamento::bolivianosPrimero()->orderBy('nombre_comercial')->get(['id', 'nombre_comercial']),
        ]);
    }

    public function update(LoteRequest $request, Lote $lote): RedirectResponse
    {
        $stockAntes = (int) $lote->cantidad_stock;
        $lote->update($request->validated());

        $diferencia = (int) $lote->cantidad_stock - $stockAntes;
        if ($diferencia !== 0) {
            MovimientoInventario::registrar($lote, 'ajuste', $diferencia, $stockAntes, null, 'Stock corregido al editar el lote');
        }

        return redirect()->route('lotes.index')->with('exito', 'Lote actualizado correctamente.');
    }

    public function destroy(Lote $lote): RedirectResponse
    {
        $ventas = $lote->detallesVenta()->count();

        if ($ventas > 0) {
            return back()->with('error', "No se puede eliminar el lote \"{$lote->numero_lote}\": ya tiene {$ventas} movimientos de venta.");
        }

        try {
            $lote->delete();
        } catch (QueryException) {
            return back()->with('error', 'No se puede eliminar el lote porque tiene registros asociados.');
        }

        return back()->with('exito', 'Lote eliminado correctamente.');
    }
}
