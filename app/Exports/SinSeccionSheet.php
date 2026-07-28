<?php

namespace App\Exports;

use App\Models\Prematricula;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SinSeccionSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        public int $periodoId,
        public int $modalidadId
    ) {}

    public function title(): string
    {
        return 'Sin sección';
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
            'Grupo',
            'Centro educativo de procedencia',
            'Encargado principal',
            'Teléfono principal',
            'Correo encargado principal',
            'Documentos',
            'Estado',
            'Fecha de registro',
        ];
    }

    public function collection()
    {
        $prematriculas = Prematricula::where('periodo_id', $this->periodoId)
            ->where('modalidad_id', $this->modalidadId)
            ->whereNull('seccion_id')
            ->whereNull('carrera_id')
            ->with(['estudiante', 'tutor', 'nivel', 'documentos'])
            ->orderBy('created_at')
            ->get();

        return $prematriculas->map(function ($p, $i) {
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
                $p->grupo_taller ? 'Grupo ' . $p->grupo_taller : '—',
                $p->colegio_procedencia,
                $p->tutor->nombre_completo,
                $p->tutor->telefono_principal,
                $p->tutor->email,
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
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '92400e']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,  'B' => 12, 'C' => 28, 'D' => 14, 'E' => 7,  'F' => 16,
            'G' => 10, 'H' => 22, 'I' => 26, 'J' => 14, 'K' => 10, 'L' => 28,
            'M' => 24, 'N' => 14, 'O' => 26, 'P' => 35, 'Q' => 12, 'R' => 18,
        ];
    }
}