<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('niveles', function (Blueprint $table) {
            $table->id();
            $table->integer('numero');        // 7 o 10
            $table->string('nombre');         // "Sétimo año", "Décimo año"
            $table->integer('seccion_inicio')->default(1); // ej. 1
            $table->integer('seccion_fin')->default(1);    // ej. 7
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('niveles');
    }
};