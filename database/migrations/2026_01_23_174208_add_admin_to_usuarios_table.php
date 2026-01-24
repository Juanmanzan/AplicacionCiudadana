<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // admin
            if (!Schema::hasColumn('usuarios', 'admin')) {
                $table->boolean('admin')->default(false);
            }

            // correo
            if (!Schema::hasColumn('usuarios', 'correo_electronico')) {
                $table->string('correo_electronico', 150)->nullable();
            }
        });

        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS uq_usuarios_correo ON usuarios (correo_electronico);");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS uq_usuarios_correo;");

        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'admin')) {
                $table->dropColumn('admin');
            }
            if (Schema::hasColumn('usuarios', 'correo_electronico')) {
                $table->dropColumn('correo_electronico');
            }
        });
    }
};
