<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->boolean('entregado_fisico')->default(false)->after('tipo');
            $table->string('ruta')->nullable()->change();
            $table->string('nombre_original')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropColumn('entregado_fisico');
        });
    }
};