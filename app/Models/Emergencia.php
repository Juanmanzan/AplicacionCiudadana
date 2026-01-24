<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Ajusta el import según el paquete Magellan que estés usando
use Clickbar\Magellan\Data\Geometries\Point;

class Emergencia extends Model
{
    protected $table = 'emergencia';
    protected $primaryKey = 'id_emergencia';

    protected $fillable = [
        'cedula_usuario',
        'id_tipo_emergencia',
        'descripcion',
        'ubicacion',
        'fecha_hora',
        'estado',
    ];

    protected $casts = [
        // Magellan: convierte a objeto Point automáticamente
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
}
