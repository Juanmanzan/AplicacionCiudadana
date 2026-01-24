<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emergencia', function (Blueprint $table) {
            $table->bigIncrements('id_emergencia');

            $table->string('cedula_usuario', 10);
            $table->unsignedSmallInteger('id_tipo_emergencia');

            $table->string('descripcion', 280);

            // No lo creamos con Blueprint porque es PostGIS (lo hacemos con DB::statement)
            // $table->geometry('ubicacion');  <-- (Laravel no lo trae nativo)

            $table->timestampTz('fecha_hora')->default(DB::raw('NOW()'));

            // Estado simple (si quieres luego lo pasamos a ENUM)
            $table->string('estado', 20)->default('PENDIENTE');

            $table->timestamps();

            $table->index(['cedula_usuario', 'fecha_hora'], 'idx_emergencia_usuario_fecha');
            $table->index(['id_tipo_emergencia', 'fecha_hora'], 'idx_emergencia_tipo_fecha');

            // FKs
            $table->foreign('cedula_usuario')
                ->references('cedula')->on('usuarios')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_tipo_emergencia')
                ->references('id_tipo_emergencia')->on('tipo_emergencia')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        // Crear columna PostGIS: POINT con SRID 4326
        DB::statement("ALTER TABLE emergencia ADD COLUMN ubicacion geometry(Point, 4326) NOT NULL;");

        // Índice espacial GiST (clave para rendimiento en el mapa)
        DB::statement("CREATE INDEX idx_emergencia_ubicacion_gist ON emergencia USING GIST (ubicacion);");

        // (Opcional recomendado) Validar estados permitidos
        DB::statement("
            ALTER TABLE emergencia
            ADD CONSTRAINT chk_emergencia_estado
            CHECK (estado IN ('PENDIENTE','ATENDIDA','CANCELADA','FALSA_ALARMA'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('emergencia');
    }
};
