<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
             
            $table->string('cedula', 10)->primary();

            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('genero', 1);
            $table->string('usuario', 50)->unique();
            // HASH (bcrypt/argon)
            $table->string('password');
            $table->unsignedTinyInteger('edad')->nullable();
            $table->string('numero_celular', 20)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
