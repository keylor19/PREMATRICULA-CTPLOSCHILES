<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nombre_completo');
            $table->string('relacion'); // padre, madre, tutor legal, etc.
            $table->string('cedula');
            $table->string('telefono_principal');
            $table->string('telefono_secundario')->nullable();
            $table->string('email');
            $table->string('ocupacion')->nullable();
            $table->text('direccion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutores');
    }
};