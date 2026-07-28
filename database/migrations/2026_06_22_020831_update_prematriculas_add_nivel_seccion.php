<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prematriculas', function (Blueprint $table) {
            // Agregar nuevas columnas
            $table->foreignId('nivel_id')->nullable()->constrained('niveles')->after('tutor_id');
            $table->foreignId('seccion_id')->nullable()->constrained('secciones')->after('nivel_id');
            $table->string('grupo_taller')->nullable()->after('seccion_id'); // "A" o "B"

            // Hacer nullable las columnas viejas (para no romper registros existentes)
            $table->integer('nivel_solicitado')->nullable()->change();
            $table->string('seccion_preferida')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('prematriculas', function (Blueprint $table) {
            $table->dropForeign(['nivel_id']);
            $table->dropForeign(['seccion_id']);
            $table->dropColumn(['nivel_id', 'seccion_id', 'grupo_taller']);
        });
    }
};
