<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('padecimientos', function (Blueprint $table) {
            $table->bigIncrements('id_padecimiento');

            $table->string('cedula_usuario', 10);
            $table->text('descripcion');

            $table->timestamps();

            $table->index('cedula_usuario', 'idx_padecimientos_cedula_usuario');

            $table->foreign('cedula_usuario')
                ->references('cedula')->on('usuarios')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('padecimientos');
    }
};
