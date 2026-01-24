<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Padecimiento extends Model
{
    protected $table = 'padecimientos';
    protected $primaryKey = 'id_padecimiento';

    protected $fillable = [
        'cedula_usuario',
        'descripcion',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cedula_usuario', 'cedula');
    }
}
