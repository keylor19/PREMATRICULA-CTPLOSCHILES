<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
     public function tutor()
    {
        return $this->hasOne(Tutor::class);
    }

    public function prematricula()
    {
        return $this->hasOne(Prematricula::class);
    }

    public function esAdmin(): bool
    {
        return in_array($this->rol, ['admin', 'superadmin'], true);
    }

    /**
     * El superadmin tiene, además de todo lo de admin, acceso a acciones
     * irreversibles (por ahora, borrar un período de matrícula). Se asigna
     * manualmente vía `php artisan app:hacer-super-admin`, nunca desde el
     * formulario de gestión de docentes.
     */
    public function esSuperAdmin(): bool
    {
        return $this->rol === 'superadmin';
    }

    public function modalidades()
{
    return $this->belongsToMany(Modalidad::class, 'docente_modalidad');
}
}
