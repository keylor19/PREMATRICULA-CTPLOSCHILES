<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');                    // ej. 2026
            $table->string('nombre');                   // ej. "Prematrícula 2026"
            $table->date('fecha_inicio');               // fecha de apertura
            $table->date('fecha_fin');                  // fecha de cierre
            $table->boolean('activo')->default(false);  // solo uno activo a la vez
            $table->timestamps();
        });

        // Agregar periodo_id a las tablas relacionadas
        Schema::table('prematriculas', function (Blueprint $table) {
            $table->foreignId('periodo_id')->nullable()->constrained('periodos')->after('id');
        });

        Schema::table('niveles', function (Blueprint $table) {
            $table->foreignId('periodo_id')->nullable()->constrained('periodos')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('niveles', function (Blueprint $table) {
            $table->dropForeign(['periodo_id']);
            $table->dropColumn('periodo_id');
        });

        Schema::table('prematriculas', function (Blueprint $table) {
            $table->dropForeign(['periodo_id']);
            $table->dropColumn('periodo_id');
        });

        Schema::dropIfExists('periodos');
    }
};