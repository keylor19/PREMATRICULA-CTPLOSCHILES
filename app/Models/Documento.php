<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
   protected $fillable = [
    'prematricula_id',
    'tipo',
    'entregado_fisico',
    'nombre_original',
    'ruta',
];
    public function prematricula()
    {
        return $this->belongsTo(Prematricula::class);
    }
}
