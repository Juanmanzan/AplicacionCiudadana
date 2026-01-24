<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoEmergencia extends Model
{
    protected $table = 'tipo_emergencia';
    protected $primaryKey = 'id_tipo_emergencia';

    protected $fillable = [
        'tipo',
    ];

    public function mensajesPredefinidos(): HasMany
    {
        return $this->hasMany(MensajePredefinido::class, 'id_tipo_emergencia', 'id_tipo_emergencia');
    }

    public function emergencias(): HasMany
    {
        return $this->hasMany(Emergencia::class, 'id_tipo_emergencia', 'id_tipo_emergencia');
    }
}
