<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prematricula extends Model
{
    protected $fillable = [
    'codigo',
    'user_id',
    'estudiante_id',
    'tutor_id',
    'nivel_id',
    'seccion_id',
    'grupo_taller',
    'nivel_solicitado',
    'seccion_preferida',
    'colegio_procedencia',
    'anio_cursado_anterior',
    'motivo',
    'estado',
    'nota_admin',
    'fecha_decision',
    'periodo_id',
    'modalidad_id',
    'carrera_id',
    'taller_segunda_opcion_id',
    'carrera_segunda_opcion_id',
    'taller_tercera_opcion_id',
    'taller_cuarta_opcion_id',
    'carrera_tercera_opcion_id',
    'carrera_cuarta_opcion_id',
    'tecnica_1',
    'tecnica_2',
    'tecnica_3',
    'formacion_vocacional',
    'notas_pn',
    'seguimiento_pn',
];
    protected $casts = [
        'fecha_decision' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }
    public function nivel()
{
    return $this->belongsTo(Nivel::class);
}

public function seccion()
{
    return $this->belongsTo(Seccion::class);
}

public function periodo()
{
    return $this->belongsTo(Periodo::class);
}
public function modalidad()
{
    return $this->belongsTo(Modalidad::class);
}
public function carrera()
{
    return $this->belongsTo(Carrera::class);
}

    /**
     * Genera un código único tipo PM-00001 para cada solicitud nueva.
     */
    public static function generarCodigo(): string
    {
        $ultimoId = self::max('id') ?? 0;
        return 'PM-' . str_pad($ultimoId + 1, 5, '0', STR_PAD_LEFT);
    }

    public function tutores()
{
    return $this->belongsToMany(Tutor::class, 'prematricula_tutores')
        ->withPivot('principal', 'orden')
        ->orderBy('prematricula_tutores.orden');
}

public function tallerSegundaOpcion()
{
    return $this->belongsTo(SeccionTaller::class, 'taller_segunda_opcion_id');
}

public function carreraSegundaOpcion()
{
    return $this->belongsTo(Carrera::class, 'carrera_segunda_opcion_id');
}

public function tallerTerceraOpcion()
{
    return $this->belongsTo(SeccionTaller::class, 'taller_tercera_opcion_id');
}

public function tallerCuartaOpcion()
{
    return $this->belongsTo(SeccionTaller::class, 'taller_cuarta_opcion_id');
}

public function carreraTerceraOpcion()
{
    return $this->belongsTo(Carrera::class, 'carrera_tercera_opcion_id');
}

public function carreraCuartaOpcion()
{
    return $this->belongsTo(Carrera::class, 'carrera_cuarta_opcion_id');
}


}