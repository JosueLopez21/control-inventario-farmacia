<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoController extends Controller
{
    public function entrar(Request $request, string $rol): RedirectResponse
    {
        abort_unless(config('feria.demo_access'), 404);
        abort_unless(in_array($rol, ['farmaceutico', 'doctor'], true), 404);

        $usuario = User::query()
            ->where('rol', $rol)
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (! $usuario) {
            return redirect()
                ->route('bienvenida')
                ->with('error', 'No hay un usuario demo activo para ese rol. Ejecuta el seeder de usuarios.');
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('exito', 'Modo demo activo: '.$usuario->rol_etiqueta.'. Explora sin miedo.');
    }
}
