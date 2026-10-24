<?php

use Database\Seeders\MedicamentosBolivianosSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Carga el lote de medicamentos bolivianos en bases que ya tienen catálogo
     * (por ejemplo la de Render/Neon). En una base vacía no hace nada: ahí los
     * datos los siembra `php artisan db:seed`.
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
        DB::table('movimientos_inventario')->where('numero_lote', 'like', 'BOL-%')->delete();
        DB::table('lotes')->where('numero_lote', 'like', 'BOL-%')->whereNotExists(
            fn ($q) => $q->select(DB::raw(1))->from('detalles_venta')->whereColumn('detalles_venta.lote_id', 'lotes.id')
        )->delete();
    }
};
