<?php

namespace App\Services;

use App\Models\Prematricula;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Tcpdf\Fpdi;

class BoletaPdfBuilder
{
    /**
     * Genera la boleta con DomPDF y le adjunta como páginas
     * adicionales los documentos digitales presentados.
     * Devuelve la ruta relativa dentro del disco 'local'.
     */
    public static function generar(Prematricula $prematricula): string
    {
        // 1. Generar la boleta principal
        $pdfBoleta = Pdf::loadView('pdf.prematricula', compact('prematricula'))
            ->setPaper('letter', 'portrait');

        $tempBoleta = storage_path('app/temp_boleta_' . $prematricula->id . '.pdf');
        file_put_contents($tempBoleta, $pdfBoleta->output());

        // 2. Preparar el documento combinado
        $merger = new Fpdi();
        $merger->setPrintHeader(false);
        $merger->setPrintFooter(false);

        // Importar las páginas de la boleta
        $paginas = $merger->setSourceFile($tempBoleta);
        for ($i = 1; $i <= $paginas; $i++) {
            $tplId = $merger->importPage($i);
            $size  = $merger->getTemplateSize($tplId);
            $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $merger->useTemplate($tplId);
        }

        // 3. Adjuntar los documentos digitales (no los marcados "en físico")
        $etiquetas = [
            'cedula_estudiante' => 'Cédula de identidad del estudiante',
            'cedula_encargado'  => 'Cédula del encargado legal',
            'notas'             => 'Certificado de notas del año anterior',
            'foto'              => 'Fotografía reciente del estudiante',
            'prueba_admision'   => 'Certificado de prueba de admisión',
        ];

        foreach ($prematricula->documentos as $doc) {
            if ($doc->entregado_fisico || empty($doc->ruta)) {
                continue;
            }

            $rutaCompleta = storage_path('app/private/' . $doc->ruta);
            if (!file_exists($rutaCompleta)) {
                continue;
            }

            $extension = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
            $titulo    = $etiquetas[$doc->tipo] ?? 'Documento adjunto';

            try {
                if ($extension === 'pdf') {
                    $paginasDoc = $merger->setSourceFile($rutaCompleta);
                    for ($i = 1; $i <= $paginasDoc; $i++) {
                        $tplId = $merger->importPage($i);
                        $size  = $merger->getTemplateSize($tplId);
                        $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $merger->useTemplate($tplId);
                    }
                } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $merger->AddPage('P', 'LETTER');
                    $merger->SetFont('dejavusans', 'B', 11);
                    $merger->SetXY(10, 12);
                    $merger->Cell(0, 8, $titulo, 0, 1);
                    $anchoDisponible = $merger->getPageWidth() - 20;
                    $merger->Image($rutaCompleta, 10, 25, $anchoDisponible, 0, '', '', '', true, 300, '', false, false, 0, true);
                }
            } catch (\Exception $e) {
                // Si un documento puntual falla, seguimos con los demás
                continue;
            }
        }

        // 4. Guardar el PDF final combinado
        $rutaFinal = 'pdfs/' . $prematricula->codigo . '.pdf';
        Storage::put($rutaFinal, $merger->Output('', 'S'));

        @unlink($tempBoleta);

        return $rutaFinal;
    }
}