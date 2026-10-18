<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'fecha_venta',
        'monto_total',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'monto_total' => 'decimal:2',
    ];

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
