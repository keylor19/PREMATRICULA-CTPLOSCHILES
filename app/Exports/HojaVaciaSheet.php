<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HojaVaciaSheet implements FromArray, WithTitle, WithStyles
{
    public function title(): string
    {
        return 'Sin resultados';
    }

    public function array(): array
    {
        return [
            ['No se encontraron prematrículas para el período y modalidad seleccionados.']
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => '92400e']]],
        ];
    }
}