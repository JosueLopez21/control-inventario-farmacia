<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'rol', 'activo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROL_ADMINISTRADOR = 'administrador';
    public const ROL_FARMACEUTICO = 'farmaceutico';
    public const ROL_DOCTOR = 'doctor';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function esAdministrador(): bool
    {
        return $this->rol === self::ROL_ADMINISTRADOR;
    }

    public function esFarmaceutico(): bool
    {
        return $this->rol === self::ROL_FARMACEUTICO;
    }

    public function esDoctor(): bool
    {
        return $this->rol === self::ROL_DOCTOR;
    }

    /**
     * Puede crear, editar y eliminar registros de inventario.
     */
    public function puedeGestionarInventario(): bool
    {
        return in_array($this->rol, [self::ROL_ADMINISTRADOR, self::ROL_FARMACEUTICO], true);
    }

    /**
     * Puede crear ventas y ver reportes.
     */
    public function puedeOperarVentas(): bool
    {
        return in_array($this->rol, [self::ROL_ADMINISTRADOR, self::ROL_FARMACEUTICO], true);
    }

    public function puedeGestionarUsuarios(): bool
    {
        return $this->esAdministrador();
    }

    /**
     * Etiqueta legible del rol.
     */
    public function getRolEtiquetaAttribute(): string
    {
        return match ($this->rol) {
            self::ROL_ADMINISTRADOR => 'Administrador',
            self::ROL_FARMACEUTICO => 'Farmacéutico',
            self::ROL_DOCTOR => 'Doctor',
            default => ucfirst((string) $this->rol),
        };
    }
}
