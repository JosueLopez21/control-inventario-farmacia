<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratorio extends Model
{
    use HasFactory;

    protected $table = 'laboratorios';

    protected $fillable = [
        'nombre',
        'pais',
        'direccion',
        'telefono',
        'email',
    ];

    /**
     * Un laboratorio fabrica muchos medicamentos.
     */
    public function medicamentos(): HasMany
    {
        return $this->hasMany(Medicamento::class, 'laboratorio_id');
    }
}
