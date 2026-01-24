<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tipo_emergencia', function (Blueprint $table) {
            $table->smallIncrements('id_tipo_emergencia');
            $table->string('tipo', 60)->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_emergencia');
    }
};
