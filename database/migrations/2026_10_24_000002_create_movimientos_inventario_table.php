<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();
            $table->foreignId('medicamento_id')->nullable()->constrained('medicamentos')->nullOnDelete();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 20);
            // Copias de texto: el historial se conserva aunque se borre el lote o el medicamento.
            $table->string('medicamento_nombre');
            $table->string('numero_lote');
            $table->integer('cantidad');
            $table->integer('stock_antes');
            $table->integer('stock_despues');
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['lote_id', 'created_at']);
            $table->index(['medicamento_id', 'created_at']);
            $table->index('venta_id');
        });

        DB::statement("ALTER TABLE movimientos_inventario ADD CONSTRAINT chk_mov_tipo CHECK (tipo IN ('venta', 'ingreso', 'ajuste'))");
        DB::statement('ALTER TABLE movimientos_inventario ADD CONSTRAINT chk_mov_cantidad_distinta_cero CHECK (cantidad <> 0)');
        DB::statement('ALTER TABLE movimientos_inventario ADD CONSTRAINT chk_mov_stock_no_negativo CHECK (stock_antes >= 0 AND stock_despues >= 0)');

        // Historial de las ventas ya registradas: se reconstruye desde el stock actual de cada lote.
        DB::statement(<<<'SQL'
            INSERT INTO movimientos_inventario
                (lote_id, medicamento_id, venta_id, tipo, medicamento_nombre, numero_lote,
                 cantidad, stock_antes, stock_despues, motivo, created_at, updated_at)
            SELECT
                d.lote_id,
                l.medicamento_id,
                d.venta_id,
                'venta',
                m.nombre_comercial,
                l.numero_lote,
                -d.cantidad,
                l.cantidad_stock + COALESCE(SUM(d.cantidad) OVER (
                    PARTITION BY d.lote_id ORDER BY d.id
                    ROWS BETWEEN CURRENT ROW AND UNBOUNDED FOLLOWING), 0),
                l.cantidad_stock + COALESCE(SUM(d.cantidad) OVER (
                    PARTITION BY d.lote_id ORDER BY d.id
                    ROWS BETWEEN 1 FOLLOWING AND UNBOUNDED FOLLOWING), 0),
                'Venta anterior (historial reconstruido)',
                v.fecha_venta,
                v.fecha_venta
            FROM detalles_venta d
            JOIN lotes l ON l.id = d.lote_id
            JOIN medicamentos m ON m.id = l.medicamento_id
            JOIN ventas v ON v.id = d.venta_id
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
