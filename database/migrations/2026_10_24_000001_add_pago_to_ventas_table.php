<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('metodo_pago', 20)->default('efectivo')->after('monto_total');
            $table->decimal('monto_recibido', 10, 2)->nullable()->after('metodo_pago');
            $table->decimal('cambio', 10, 2)->default(0)->after('monto_recibido');
        });

        // Las ventas anteriores se consideran pagadas en efectivo por el monto exacto.
        DB::statement('UPDATE ventas SET monto_recibido = monto_total WHERE monto_recibido IS NULL');

        DB::statement("ALTER TABLE ventas ADD CONSTRAINT chk_ventas_metodo_pago CHECK (metodo_pago IN ('efectivo', 'tarjeta', 'qr'))");
        DB::statement('ALTER TABLE ventas ADD CONSTRAINT chk_ventas_cambio_no_negativo CHECK (cambio >= 0)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ventas DROP CONSTRAINT IF EXISTS chk_ventas_cambio_no_negativo');
        DB::statement('ALTER TABLE ventas DROP CONSTRAINT IF EXISTS chk_ventas_metodo_pago');

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago', 'monto_recibido', 'cambio']);
        });
    }
};
