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
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('restrict');
            $table->foreignId('laboratorio_id')->constrained('laboratorios')->onDelete('restrict');
            $table->string('nombre_comercial');
            $table->string('principio_activo');
            $table->string('presentacion');
            $table->decimal('precio_venta', 10, 2);
            $table->integer('stock_minimo')->default(10);
            $table->timestamps();

            $table->index('nombre_comercial');
            $table->index('principio_activo');
            $table->index(['categoria_id', 'laboratorio_id']);
        });

        // Restricciones CHECK en PostgreSQL
        DB::statement('ALTER TABLE medicamentos ADD CONSTRAINT chk_precio_venta_positivo CHECK (precio_venta > 0)');
        DB::statement('ALTER TABLE medicamentos ADD CONSTRAINT chk_stock_minimo_no_negativo CHECK (stock_minimo >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicamentos');
    }
};
