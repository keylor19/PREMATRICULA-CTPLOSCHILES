<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prematriculas', function (Blueprint $table) {
            $table->string('tecnica_1')->nullable()->after('carrera_cuarta_opcion_id');
            $table->string('tecnica_2')->nullable()->after('tecnica_1');
            $table->string('tecnica_3')->nullable()->after('tecnica_2');
            $table->string('formacion_vocacional')->nullable()->after('tecnica_3');
            $table->text('notas_pn')->nullable()->after('formacion_vocacional');
            $table->text('seguimiento_pn')->nullable()->after('notas_pn');
        });
    }

    public function down(): void
    {
        Schema::table('prematriculas', function (Blueprint $table) {
            $table->dropColumn(['tecnica_1', 'tecnica_2', 'tecnica_3', 'formacion_vocacional', 'notas_pn', 'seguimiento_pn']);
        });
    }
};