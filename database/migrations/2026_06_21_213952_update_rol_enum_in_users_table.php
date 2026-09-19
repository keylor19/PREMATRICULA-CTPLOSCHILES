<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ALTER ... MODIFY COLUMN es sintaxis específica de MySQL. En otros
        // motores (p. ej. SQLite, usado en tests) el enum se emula con un
        // CHECK constraint que hay que recrear con el Schema Builder.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('docente', 'admin') NOT NULL DEFAULT 'docente'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('rol', ['encargado', 'docente', 'admin'])->default('docente')->change();
            });
        }

        // Actualizamos cualquier usuario que tuviera el rol anterior 'encargado' hacia 'docente'
        DB::table('users')->where('rol', 'encargado')->update(['rol' => 'docente']);

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('rol', ['docente', 'admin'])->default('docente')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('encargado', 'admin') NOT NULL DEFAULT 'encargado'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('rol', ['encargado', 'admin'])->default('encargado')->change();
            });
        }
    }
};