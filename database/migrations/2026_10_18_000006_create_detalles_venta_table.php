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
        Schema::create('detalles_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('restrict');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->timestamps();

            $table->index(['venta_id', 'lote_id']);
        });

        // Restricciones CHECK en PostgreSQL
        DB::statement('ALTER TABLE detalles_venta ADD CONSTRAINT chk_cantidad_positiva CHECK (cantidad > 0)');
        DB::statement('ALTER TABLE detalles_venta ADD CONSTRAINT chk_precio_unitario_positivo CHECK (precio_unitario > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_venta');
    }
};
