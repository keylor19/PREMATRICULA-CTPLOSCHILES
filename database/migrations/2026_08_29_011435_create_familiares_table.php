<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('familiares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->enum('tipo', ['padre', 'madre']);
            $table->string('nombre_completo')->nullable();
            $table->string('cedula')->nullable();
            $table->string('telefono_principal')->nullable();
            $table->string('telefono_secundario')->nullable();
            $table->string('email')->nullable();
            $table->string('ocupacion')->nullable();
            $table->text('direccion')->nullable();
            $table->timestamps();

            $table->unique(['estudiante_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('familiares');
    }
};