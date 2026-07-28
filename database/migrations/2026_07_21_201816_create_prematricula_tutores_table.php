<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prematricula_tutores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prematricula_id')->constrained('prematriculas')->onDelete('cascade');
            $table->foreignId('tutor_id')->constrained('tutores')->onDelete('cascade');
            $table->boolean('principal')->default(false);
            $table->integer('orden')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prematricula_tutores');
    }
};