<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Quitar índice existente si existe (por si acaso)
        try {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->dropIndex(['tokenable_type', 'tokenable_id']);
            });
        } catch (\Throwable $e) {
            // si no existe, no pasa nada
        }

        // En PostgreSQL, cambiar bigint -> varchar usando CAST
        DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE varchar(10) USING tokenable_id::text');

        // Crear índice compuesto nuevamente
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->index(['tokenable_type', 'tokenable_id']);
        });
    }

    public function down(): void
    {
        // revertir (si deseas)
        try {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->dropIndex(['tokenable_type', 'tokenable_id']);
            });
        } catch (\Throwable $e) {}

        DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE bigint USING tokenable_id::bigint');

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->index(['tokenable_type', 'tokenable_id']);
        });
    }
};
