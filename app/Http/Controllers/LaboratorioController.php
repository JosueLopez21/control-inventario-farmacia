<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaboratorioRequest;
use App\Models\Laboratorio;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaboratorioController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $laboratorios = Laboratorio::query()
            ->withCount('medicamentos')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'ilike', "%{$q}%")
                        ->orWhere('pais', 'ilike', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('laboratorios.index', compact('laboratorios', 'q'));
    }

    public function create(): View
    {
        return view('laboratorios.create');
    }

    public function store(LaboratorioRequest $request): RedirectResponse
    {
        Laboratorio::create($request->validated());

        return redirect()->route('laboratorios.index')->with('exito', 'Laboratorio creado correctamente.');
    }

    public function edit(Laboratorio $laboratorio): View
    {
        return view('laboratorios.edit', compact('laboratorio'));
    }

    public function update(LaboratorioRequest $request, Laboratorio $laboratorio): RedirectResponse
    {
        $laboratorio->update($request->validated());

        return redirect()->route('laboratorios.index')->with('exito', 'Laboratorio actualizado correctamente.');
    }

    public function destroy(Laboratorio $laboratorio): RedirectResponse
    {
        $asociados = $laboratorio->medicamentos()->count();

        if ($asociados > 0) {
            return back()->with('error', "No se puede eliminar el laboratorio \"{$laboratorio->nombre}\": tiene {$asociados} medicamentos asociados.");
        }

        try {
            $laboratorio->delete();
        } catch (QueryException) {
            return back()->with('error', 'No se puede eliminar el laboratorio porque tiene registros asociados.');
        }

        return back()->with('exito', 'Laboratorio eliminado correctamente.');
    }
}
