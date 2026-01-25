<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Emergencia;
use App\Events\EmergenciaActualizada;

class ConfirmarEmergenciasVencidas extends Command
{
    protected $signature = 'emergencias:confirmar-vencidas';
    protected $description = 'Confirma emergencias EN_VERIFICACION cuyo tiempo cancelable ya venció';

    public function handle(): int
    {
        $now = now();

        $vencidas = Emergencia::query()
            ->where('estado', Emergencia::ESTADO_EN_VERIFICACION)
            ->whereNotNull('cancelable_hasta')
            ->where('cancelable_hasta', '<=', $now)
            ->get();

        foreach ($vencidas as $e) {
            $e->estado = Emergencia::ESTADO_CONFIRMADA;
            $e->confirmada_en = $now;
            $e->save();

            broadcast(new EmergenciaActualizada($e));
        }

        $this->info("Confirmadas: " . $vencidas->count());

        return self::SUCCESS;
    }
}
