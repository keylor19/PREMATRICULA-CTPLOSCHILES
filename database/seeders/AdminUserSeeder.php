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
     */
    public function run(): void
    {
        $email = 'admin@ctp.local';
        $passwordPlano = 'CambiaEstaClave123';

        $usuario = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin CTP',
                'password' => Hash::make($passwordPlano),
            ]
        );
        $usuario->rol = 'admin';
        $usuario->save();

        $this->command->info("Usuario admin listo: {$usuario->email} (id: {$usuario->id})");
        $this->command->warn("Contraseña temporal: {$passwordPlano} — cambiala después de tu primer login.");
    }
}