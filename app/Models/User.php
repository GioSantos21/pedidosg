<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use App\Models\Branch; // Aseguramos la importación de Branch

class User extends Authenticatable implements MustVerifyEmail
{
    // Quitamos 'HasApiTokens' y el type-hint 'array' de $casts para compatibilidad con Laravel 11+
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * NOTA: Se ha quitado el 'array' type hint para evitar FatalError en Laravel 10/11
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ===============================================
    // RELACIONES
    // ===============================================

    /**
     * Relación con la sucursal a la que pertenece el usuario (Gerente).
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // ===============================================
    // LÓGICA DE ROLES (Implementación de hasRole())
    // ===============================================

    /**
     * Verifica si el usuario tiene el rol dado o si está en la lista de roles.
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole(string|array $roles): bool
    {
        // Si se pasa un solo rol como string, lo convertimos a un array para simplificar la lógica.
        if (is_string($roles)) {
            $roles = [$roles];
        }

        // El usuario tiene el rol si el valor de su campo 'role' está en el array $roles.
        return in_array($this->role, $roles);
    }
}
