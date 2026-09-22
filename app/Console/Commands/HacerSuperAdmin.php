<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:hacer-super-admin {email : Correo del usuario que se va a promover}')]
#[Description('Promueve una cuenta existente a superadmin (único rol que puede borrar períodos de matrícula).')]
class HacerSuperAdmin extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email');
        $usuario = User::where('email', $email)->first();

        if (!$usuario) {
            $this->error("No existe ningún usuario con el correo {$email}.");
            return self::FAILURE;
        }

        if ($usuario->rol === 'superadmin') {
            $this->info("{$usuario->email} ya es superadmin.");
            return self::SUCCESS;
        }

        if (!$this->confirm("¿Promover a \"{$usuario->name}\" ({$usuario->email}) a superadmin? Podrá borrar períodos de matrícula.")) {
            $this->comment('Cancelado.');
            return self::SUCCESS;
        }

        $usuario->rol = 'superadmin';
        $usuario->save();

        $this->info("Listo: {$usuario->email} ahora es superadmin.");
        return self::SUCCESS;
    }
}
