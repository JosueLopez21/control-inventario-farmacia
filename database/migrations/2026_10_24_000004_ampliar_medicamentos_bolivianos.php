<?php

use Database\Seeders\MedicamentosBolivianosSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Completa el catálogo boliviano en bases donde ya se había cargado la primera tanda.
     * El seeder es idempotente: solo agrega lo que falta.
     */
    public function up(): void
    {
        if (DB::table('categorias')->doesntExist()) {
            return;
        }

        (new MedicamentosBolivianosSeeder)->run();
    }

    public function down(): void
    {
        // Los datos cargados se conservan.
    }
};
