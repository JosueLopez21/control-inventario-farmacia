<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha_venta')->useCurrent();
            $table->decimal('monto_total', 10, 2)->default(0);
            $table->timestamps();

            $table->index('fecha_venta');
        });

        // Restricción CHECK en PostgreSQL para monto total no negativo
        DB::statement('ALTER TABLE ventas ADD CONSTRAINT chk_monto_total_no_negativo CHECK (monto_total >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
