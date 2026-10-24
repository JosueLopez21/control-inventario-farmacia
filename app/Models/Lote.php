<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'medicamento_id',
        'numero_lote',
        'fecha_vencimiento',
        'cantidad_stock',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'cantidad_stock' => 'integer',
    ];

    /**
     * Un lote pertenece a un medicamento.
     */
    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'medicamento_id');
    }

    /**
     * Un lote puede tener muchos detalles de venta.
     */
    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'lote_id');
    }

    /**
     * Scope: Lotes que vencen en los próximos N días (por defecto 30).
     * Regla de negocio obligatoria #1.
     */
    public function scopePorVencer(Builder $query, int $dias = 30): Builder
    {
        return $query->where('fecha_vencimiento', '>=', now()->toDateString())
                     ->where('fecha_vencimiento', '<=', now()->addDays($dias)->toDateString())
                     ->where('cantidad_stock', '>', 0);
    }

    /**
     * Scope: Lotes ya vencidos (fecha anterior a hoy).
     * Regla de negocio obligatoria #3: Bloquear venta de vencidos.
     */
    public function scopeVencidos(Builder $query): Builder
    {
        return $query->where('fecha_vencimiento', '<', now()->toDateString());
    }

    /**
     * Scope: Lotes vigentes disponibles (no vencidos con stock > 0).
     */
    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('fecha_vencimiento', '>=', now()->toDateString())
                     ->where('cantidad_stock', '>', 0);
    }

    /**
     * Scope: ordena primero los lotes de medicamentos de laboratorios bolivianos.
     */
    public function scopeBolivianosPrimero(Builder $query): Builder
    {
        return $query->orderByRaw(
            "CASE WHEN EXISTS (SELECT 1 FROM medicamentos m JOIN laboratorios l ON l.id = m.laboratorio_id WHERE m.id = lotes.medicamento_id AND l.pais = 'Bolivia') THEN 0 ELSE 1 END"
        );
    }

    /**
     * Scope: Ordenamiento FEFO (primero en vencer, primero en salir).
     */
    public function scopeFefo(Builder $query): Builder
    {
        return $query->orderBy('fecha_vencimiento', 'asc');
    }

    /**
     * Comprobar si el lote está vencido.
     */
    public function getEstaVencidoAttribute(): bool
    {
        return $this->fecha_vencimiento->lt(now()->startOfDay());
    }

    /**
     * Comprobar si el lote está por vencer en los próximos 30 días.
     */
    public function getEstaPorVencerAttribute(): bool
    {
        return ! $this->esta_vencido
            && $this->fecha_vencimiento->lte(now()->addDays(30)->startOfDay());
    }
}
