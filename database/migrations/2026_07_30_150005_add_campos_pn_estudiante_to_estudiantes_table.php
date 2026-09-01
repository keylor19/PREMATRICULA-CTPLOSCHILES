<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->string('tipo_discapacidad')->nullable()->after('adecuacion');
            $table->string('boleta_ubicacion')->nullable()->after('tipo_discapacidad');
            $table->text('nivel_funcionamiento')->nullable()->after('boleta_ubicacion');
        });
    }

    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn(['tipo_discapacidad', 'boleta_ubicacion', 'nivel_funcionamiento']);
        });
    }
};