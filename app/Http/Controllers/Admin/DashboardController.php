<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periodo;
use App\Models\Modalidad;
use App\Models\Prematricula;
use App\Models\Seccion;
use App\Models\Carrera;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $periodoActivo = Periodo::activo();
        $periodos      = Periodo::latest()->get();
        $modalidades   = Modalidad::where('activa', true)->get();

        $periodoId   = $request->get('periodo_id', $periodoActivo?->id);
        $modalidadId = $request->get('modalidad_id');

        $periodo   = $periodoId ? Periodo::find($periodoId) : null;
        $modalidadSeleccionada = $modalidadId ? Modalidad::find($modalidadId) : null;

        $stats = [];
        $bloques = []; // cada bloque = una modalidad con su tabla

        if ($periodo) {
            $query = Prematricula::where('periodo_id', $periodo->id);
            if ($modalidadId) {
                $query->where('modalidad_id', $modalidadId);
            }

            $stats = [
                'total'     => $query->clone()->count(),
                'pendiente' => $query->clone()->where('estado', 'pendiente')->count(),
                'aprobada'  => $query->clone()->where('estado', 'aprobada')->count(),
                'rechazada' => $query->clone()->where('estado', 'rechazada')->count(),
            ];

            // Determinar qué modalidades procesar: la seleccionada, o todas
            $modalidadesAProcesar = $modalidadSeleccionada
                ? collect([$modalidadSeleccionada])
                : $modalidades;

            foreach ($modalidadesAProcesar as $mod) {
                $esDiurna       = $mod->nombre === 'Diurna';
                $esPlanNacional = $mod->nombre === 'Plan Nacional';
                $porSeccion     = [];
                $porCarrera     = [];
                $porSeccionPN   = [];

                if ($esDiurna) {
                    $secciones = Seccion::with(['nivel', 'talleres'])
                        ->whereHas('prematriculas', function($q) use ($periodo, $mod) {
                            $q->where('periodo_id', $periodo->id)
                              ->where('modalidad_id', $mod->id);
                        })
                        ->orderBy('nivel_id')
                        ->orderBy('numero')
                        ->get();

                    foreach ($secciones as $seccion) {
                        $tallerA = $seccion->talleres->where('grupo', 'A')->first();
                        $tallerB = $seccion->talleres->where('grupo', 'B')->first();

                        $grupoA = Prematricula::where('periodo_id', $periodo->id)
                            ->where('seccion_id', $seccion->id)
                            ->where('grupo_taller', 'A')
                            ->where('modalidad_id', $mod->id)
                            ->count();

                        $grupoB = Prematricula::where('periodo_id', $periodo->id)
                            ->where('seccion_id', $seccion->id)
                            ->where('grupo_taller', 'B')
                            ->where('modalidad_id', $mod->id)
                            ->count();

                        $porSeccion[] = [
                            'seccion'     => $seccion->nombre,
                            'nivel'       => $seccion->nivel->nombre ?? '—',
                            'taller_a'    => $tallerA?->nombre ?? 'Grupo A',
                            'taller_b'    => $tallerB?->nombre ?? 'Grupo B',
                            'cupo_a'      => $tallerA?->capacidad ?? 0,
                            'cupo_b'      => $tallerB?->capacidad ?? 0,
                            'matricula_a' => $grupoA,
                            'matricula_b' => $grupoB,
                            'total'       => $grupoA + $grupoB,
                        ];
                    }
                } elseif ($esPlanNacional) {
                    // Plan Nacional: por sección, sin cupos (solo cuenta matriculados)
                    $secciones = Seccion::with(['nivel'])
                        ->whereHas('prematriculas', function($q) use ($periodo, $mod) {
                            $q->where('periodo_id', $periodo->id)
                              ->where('modalidad_id', $mod->id);
                        })
                        ->orderBy('nivel_id')
                        ->orderBy('numero')
                        ->get();

                    foreach ($secciones as $seccion) {
                        $matriculados = Prematricula::where('periodo_id', $periodo->id)
                            ->where('seccion_id', $seccion->id)
                            ->where('modalidad_id', $mod->id)
                            ->count();

                        $porSeccionPN[] = [
                            'nivel'        => $seccion->nivel->nombre ?? '—',
                            'seccion'      => $seccion->nombre,
                            'matriculados' => $matriculados,
                        ];
                    }
                } else {
                    // Nocturna: por carrera técnica
                    $carreras = Carrera::with(['nivel'])
                        ->where('modalidad_id', $mod->id)
                        ->where('activa', true)
                        ->orderBy('nivel_id')
                        ->orderBy('nombre')
                        ->get();

                    foreach ($carreras as $carrera) {
                        $matriculados = Prematricula::where('periodo_id', $periodo->id)
                            ->where('carrera_id', $carrera->id)
                            ->where('modalidad_id', $mod->id)
                            ->count();

                        $porCarrera[] = [
                            'nivel'        => $carrera->nivel->nombre ?? '—',
                            'carrera'      => $carrera->nombre,
                            'capacidad'    => $carrera->capacidad,
                            'matriculados' => $matriculados,
                            'disponibles'  => max(0, $carrera->capacidad - $matriculados),
                            'llena'        => $matriculados >= $carrera->capacidad,
                            'porcentaje'   => $carrera->capacidad > 0
                                ? round(($matriculados / $carrera->capacidad) * 100)
                                : 0,
                        ];
                    }
                }

                $totalModalidad = Prematricula::where('periodo_id', $periodo->id)
                    ->where('modalidad_id', $mod->id)
                    ->count();

                // Solo agregar el bloque si tiene datos configurados o prematrículas
                if (count($porSeccion) > 0 || count($porCarrera) > 0 || count($porSeccionPN) > 0 || $totalModalidad > 0) {
                    $bloques[] = [
                        'modalidad'      => $mod->nombre,
                        'esDiurna'       => $esDiurna,
                        'esPlanNacional' => $esPlanNacional,
                        'total'          => $totalModalidad,
                        'porSeccion'     => $porSeccion,
                        'porCarrera'     => $porCarrera,
                        'porSeccionPN'   => $porSeccionPN,
                    ];
                }
            }
        }

        return view('admin.dashboard', compact(
            'periodoActivo', 'periodos', 'modalidades',
            'periodo', 'modalidadSeleccionada', 'stats', 'bloques'
        ));
    }
}