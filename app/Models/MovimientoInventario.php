<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    public const TIPOS = [
        'venta' => 'Venta',
        'ingreso' => 'Ingreso de lote',
        'ajuste' => 'Ajuste de stock',
    ];

    protected $fillable = [
        'lote_id',
        'medicamento_id',
        'venta_id',
        'user_id',
        'tipo',
        'medicamento_nombre',
        'numero_lote',
        'cantidad',
        'stock_antes',
        'stock_despues',
        'motivo',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_antes' => 'integer',
        'stock_despues' => 'integer',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'medicamento_id');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTipoEtiquetaAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst((string) $this->tipo);
    }

    /**
     * Registra un movimiento de un lote. $cantidad es negativa si el stock baja.
     */
    public static function registrar(Lote $lote, string $tipo, int $cantidad, int $stockAntes, ?Venta $venta = null, ?string $motivo = null): self
    {
        $lote->loadMissing('medicamento:id,nombre_comercial');

        return self::create([
            'lote_id' => $lote->id,
            'medicamento_id' => $lote->medicamento_id,
            'venta_id' => $venta?->id,
            'user_id' => auth()->id(),
            'tipo' => $tipo,
            'medicamento_nombre' => $lote->medicamento?->nombre_comercial ?? 'Medicamento',
            'numero_lote' => $lote->numero_lote,
            'cantidad' => $cantidad,
            'stock_antes' => $stockAntes,
            'stock_despues' => $stockAntes + $cantidad,
            'motivo' => $motivo,
        ]);
    }
}
