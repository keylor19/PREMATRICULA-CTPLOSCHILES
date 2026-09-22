<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea el usuario administrador inicial del sistema.
     * Si ya existe un usuario con este correo, actualiza su contraseña y rol
     * en vez de fallar (así el seeder se puede correr varias veces sin problema).
     *
     * La contraseña sale de ADMIN_INITIAL_PASSWORD (.env, nunca se sube al
     * repo) para no dejar una contraseña real escrita en el código fuente
     * público. Si esa variable no está definida (instalación nueva sin
     * configurarla todavía), usa una genérica que hay que cambiar de una vez.
     */
    public function run(): void
    {
        $email = 'admin@ctp.local';
        $passwordPlano = env('ADMIN_INITIAL_PASSWORD', 'CambiaEstaClave123');

        $usuario = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin CTP',
                'password' => Hash::make($passwordPlano),
            ]
        );

        // No degradar a un superadmin ya promovido (ver php artisan app:hacer-super-admin).
        if ($usuario->rol !== 'superadmin') {
            $usuario->rol = 'admin';
        }
        $usuario->save();

        $this->command->info("Usuario admin listo: {$usuario->email} (id: {$usuario->id}, rol: {$usuario->rol})");

        if (env('ADMIN_INITIAL_PASSWORD')) {
            $this->command->info('Contraseña tomada de ADMIN_INITIAL_PASSWORD (.env).');
        } else {
            $this->command->warn("Contraseña temporal: {$passwordPlano} — cambiala después de tu primer login, o definí ADMIN_INITIAL_PASSWORD en tu .env.");
        }
    }
}