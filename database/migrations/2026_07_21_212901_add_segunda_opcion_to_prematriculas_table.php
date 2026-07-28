<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prematriculas', function (Blueprint $table) {
            $table->foreignId('taller_segunda_opcion_id')->nullable()->constrained('seccion_talleres')->after('grupo_taller');
            $table->foreignId('carrera_segunda_opcion_id')->nullable()->constrained('carreras')->after('carrera_id');
        });
    }

    public function down(): void
    {
        Schema::table('prematriculas', function (Blueprint $table) {
            $table->dropForeign(['taller_segunda_opcion_id']);
            $table->dropForeign(['carrera_segunda_opcion_id']);
            $table->dropColumn(['taller_segunda_opcion_id', 'carrera_segunda_opcion_id']);
        });
    }
};