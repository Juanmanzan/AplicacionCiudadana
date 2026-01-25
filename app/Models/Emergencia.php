<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Clickbar\Magellan\Data\Geometries\Point;

class Emergencia extends Model
{
    protected $table = 'emergencia';
    protected $primaryKey = 'id_emergencia';
    public $timestamps = true;

    protected $fillable = [
        'cedula_usuario',
        'id_tipo_emergencia',
        'descripcion',
        'ubicacion',
        'fecha_hora',
        'estado',
        'cancelable_hasta',
        'confirmada_en',
    ];


    protected $casts = [
        
        'ubicacion' => Point::class,
        'fecha_hora' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cedula_usuario', 'cedula');
    }

    public function tipoEmergencia(): BelongsTo
    {
        return $this->belongsTo(TipoEmergencia::class, 'id_tipo_emergencia', 'id_tipo_emergencia');
    }

    public const ESTADO_EN_VERIFICACION = 'EN_VERIFICACION';
    public const ESTADO_CONFIRMADA = 'CONFIRMADA';
    public const ESTADO_ATENDIDA = 'ATENDIDA';
    public const ESTADO_FALSA_ALARMA = 'FALSA_ALARMA';
}
