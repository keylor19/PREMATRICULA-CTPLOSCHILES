<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('docente', 'admin') NOT NULL DEFAULT 'docente'");

        // Actualizamos cualquier usuario que tuviera el rol anterior 'encargado' hacia 'docente'
        DB::table('users')->where('rol', 'encargado')->update(['rol' => 'docente']);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('encargado', 'admin') NOT NULL DEFAULT 'encargado'");
    }
};