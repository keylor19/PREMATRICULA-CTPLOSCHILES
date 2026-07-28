<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seccion_talleres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seccion_id')->constrained('secciones')->onDelete('cascade');
            $table->string('grupo');        // "A" o "B"
            $table->string('nombre');       // ej. "Turismo", "Textiles"
            $table->text('descripcion')->nullable();
            $table->timestamps();

            // Cada sección solo puede tener un taller por grupo
            $table->unique(['seccion_id', 'grupo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seccion_talleres');
    }
};