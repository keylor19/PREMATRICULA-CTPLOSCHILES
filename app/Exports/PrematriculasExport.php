<?php

namespace App\Exports;

use App\Models\Prematricula;
use App\Models\Seccion;
use App\Models\Carrera;
use App\Models\Modalidad;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PrematriculasExport implements WithMultipleSheets
{
    public function __construct(
        public int $periodoId,
        public int $modalidadId
    ) {}

    public function sheets(): array
    {
        $sheets = [];
        $modalidad = Modalidad::find($this->modalidadId);
        $nombreModalidad = $modalidad?->nombre;

        if ($nombreModalidad === 'Diurna') {
            // Hojas por sección (Diurna)
            $seccionIds = Prematricula::where('periodo_id', $this->periodoId)
                ->where('modalidad_id', $this->modalidadId)
                ->whereNotNull('seccion_id')
                ->pluck('seccion_id')
                ->unique();

            $secciones = Seccion::with(['nivel'])
                ->whereIn('id', $seccionIds)
                ->orderBy('nivel_id')
                ->orderBy('numero')
                ->get();

            foreach ($secciones as $seccion) {
                $sheets[] = new SeccionSheet($seccion, $this->periodoId, $this->modalidadId);
            }

            $sinSeccion = Prematricula::where('periodo_id', $this->periodoId)
                ->where('modalidad_id', $this->modalidadId)
                ->whereNull('seccion_id')
                ->count();

            if ($sinSeccion > 0) {
                $sheets[] = new SinSeccionSheet($this->periodoId, $this->modalidadId);
            }

        } elseif ($nombreModalidad === 'Plan Nacional') {
            // Hojas por sección (Plan Nacional, con campos propios)
            $seccionIds = Prematricula::where('periodo_id', $this->periodoId)
                ->where('modalidad_id', $this->modalidadId)
                ->whereNotNull('seccion_id')
                ->pluck('seccion_id')
                ->unique();

            $secciones = Seccion::with(['nivel'])
                ->whereIn('id', $seccionIds)
                ->orderBy('nivel_id')
                ->orderBy('numero')
                ->get();

            foreach ($secciones as $seccion) {
                $sheets[] = new PlanNacionalSheet($seccion, $this->periodoId, $this->modalidadId);
            }

            $sinSeccion = Prematricula::where('periodo_id', $this->periodoId)
                ->where('modalidad_id', $this->modalidadId)
                ->whereNull('seccion_id')
                ->count();

            if ($sinSeccion > 0) {
                $sheets[] = new SinSeccionSheet($this->periodoId, $this->modalidadId);
            }

        } else {
            // Hojas por carrera técnica (Nocturna)
            $carreraIds = Prematricula::where('periodo_id', $this->periodoId)
                ->where('modalidad_id', $this->modalidadId)
                ->whereNotNull('carrera_id')
                ->pluck('carrera_id')
                ->unique();

            $carreras = Carrera::with(['nivel'])
                ->whereIn('id', $carreraIds)
                ->orderBy('nivel_id')
                ->orderBy('nombre')
                ->get();

            foreach ($carreras as $carrera) {
                $sheets[] = new CarreraSheet($carrera, $this->periodoId, $this->modalidadId);
            }

            $sinCarrera = Prematricula::where('periodo_id', $this->periodoId)
                ->where('modalidad_id', $this->modalidadId)
                ->whereNull('carrera_id')
                ->count();

            if ($sinCarrera > 0) {
                $sheets[] = new SinSeccionSheet($this->periodoId, $this->modalidadId);
            }
        }

        if (empty($sheets)) {
            $sheets[] = new HojaVaciaSheet();
        }

        return $sheets;
    }
}