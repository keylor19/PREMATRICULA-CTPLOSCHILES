<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prematriculas', function (Blueprint $table) {
            $table->foreignId('taller_tercera_opcion_id')->nullable()->constrained('seccion_talleres')->after('taller_segunda_opcion_id');
            $table->foreignId('taller_cuarta_opcion_id')->nullable()->constrained('seccion_talleres')->after('taller_tercera_opcion_id');

            $table->foreignId('carrera_tercera_opcion_id')->nullable()->constrained('carreras')->after('carrera_segunda_opcion_id');
            $table->foreignId('carrera_cuarta_opcion_id')->nullable()->constrained('carreras')->after('carrera_tercera_opcion_id');
        });
    }

    public function down(): void
    {
        Schema::table('prematriculas', function (Blueprint $table) {
            $table->dropForeign(['taller_tercera_opcion_id']);
            $table->dropForeign(['taller_cuarta_opcion_id']);
            $table->dropForeign(['carrera_tercera_opcion_id']);
            $table->dropForeign(['carrera_cuarta_opcion_id']);
            $table->dropColumn([
                'taller_tercera_opcion_id',
                'taller_cuarta_opcion_id',
                'carrera_tercera_opcion_id',
                'carrera_cuarta_opcion_id',
            ]);
        });
    }
};