<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prematriculas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // ej. PM-00001
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('tutor_id')->constrained('tutores')->onDelete('cascade');
            $table->integer('nivel_solicitado'); // 7, 8, 9, 10, 11
            $table->string('seccion_preferida')->nullable();
            $table->string('colegio_procedencia');
            $table->string('anio_cursado_anterior');
            $table->text('motivo')->nullable();
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('nota_admin')->nullable();
            $table->timestamp('fecha_decision')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prematriculas');
    }
};