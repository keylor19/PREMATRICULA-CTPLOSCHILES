<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el rol "superadmin": por encima de "admin", con acceso a
     * acciones irreversibles (por ahora, borrar un período de matrícula).
     * No se asigna a nadie automáticamente aquí — se promueve manualmente
     * a la cuenta que corresponda (ver php artisan user:hacer-superadmin).
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('docente', 'admin', 'superadmin') NOT NULL DEFAULT 'docente'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('rol', ['docente', 'admin', 'superadmin'])->default('docente')->change();
            });
        }
    }

    public function down(): void
    {
        DB::table('users')->where('rol', 'superadmin')->update(['rol' => 'admin']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('docente', 'admin') NOT NULL DEFAULT 'docente'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('rol', ['docente', 'admin'])->default('docente')->change();
            });
        }
    }
};
