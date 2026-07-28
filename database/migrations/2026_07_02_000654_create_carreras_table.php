<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modalidad_id')->constrained('modalidades')->onDelete('cascade');
            $table->foreignId('nivel_id')->constrained('niveles')->onDelete('cascade');
            $table->string('nombre');              // ej. "Contabilidad", "Aduanas"
            $table->integer('capacidad')->default(20); // cupo modificable
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::table('prematriculas', function (Blueprint $table) {
            $table->foreignId('carrera_id')->nullable()->constrained('carreras')->after('modalidad_id');
        });
    }

    public function down(): void
    {
        Schema::table('prematriculas', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
            $table->dropColumn('carrera_id');
        });
        Schema::dropIfExists('carreras');
    }
};