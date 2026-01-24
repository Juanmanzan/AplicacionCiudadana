<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajePredefinido extends Model
{
    protected $table = 'mensajes_predefinidos';
    protected $primaryKey = 'id_mensaje_predefinido';

    protected $fillable = [
        'cedula_usuario',
        'id_tipo_emergencia',
        'mensaje',
        'combinacion_botones',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cedula_usuario', 'cedula');
    }

    public function tipoEmergencia(): BelongsTo
    {
        return $this->belongsTo(TipoEmergencia::class, 'id_tipo_emergencia', 'id_tipo_emergencia');
    }
}
