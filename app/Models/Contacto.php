<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contacto extends Model
{
    protected $table = 'contactos';
    protected $primaryKey = 'id_contacto';

    protected $fillable = [
        'cedula_usuario',
        'nombres',
        'apellidos',
        'numero_celular',
        'correo_electronico',
        'parentesco',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cedula_usuario', 'cedula');
    }
}
