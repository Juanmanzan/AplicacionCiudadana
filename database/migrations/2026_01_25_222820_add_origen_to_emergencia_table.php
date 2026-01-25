<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emergencia', function (Blueprint $table) {
            $table->string('origen', 20)->default('REPORTE');
        });
    }

    public function down(): void
    {
        Schema::table('emergencia', function (Blueprint $table) {
            $table->dropColumn('origen');
        });
    }
};
