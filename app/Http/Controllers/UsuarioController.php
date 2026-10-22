<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $usuarios = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'ilike', "%{$q}%")
                        ->orWhere('email', 'ilike', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios', 'q'));
    }

    public function create(): View
    {
        return view('usuarios.create');
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['password'] = Hash::make($datos['password']);
        $datos['activo'] = $request->boolean('activo');

        User::create($datos);

        return redirect()->route('usuarios.index')->with('exito', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(UsuarioRequest $request, User $usuario): RedirectResponse
    {
        $datos = $request->validated();
        $datos['activo'] = $request->boolean('activo');

        if (blank($datos['password'] ?? null)) {
            unset($datos['password']);
        } else {
            $datos['password'] = Hash::make($datos['password']);
        }

        // El administrador no puede desactivarse ni quitarse el rol a sí mismo.
        if ($usuario->id === $request->user()->id) {
            $datos['rol'] = User::ROL_ADMINISTRADOR;
            $datos['activo'] = true;
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')->with('exito', 'Usuario actualizado correctamente.');
    }

    public function resetPassword(Request $request, User $usuario): RedirectResponse
    {
        $datos = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $usuario->update(['password' => Hash::make($datos['password'])]);

        return redirect()->route('usuarios.index')->with('exito', "Contraseña de {$usuario->name} restablecida correctamente.");
    }
}
