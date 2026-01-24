<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Llamada extends Model
{
    protected $table = 'llamadas';
    protected $primaryKey = 'id_llamada';

    protected $fillable = [
        'id_emergencia',
        'id_contacto',
        'medio',
        'mensaje_enviado',
        'estado',
        'fecha_hora_envio',
    ];

    protected $casts = [
        'fecha_hora_envio' => 'datetime',
    ];

    public function emergencia(): BelongsTo
    {
        return $this->belongsTo(Emergencia::class, 'id_emergencia', 'id_emergencia');
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Contacto::class, 'id_contacto', 'id_contacto');
    }
}
