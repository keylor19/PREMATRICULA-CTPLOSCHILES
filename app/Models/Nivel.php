<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    protected $table = 'niveles';
    protected $fillable = [
        'numero',
        'nombre',
        'seccion_inicio',
        'seccion_fin',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function talleres()
    {
        return $this->hasMany(Taller::class);
    }

    public function secciones()
    {
        return $this->hasMany(Seccion::class);
    }

    public function prematriculas()
    {
        return $this->hasMany(Prematricula::class);
    }
    public function modalidad()
{
    return $this->belongsTo(Modalidad::class);
}

    public function periodo()
{
    return $this->belongsTo(Periodo::class);
}
public function carreras()
{
    return $this->hasMany(Carrera::class);
}

    /**
     * Genera automáticamente los registros de secciones según el rango configurado.
     * Ej: nivel 7, inicio 1, fin 7 → crea 7-1, 7-2 ... 7-7
     */
    public function generarSecciones(): void
    {
        // Primero borra las secciones existentes de este nivel
        $this->secciones()->delete();

        for ($i = $this->seccion_inicio; $i <= $this->seccion_fin; $i++) {
            Seccion::create([
                'nivel_id' => $this->id,
                'nombre'   => $this->numero . '-' . $i,
                'numero'   => $i,
                'activa'   => true,
            ]);
        }
    }
    
}