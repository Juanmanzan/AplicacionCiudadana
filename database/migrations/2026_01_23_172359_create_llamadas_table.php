<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('llamadas', function (Blueprint $table) {
            $table->bigIncrements('id_llamada');

            $table->unsignedBigInteger('id_emergencia');
            $table->unsignedBigInteger('id_contacto');

            $table->string('medio', 15); // LLAMADA, SMS, WHATSAPP, EMAIL
            $table->text('mensaje_enviado')->nullable();

            $table->string('estado', 20)->default('ENVIADO'); // ENVIADO, ENTREGADO, FALLIDO
            $table->timestampTz('fecha_hora_envio')->default(DB::raw('NOW()'));

            $table->timestamps();

            // índices
            $table->index('id_emergencia', 'idx_llamadas_emergencia');
            $table->index('id_contacto', 'idx_llamadas_contacto');

            // FKs
            $table->foreign('id_emergencia')
                ->references('id_emergencia')->on('emergencia')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // si se borra la emergencia, se borran sus llamadas

            $table->foreign('id_contacto')
                ->references('id_contacto')->on('contactos')
                ->onUpdate('cascade')
                ->onDelete('restrict'); // no borrar contacto si hay histórico (opcional)
        });

        // Checks (opcional pero recomendado)
        DB::statement("
            ALTER TABLE llamadas
            ADD CONSTRAINT chk_llamadas_medio
            CHECK (medio IN ('LLAMADA','SMS','WHATSAPP','EMAIL'))
        ");

        DB::statement("
            ALTER TABLE llamadas
            ADD CONSTRAINT chk_llamadas_estado
            CHECK (estado IN ('ENVIADO','ENTREGADO','FALLIDO'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('llamadas');
    }
};
