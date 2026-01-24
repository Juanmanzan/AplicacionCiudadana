<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mensajes_predefinidos', function (Blueprint $table) {
            $table->bigIncrements('id_mensaje_predefinido');

            $table->string('cedula_usuario', 10);
            $table->unsignedSmallInteger('id_tipo_emergencia');

            $table->text('mensaje');
            $table->string('combinacion_botones', 50)->nullable(); // Ej: POWER+VOL_UP

            $table->timestamps();

            // índices
            $table->index(['cedula_usuario', 'id_tipo_emergencia'], 'idx_msg_usuario_tipo');

            // FK usuario
            $table->foreign('cedula_usuario')
                ->references('cedula')->on('usuarios')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // FK tipo emergencia
            $table->foreign('id_tipo_emergencia')
                ->references('id_tipo_emergencia')->on('tipo_emergencia')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        // (Opcional recomendado) Evitar duplicados del mismo atajo por usuario+tipo
        DB::statement("
            ALTER TABLE mensajes_predefinidos
            ADD CONSTRAINT uq_msg_combo
            UNIQUE (cedula_usuario, id_tipo_emergencia, combinacion_botones)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_predefinidos');
    }
};
