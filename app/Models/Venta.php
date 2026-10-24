<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;

    /**
     * Métodos de pago admitidos (clave => etiqueta).
     */
    public const METODOS_PAGO = [
        'efectivo' => 'Efectivo',
        'tarjeta' => 'Tarjeta',
        'qr' => 'QR / Transferencia',
    ];

    protected $table = 'ventas';

    protected $fillable = [
        'fecha_venta',
        'monto_total',
        'metodo_pago',
        'monto_recibido',
        'cambio',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'monto_total' => 'decimal:2',
        'monto_recibido' => 'decimal:2',
        'cambio' => 'decimal:2',
    ];

    /**
     * Resumen de stock antes y después de la venta, por medicamento.
     * Solo se llena al registrar la venta (no se guarda en la base).
     *
     * @var array<int, array{medicamento: string, cantidad: int, antes: int, despues: int, lotes: array}>
     */
    public array $resumenStock = [];

    /**
     * Etiqueta legible del método de pago.
     */
    public function getMetodoPagoEtiquetaAttribute(): string
    {
        return self::METODOS_PAGO[$this->metodo_pago] ?? ucfirst((string) $this->metodo_pago);
    }

    /**
     * Una venta tiene muchos detalles de venta.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    /**
     * Recalcular el monto total sumando los subtotales de sus detalles.
     */
    public function recalcularTotal(): void
    {
        $this->update([
            'monto_total' => $this->detalles->sum(fn ($d) => $d->cantidad * $d->precio_unitario),
        ]);
    }
}
