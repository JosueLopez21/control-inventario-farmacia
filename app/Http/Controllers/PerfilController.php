<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function editPassword(): View
    {
        return view('perfil.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password_actual' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password_actual.required' => 'Debes ingresar tu contraseña actual.',
            'password.required' => 'Debes ingresar la nueva contraseña.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        if (! Hash::check($data['password_actual'], $request->user()->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $request->user()->update(['password' => $data['password']]);

        return back()->with('exito', 'Contraseña actualizada correctamente.');
    }
}
