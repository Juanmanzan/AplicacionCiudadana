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
       
        Schema::create('contactos', function (Blueprint $table) {
            $table->bigIncrements('id_contacto');

            $table->string('cedula_usuario', 10);
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('numero_celular', 20);

            $table->string('correo_electronico', 150)->nullable();
            $table->string('parentesco', 50)->nullable();

            $table->timestamps();

            // Index para consultas frecuentes
            $table->index('cedula_usuario', 'idx_contactos_cedula_usuario');

            // FK
            $table->foreign('cedula_usuario')
                ->references('cedula')->on('usuarios')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // si se borra usuario, se borran sus contactos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contactos');
    }
};
