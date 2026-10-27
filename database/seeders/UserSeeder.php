<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Crea los usuarios base del sistema.
     */
    public function run(): void
    {
        $usuarios = config('feria.usuarios');

        foreach ($usuarios as $usuario) {
            User::updateOrCreate(
                ['email' => $usuario['email']],
                [
                    'name' => $usuario['name'],
                    'password' => Hash::make($usuario['password']),
                    'rol' => $usuario['rol'],
                    'activo' => true,
                ]
            );
        }

        $this->command->info('👥 Usuarios creados: '.User::count());
    }
}
