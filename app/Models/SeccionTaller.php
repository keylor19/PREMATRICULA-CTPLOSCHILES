<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeccionTaller extends Model
{
    protected $table = 'seccion_talleres';

    protected $fillable = [
        'seccion_id',
        'grupo',
        'nombre',
        'descripcion',
        'capacidad',
    ];

    public function seccion()
    {
        return $this->belongsTo(Seccion::class);
    }

    /**
     * Cuenta cuántas prematrículas aprobadas o pendientes hay en este grupo/sección.
     */
    public function cuposOcupados(): int
    {
        return Prematricula::where('seccion_id', $this->seccion_id)
            ->where('grupo_taller', $this->grupo)
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->count();
    }

    public function cuposDisponibles(): int
    {
        return max(0, $this->capacidad - $this->cuposOcupados());
    }

    public function estaLleno(): bool
    {
        return $this->cuposDisponibles() === 0;
    }
}