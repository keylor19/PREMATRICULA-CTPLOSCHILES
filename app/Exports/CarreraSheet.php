<?php

namespace App\Exports;

use App\Models\Prematricula;
use App\Models\Carrera;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CarreraSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        public Carrera $carrera,
        public int $periodoId,
        public int $modalidadId
    ) {}

    public function title(): string
    {
        $titulo = $this->carrera->nivel->nombre . ' - ' . $this->carrera->nombre;
        return substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $titulo), 0, 31);
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
            'Carrera técnica',
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
            ->where('carrera_id', $this->carrera->id)
            ->with(['estudiante', 'tutor', 'tutores', 'nivel', 'carrera', 'documentos', 'carreraSegundaOpcion'])
            ->orderBy('created_at')
            ->get();

        return $prematriculas->map(function ($p, $i) {
            $encargados = $p->tutores->count() > 0 ? $p->tutores : collect([$p->tutor]);
            $enc2 = $encargados->get(1);
            $enc3 = $encargados->get(2);

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
                $p->carrera->nombre ?? '—',
                $p->carreraSegundaOpcion?->nombre ?? '—',
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
            'G' => 10, 'H' => 22, 'I' => 26, 'J' => 14, 'K' => 20, 'L' => 20,
            'M' => 28, 'N' => 24, 'O' => 14, 'P' => 14, 'Q' => 26, 'R' => 22,
            'S' => 14, 'T' => 22, 'U' => 14, 'V' => 35, 'W' => 12, 'X' => 18,
        ];
    }
}