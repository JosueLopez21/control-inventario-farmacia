<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Medicamento extends Model
{
    use HasFactory;

    protected $table = 'medicamentos';

    protected $fillable = [
        'categoria_id',
        'laboratorio_id',
        'nombre_comercial',
        'principio_activo',
        'presentacion',
        'precio_venta',
        'stock_minimo',
    ];

    protected $casts = [
        'precio_venta' => 'decimal:2',
        'stock_minimo' => 'integer',
    ];

    /**
     * Un medicamento pertenece a una categoría.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Un medicamento pertenece a un laboratorio fabricante.
     */
    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class, 'laboratorio_id');
    }

    /**
     * Un medicamento tiene muchos lotes.
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'medicamento_id');
    }

    /**
     * Obtener los detalles de venta a través de los lotes.
     */
    public function detallesVenta(): HasManyThrough
    {
        return $this->hasManyThrough(DetalleVenta::class, Lote::class, 'medicamento_id', 'lote_id');
    }

    /**
     * Calcular el stock total disponible sumando todos los lotes vigentes.
     */
    public function getStockTotalAttribute(): int
    {
        return (int) $this->lotes()
            ->where('fecha_vencimiento', '>=', now()->toDateString())
            ->sum('cantidad_stock');
    }

    /**
     * Scope: Medicamentos cuyo stock total vigente está por debajo del mínimo.
     * Regla de negocio #2.
     */
    public function scopeBajoStockMinimo(Builder $query): Builder
    {
        return $query->withSum(['lotes as stock_vigente' => function ($q) {
            $q->where('fecha_vencimiento', '>=', now()->toDateString());
        }], 'cantidad_stock')
        ->havingRaw('COALESCE(stock_vigente, 0) < stock_minimo');
    }

    /**
     * Scope: Medicamentos agotados (sin stock vigente).
     */
    public function scopeAgotados(Builder $query): Builder
    {
        return $query->whereDoesntHave('lotes', function ($q) {
            $q->where('fecha_vencimiento', '>=', now()->toDateString())
              ->where('cantidad_stock', '>', 0);
        });
    }
}
