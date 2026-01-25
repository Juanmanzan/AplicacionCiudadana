<?php

namespace App\Events;

use App\Models\Emergencia;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // o ShouldBroadcast
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class EmergenciaCreada implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Emergencia $emergencia) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('emergencias.admin');
    }

    public function broadcastAs(): string
    {
        return 'emergencia.creada';
    }

    public function broadcastWith(): array
    {
        
        $coords = DB::table('emergencia')
            ->selectRaw('ST_Y(ubicacion::geometry) AS lat, ST_X(ubicacion::geometry) AS lng')
            ->where('id_emergencia', $this->emergencia->id_emergencia)
            ->first();

        return [
            'id_emergencia' => $this->emergencia->id_emergencia,
            'id_tipo_emergencia' => $this->emergencia->id_tipo_emergencia,
            'descripcion' => $this->emergencia->descripcion,
            'estado' => $this->emergencia->estado,
            'lat' => $coords?->lat,
            'lng' => $coords?->lng,
        ];
    }
}