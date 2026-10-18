<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Una categoría tiene muchos medicamentos.
     */
    public function medicamentos(): HasMany
    {
        return $this->hasMany(Medicamento::class, 'categoria_id');
    }

    /**
     * Obtener todos los lotes de medicamentos a través de medicamentos.
     */
    public function lotes(): HasManyThrough
    {
        return $this->hasManyThrough(Lote::class, Medicamento::class, 'categoria_id', 'medicamento_id');
    }
}
