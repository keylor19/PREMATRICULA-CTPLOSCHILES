<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $table = 'periodos';

    protected $fillable = [
        'anio',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'activo'       => 'boolean',
    ];

    public function niveles()
    {
        return $this->hasMany(Nivel::class);
    }

    public function prematriculas()
    {
        return $this->hasMany(Prematricula::class);
    }

    /**
     * Devuelve el período activo actualmente.
     */
    public static function activo(): ?self
    {
        return self::where('activo', true)->first();
    }

    /**
     * Verifica si el período está abierto para recibir prematrículas.
     */
    public function estaAbierto(): bool
    {
        $hoy = now()->toDateString();
        return $this->activo
            && $this->fecha_inicio->toDateString() <= $hoy
            && $this->fecha_fin->toDateString() >= $hoy;
    }

    /**
     * Verifica si la fecha de cierre del período ya pasó.
     */
    public function estaVencido(): bool
    {
        return $this->fecha_fin->toDateString() < now()->toDateString();
    }
}