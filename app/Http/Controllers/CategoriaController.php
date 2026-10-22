<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $categorias = Categoria::query()
            ->withCount('medicamentos')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'ilike', "%{$q}%")
                        ->orWhere('descripcion', 'ilike', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('categorias.index', compact('categorias', 'q'));
    }

    public function create(): View
    {
        return view('categorias.create');
    }

    public function store(CategoriaRequest $request): RedirectResponse
    {
        Categoria::create($request->validated());

        return redirect()->route('categorias.index')->with('exito', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria): View
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(CategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return redirect()->route('categorias.index')->with('exito', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $asociados = $categoria->medicamentos()->count();

        if ($asociados > 0) {
            return back()->with('error', "No se puede eliminar la categoría \"{$categoria->nombre}\": tiene {$asociados} medicamentos asociados.");
        }

        try {
            $categoria->delete();
        } catch (QueryException) {
            return back()->with('error', 'No se puede eliminar la categoría porque tiene registros asociados.');
        }

        return back()->with('exito', 'Categoría eliminada correctamente.');
    }
}
