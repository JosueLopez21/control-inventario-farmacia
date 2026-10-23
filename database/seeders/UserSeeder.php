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
        $usuarios = [
            [
                'name' => 'Nahuel Martínez',
                'email' => 'admin@farmacia.test',
                'password' => env('SEED_ADMIN_PASSWORD') ?: '1234',
                'rol' => 'administrador',
                'activo' => true,
            ],
            [
                'name' => 'Lic. Carla Rojas',
                'email' => 'farmaceutica@farmacia.test',
                'password' => env('SEED_FARMACEUTICO_PASSWORD') ?: 'password',
                'rol' => 'farmaceutico',
                'activo' => true,
            ],
            [
                'name' => 'Dr. Andrés Vargas',
                'email' => 'doctor@farmacia.test',
                'password' => env('SEED_DOCTOR_PASSWORD') ?: 'password',
                'rol' => 'doctor',
                'activo' => true,
            ],
        ];

        foreach ($usuarios as $usuario) {
            User::updateOrCreate(
                ['email' => $usuario['email']],
                [
                    'name' => $usuario['name'],
                    'password' => Hash::make($usuario['password']),
                    'rol' => $usuario['rol'],
                    'activo' => $usuario['activo'],
                ]
            );
        }

        $this->command->info('👥 Usuarios creados: '.User::count());
    }
}
