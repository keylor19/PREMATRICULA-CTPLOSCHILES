<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla principal de modalidades
        Schema::create('modalidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');        // Diurna, Nocturna, Plan Nacional
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        // Tabla pivote: docente ↔ modalidad
        Schema::create('docente_modalidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('modalidad_id')->constrained('modalidades')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'modalidad_id']);
        });

        // Agregar modalidad_id a niveles y prematriculas
        Schema::table('niveles', function (Blueprint $table) {
            $table->foreignId('modalidad_id')->nullable()->constrained('modalidades')->after('periodo_id');
        });

        Schema::table('prematriculas', function (Blueprint $table) {
            $table->foreignId('modalidad_id')->nullable()->constrained('modalidades')->after('periodo_id');
        });
    }

    public function down(): void
    {
        Schema::table('prematriculas', function (Blueprint $table) {
            $table->dropForeign(['modalidad_id']);
            $table->dropColumn('modalidad_id');
        });

        Schema::table('niveles', function (Blueprint $table) {
            $table->dropForeign(['modalidad_id']);
            $table->dropColumn('modalidad_id');
        });

        Schema::dropIfExists('docente_modalidad');
        Schema::dropIfExists('modalidades');
    }
};