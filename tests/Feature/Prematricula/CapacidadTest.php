<?php

namespace Tests\Feature\Prematricula;

use App\Models\Estudiante;
use App\Models\Modalidad;
use App\Models\Nivel;
use App\Models\Periodo;
use App\Models\Prematricula;
use App\Models\Seccion;
use App\Models\SeccionTaller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * PrematriculaController::store verifica el cupo disponible de un
 * taller/carrera y luego crea el registro. Ese "verificar y luego crear"
 * es una condición de carrera clásica (TOCTOU): sin bloqueo, dos envíos
 * simultáneos para el último cupo podrían pasar ambos la verificación.
 * El fix bloquea la fila del taller (lockForUpdate) dentro de una
 * transacción que envuelve toda la creación. Esta prueba no puede simular
 * dos peticiones HTTP concurrentes reales, pero sí confirma el
 * comportamiento observable: una vez lleno el cupo, ningún envío
 * posterior logra crear una prematrícula de más, y no queda un
 * Estudiante/Tutor huérfano de un intento fallido.
 */
class CapacidadTest extends TestCase
{
    use RefreshDatabase;

    private function prepararEscenario(int $capacidad = 1): array
    {
        $modalidad = Modalidad::create(['nombre' => 'Diurna', 'activa' => true]);

        $periodo = Periodo::create([
            'anio'         => now()->year,
            'nombre'       => 'Prematrícula ' . now()->year,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin'    => now()->addDay()->toDateString(),
            'activo'       => true,
        ]);

        $nivel = Nivel::create([
            'numero'         => '7',
            'nombre'         => 'Sétimo año',
            'seccion_inicio' => 1,
            'seccion_fin'    => 1,
            'activo'         => true,
            'modalidad_id'   => $modalidad->id,
            'periodo_id'     => $periodo->id,
        ]);

        $seccion = Seccion::create([
            'nivel_id' => $nivel->id,
            'nombre'   => '7-1',
            'numero'   => 1,
            'activa'   => true,
        ]);

        $taller = SeccionTaller::create([
            'seccion_id' => $seccion->id,
            'grupo'      => 'A',
            'nombre'     => 'Turismo',
            'capacidad'  => $capacidad,
        ]);

        return compact('modalidad', 'periodo', 'nivel', 'seccion', 'taller');
    }

    private function payload(array $escenario, string $cedula): array
    {
        return [
            'est_nombre'             => 'Estudiante',
            'est_apellido'           => 'Prueba',
            'est_cedula'             => $cedula,
            'est_nacimiento'         => '2012-01-01',
            'est_provincia'          => 'Alajuela',
            'est_canton'             => 'Los Chiles',
            'est_distrito'           => 'Los Chiles',
            'est_poblado'            => 'Centro',
            'est_adecuacion'         => 'No aplica',
            'nivel_id'               => $escenario['nivel']->id,
            'seccion_id'             => $escenario['seccion']->id,
            'grupo_taller'           => 'A',
            'colegio_procedencia'    => 'Escuela X',
            'anio_cursado_anterior'  => '2025',
            'modalidad_id'           => $escenario['modalidad']->id,
            'fisico_cedula'          => '1',
            'fisico_notas'           => '1',
            'fisico_cedula_encargado' => '1',
            'tut_nombre'    => 'Tutor',
            'tut_relacion'  => 'Madre',
            'tut_cedula'    => (string) fake()->unique()->numberBetween(100000000, 999999999),
            'tut_telefono'  => '88888888',
            'tut_email'     => 'tutor@example.com',
            'tut_provincia' => 'Alajuela',
            'tut_canton'    => 'Los Chiles',
            'tut_distrito'  => 'Los Chiles',
            'tut_poblado'   => 'Centro',
            'principal'     => '1',
        ];
    }

    public function test_puede_matricularse_mientras_hay_cupo(): void
    {
        Mail::fake();
        $escenario = $this->prepararEscenario(capacidad: 1);
        $docente = User::factory()->docente()->create();

        $response = $this->actingAs($docente)->post('/prematricula', $this->payload($escenario, '111111111'));

        $response->assertRedirect(route('prematricula.index'));
        $this->assertSame(1, Prematricula::count());
        $this->assertSame(1, Estudiante::count());
    }

    public function test_no_se_puede_matricular_cuando_el_taller_ya_esta_lleno(): void
    {
        Mail::fake();
        $escenario = $this->prepararEscenario(capacidad: 1);
        $docente = User::factory()->docente()->create();

        // Primer envío: ocupa el único cupo disponible.
        $this->actingAs($docente)->post('/prematricula', $this->payload($escenario, '111111111'));
        $this->assertSame(1, Prematricula::count());

        // Segundo envío (otro estudiante) para el mismo grupo: ya no hay cupo.
        $response = $this->actingAs($docente)->post('/prematricula', $this->payload($escenario, '222222222'));

        $response->assertSessionHasErrors('grupo_taller');

        // No debe haberse creado una segunda prematrícula...
        $this->assertSame(1, Prematricula::count());
        // ...ni un Estudiante huérfano del intento que falló por cupo lleno
        // (la verificación de cupo ocurre ANTES de crear cualquier registro,
        // dentro de la misma transacción bloqueada).
        $this->assertSame(1, Estudiante::count());
        $this->assertDatabaseMissing('estudiantes', ['cedula' => '222222222']);
    }
}
