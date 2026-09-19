<?php

namespace Tests\Feature\Admin;

use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La exportación a Excel de las prematrículas contiene datos personales
 * de estudiantes y encargados: debe seguir detrás del middleware admin.
 */
class ExportExcelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_docente_cannot_export_excel(): void
    {
        $docente = User::factory()->docente()->create();
        $periodo = Periodo::create([
            'anio' => now()->year, 'nombre' => 'P', 'fecha_inicio' => now(), 'fecha_fin' => now(), 'activo' => true,
        ]);
        $modalidad = Modalidad::create(['nombre' => 'Diurna', 'activa' => true]);

        $response = $this->actingAs($docente)->get('/admin/prematriculas/exportar/excel?' . http_build_query([
            'periodo_id' => $periodo->id,
            'modalidad_id' => $modalidad->id,
        ]));

        $response->assertForbidden();
    }

    public function test_guest_cannot_export_excel(): void
    {
        $response = $this->get('/admin/prematriculas/exportar/excel');

        $response->assertRedirect('/login');
    }
}
