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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_id')->constrained('medicamentos')->onDelete('restrict');
            $table->string('numero_lote')->unique();
            $table->date('fecha_vencimiento');
            $table->integer('cantidad_stock');
            $table->timestamps();

            $table->index('fecha_vencimiento');
            $table->index(['medicamento_id', 'fecha_vencimiento']);
        });

        // Restricción CHECK en PostgreSQL para no permitir stock negativo
        DB::statement('ALTER TABLE lotes ADD CONSTRAINT chk_cantidad_stock_no_negativo CHECK (cantidad_stock >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
