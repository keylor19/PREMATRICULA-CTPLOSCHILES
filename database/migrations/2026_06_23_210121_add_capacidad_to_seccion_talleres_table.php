<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seccion_talleres', function (Blueprint $table) {
            $table->integer('capacidad')->default(15)->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('seccion_talleres', function (Blueprint $table) {
            $table->dropColumn('capacidad');
        });
    }
};