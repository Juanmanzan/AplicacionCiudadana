<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    protected $primaryKey = 'cedula';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cedula',
        'nombres',
        'apellidos',
        'genero',
        'correo_electronico', 
        'usuario',
        'password',
        'admin',          
        'edad',
        'numero_celular',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'admin' => 'boolean',
    ];

    public function contactos(): HasMany
    {
        return $this->hasMany(Contacto::class, 'cedula_usuario', 'cedula');
    }

    public function padecimientos(): HasMany
    {
        return $this->hasMany(Padecimiento::class, 'cedula_usuario', 'cedula');
    }

    public function mensajesPredefinidos(): HasMany
    {
        return $this->hasMany(MensajePredefinido::class, 'cedula_usuario', 'cedula');
    }

    public function emergencias(): HasMany
    {
        return $this->hasMany(Emergencia::class, 'cedula_usuario', 'cedula');
    }
}
