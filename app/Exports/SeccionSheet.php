<?php

namespace App\Exports;

use App\Models\Prematricula;
use App\Models\Seccion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SeccionSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        public Seccion $seccion,
        public int $periodoId,
        public int $modalidadId
    ) {}

    public function title(): string
    {
        return $this->seccion->nombre;
    }

    public function headings(): array
    {
        return [
            'N°',
            'Código',
            'Nombre del estudiante',
            'Cédula',
            'Edad',
            'Fecha de nacimiento',
            'Género',
            'Adecuación',
            'Correo MEP',
            'Nivel',
            'Sección',
            'Grupo',
            'Taller',
            'Segunda opción',
            'Centro educativo de procedencia',
            'Encargado principal',
            'Relación',
            'Teléfono principal',
            'Correo encargado principal',
            'Segundo encargado',
            'Teléfono 2°',
            'Tercer encargado',
            'Teléfono 3°',
            'Documentos',
            'Estado',
            'Fecha de registro',
        ];
    }

    public function collection()
    {
        $prematriculas = Prematricula::where('periodo_id', $this->periodoId)
            ->where('modalidad_id', $this->modalidadId)
            ->where('seccion_id', $this->seccion->id)
            ->with(['estudiante', 'tutor', 'tutores', 'nivel', 'seccion', 'documentos', 'tallerSegundaOpcion.seccion'])
            ->orderBy('grupo_taller')
            ->orderBy('created_at')
            ->get();

        return $prematriculas->map(function ($p, $i) {
            $taller = $p->seccion?->talleres->where('grupo', $p->grupo_taller)->first();

            $encargados = $p->tutores->count() > 0 ? $p->tutores : collect([$p->tutor]);
            $enc2 = $encargados->get(1);
            $enc3 = $encargados->get(2);

            $segundaOpcion = $p->tallerSegundaOpcion
                ? $p->tallerSegundaOpcion->seccion->nombre . ' - ' . $p->tallerSegundaOpcion->nombre . ' (Grupo ' . $p->tallerSegundaOpcion->grupo . ')'
                : '—';

            $documentos = $p->documentos->map(function ($d) {
                $estado = $d->entregado_fisico ? 'físico' : 'digital';
                return ucfirst(str_replace('_', ' ', $d->tipo)) . ' (' . $estado . ')';
            })->implode('; ');

            return [
                $i + 1,
                $p->codigo,
                $p->estudiante->nombre . ' ' . $p->estudiante->apellido,
                $p->estudiante->cedula,
                $p->estudiante->fecha_nacimiento->age,
                $p->estudiante->fecha_nacimiento->format('d/m/Y'),
                $p->estudiante->genero ?? '—',
                $p->estudiante->adecuacion ?? 'No aplica',
                $p->estudiante->email_mep ?? '—',
                $p->nivel->nombre ?? '—',
                $p->seccion->nombre ?? '—',
                'Grupo ' . $p->grupo_taller,
                $taller?->nombre ?? '—',
                $segundaOpcion,
                $p->colegio_procedencia,
                $p->tutor->nombre_completo,
                $p->tutor->relacion,
                $p->tutor->telefono_principal,
                $p->tutor->email,
                $enc2?->nombre_completo ?? '—',
                $enc2?->telefono_principal ?? '—',
                $enc3?->nombre_completo ?? '—',
                $enc3?->telefono_principal ?? '—',
                $documentos ?: '—',
                ucfirst($p->estado),
                $p->created_at->format('d/m/Y H:i'),
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1e3a6e']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,  'B' => 12, 'C' => 28, 'D' => 14, 'E' => 7,  'F' => 16,
            'G' => 10, 'H' => 22, 'I' => 26, 'J' => 14, 'K' => 10, 'L' => 10,
            'M' => 15, 'N' => 28, 'O' => 24, 'P' => 24, 'Q' => 14, 'R' => 14,
            'S' => 26, 'T' => 22, 'U' => 14, 'V' => 22, 'W' => 14, 'X' => 35,
            'Y' => 12, 'Z' => 18,
        ];
    }
}