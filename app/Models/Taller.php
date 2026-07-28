<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taller extends Model
{
    protected $table = 'talleres';
    protected $fillable = [
        'nivel_id',
        'nombre',
        'grupo',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }
}