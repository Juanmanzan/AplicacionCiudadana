<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoEmergenciaSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'Emergencia médica',
            'Incendio',
            'Asalto',
            'Siniestro de tránsito',
        ];

        foreach ($tipos as $t) {
            DB::table('tipo_emergencia')->updateOrInsert(
                ['tipo' => $t],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
