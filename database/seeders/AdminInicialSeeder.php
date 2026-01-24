<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class AdminInicialSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::updateOrCreate(
            ['cedula' => '0605594449'], 
            [
                'nombres' => 'Juan',
                'apellidos' => 'Manzano',
                'genero' => 'H', 
                'correo_electronico' => 'juandavidabc.19@gmail.com',
                'numero_celular' => '0964131003', 
                'usuario' => 'admin', 
                'password' => Hash::make('admin123'), 
                'admin' => true,
                'edad' => 25, 
            ]
        );
    }
}
