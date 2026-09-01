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

class PlanNacionalSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
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
            'Correo MEP',
            'Nivel',
            'Sección',
            'Técnica 1',
            'Técnica 2',
            'Formación vocacional',
            'Técnica',
            'Seguimiento',
            'Tipo de discapacidad',
            'Boleta de ubicación',
            'Nivel de funcionamiento',
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
            ->with(['estudiante', 'tutor', 'tutores', 'nivel', 'seccion', 'documentos'])
            ->orderBy('created_at')
            ->get();

        return $prematriculas->map(function ($p, $i) {
            $encargados = $p->tutores->count() > 0 ? $p->tutores : collect([$p->tutor]);
            $enc2 = $encargados->get(1);
            $enc3 = $encargados->get(2);

            $etiquetasDocPN = [
    'cedula_estudiante' => 'Cédula estudiante',
    'cedula_encargado'  => 'Cédula encargado',
    'notas'             => 'Notas',
    'foto'              => 'Foto',
    'pase'              => 'PASE',
];

        $documentos = $p->documentos->map(function ($d) use ($etiquetasDocPN) {
        $estado = $d->entregado_fisico ? 'agregado en físico' : 'agregado en digital';
        $etiqueta = $etiquetasDocPN[$d->tipo] ?? ucfirst(str_replace('_', ' ', $d->tipo));
        return $etiqueta . ' (' . $estado . ')';
        })->implode('; ');

            return [
                $i + 1,
                $p->codigo,
                $p->estudiante->nombre . ' ' . $p->estudiante->apellido,
                $p->estudiante->cedula,
                $p->estudiante->fecha_nacimiento->age,
                $p->estudiante->fecha_nacimiento->format('d/m/Y'),
                $p->estudiante->genero ?? '—',
                $p->estudiante->email_mep ?? '—',
                $p->nivel->nombre ?? '—',
                $p->seccion->nombre ?? '—',
                $p->tecnica_1 ?? '—',
                $p->tecnica_2 ?? '—',
                $p->formacion_vocacional ?? '—',
                $p->tecnica_3 ?? '—',
                $p->seguimiento_pn ?? '—',
                $p->estudiante->tipo_discapacidad ?? '—',
                $p->estudiante->boleta_ubicacion ?? '—',
                $p->estudiante->nivel_funcionamiento ?? '—',
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
            'G' => 10, 'H' => 26, 'I' => 14, 'J' => 10, 'K' => 18, 'L' => 18,
            'M' => 20, 'N' => 18, 'O' => 22, 'P' => 20, 'Q' => 16, 'R' => 24,
            'S' => 28, 'T' => 24, 'U' => 14, 'V' => 14, 'W' => 26, 'X' => 22,
            'Y' => 14, 'Z' => 22, 'AA' => 14, 'AB' => 35, 'AC' => 12, 'AD' => 18,
        ];
    }
}