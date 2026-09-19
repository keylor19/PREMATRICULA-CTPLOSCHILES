<?php

namespace Tests\Feature\Prematricula;

use App\Models\Estudiante;
use App\Models\Modalidad;
use App\Models\Nivel;
use App\Models\Periodo;
use App\Models\Prematricula;
use App\Models\Seccion;
use App\Models\SeccionTaller;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Flujo de "ratificar": al abrir un nuevo período, un docente puede traer
 * un estudiante que ya matriculó el período anterior y confirmar/actualizar
 * sus datos en vez de llenar todo de cero (PrematriculaController::ratificar*).
 */
class RatificacionTest extends TestCase
{
    use RefreshDatabase;

    private function prepararEscenario(): array
    {
        $modalidad = Modalidad::create(['nombre' => 'Diurna', 'activa' => true]);

        $periodoAnterior = Periodo::create([
            'anio' => now()->year - 1, 'nombre' => 'Prematrícula ' . (now()->year - 1),
            'fecha_inicio' => now()->subYear()->subDays(30)->toDateString(),
            'fecha_fin' => now()->subYear()->toDateString(),
            'activo' => false,
        ]);
        $periodoActivo = Periodo::create([
            'anio' => now()->year, 'nombre' => 'Prematrícula ' . now()->year,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDay()->toDateString(),
            'activo' => true,
        ]);

        $nivel7Anterior = Nivel::create([
            'numero' => '7', 'nombre' => '7° año', 'seccion_inicio' => 1, 'seccion_fin' => 1,
            'activo' => true, 'modalidad_id' => $modalidad->id, 'periodo_id' => $periodoAnterior->id,
        ]);
        $seccionAnterior = Seccion::create(['nivel_id' => $nivel7Anterior->id, 'nombre' => '7-1', 'numero' => 1, 'activa' => true]);
        SeccionTaller::create(['seccion_id' => $seccionAnterior->id, 'grupo' => 'A', 'nombre' => 'Turismo', 'capacidad' => 40]);
        SeccionTaller::create(['seccion_id' => $seccionAnterior->id, 'grupo' => 'B', 'nombre' => 'Contabilidad', 'capacidad' => 40]);

        // El nivel "siguiente" (8°) del período activo — el que debería sugerirse.
        $nivel8Activo = Nivel::create([
            'numero' => '8', 'nombre' => '8° año', 'seccion_inicio' => 1, 'seccion_fin' => 1,
            'activo' => true, 'modalidad_id' => $modalidad->id, 'periodo_id' => $periodoActivo->id,
        ]);
        $seccion8Activo = Seccion::create(['nivel_id' => $nivel8Activo->id, 'nombre' => '8-1', 'numero' => 1, 'activa' => true]);
        SeccionTaller::create(['seccion_id' => $seccion8Activo->id, 'grupo' => 'A', 'nombre' => 'Turismo', 'capacidad' => 1]);
        SeccionTaller::create(['seccion_id' => $seccion8Activo->id, 'grupo' => 'B', 'nombre' => 'Contabilidad', 'capacidad' => 40]);

        $docente = User::factory()->docente()->create();
        $docente->modalidades()->attach($modalidad->id);
        $otroDocente = User::factory()->docente()->create();
        $otroDocente->modalidades()->attach($modalidad->id);

        $estudiante = Estudiante::create([
            'nombre' => 'Juan', 'apellido' => 'Pérez', 'cedula' => '111111111',
            'fecha_nacimiento' => '2012-01-01', 'direccion' => 'Alajuela, Los Chiles, Los Chiles, Centro',
        ]);
        $tutor = Tutor::create([
            'user_id' => $docente->id, 'nombre_completo' => 'María Pérez', 'relacion' => 'Madre',
            'cedula' => '222222222', 'telefono_principal' => '88888888', 'email' => 'madre@example.test',
        ]);
        $prematriculaAnterior = Prematricula::create([
            'codigo' => Prematricula::generarCodigo(), 'user_id' => $docente->id, 'periodo_id' => $periodoAnterior->id,
            'estudiante_id' => $estudiante->id, 'tutor_id' => $tutor->id, 'nivel_id' => $nivel7Anterior->id,
            'seccion_id' => $seccionAnterior->id, 'grupo_taller' => 'A', 'modalidad_id' => $modalidad->id,
            'colegio_procedencia' => 'Escuela X', 'anio_cursado_anterior' => (string) (now()->year - 2),
        ]);
        $prematriculaAnterior->tutores()->attach($tutor->id, ['principal' => true, 'orden' => 1]);

        return compact(
            'modalidad', 'periodoAnterior', 'periodoActivo', 'nivel7Anterior', 'nivel8Activo',
            'seccion8Activo', 'docente', 'otroDocente', 'estudiante', 'tutor', 'prematriculaAnterior'
        );
    }

    public function test_docente_dueno_ve_al_estudiante_como_candidato_y_otro_docente_no(): void
    {
        $e = $this->prepararEscenario();

        $this->actingAs($e['docente'])->get('/prematricula/ratificar')
            ->assertOk()
            ->assertSee($e['estudiante']->cedula);

        $this->actingAs($e['otroDocente'])->get('/prematricula/ratificar')
            ->assertOk()
            ->assertDontSee($e['estudiante']->cedula);
    }

    public function test_docente_no_dueno_no_puede_abrir_el_formulario_de_ratificacion(): void
    {
        $e = $this->prepararEscenario();

        $this->actingAs($e['otroDocente'])
            ->get('/prematricula/ratificar/' . $e['estudiante']->id)
            ->assertForbidden();
    }

    public function test_formulario_sugiere_el_siguiente_nivel_y_precarga_los_datos_existentes(): void
    {
        $e = $this->prepararEscenario();

        $response = $this->actingAs($e['docente'])->get('/prematricula/ratificar/' . $e['estudiante']->id);

        $response->assertOk();
        // El nivel sugerido (8°) debe ir marcado por el script de preselección.
        $response->assertSee("nivelSelect.value = '" . $e['nivel8Activo']->id . "'", false);
        // Los datos del estudiante y del tutor se precargan vía flashInput/old().
        $response->assertSee('value="Juan"', false);
        $response->assertSee('value="Pérez"', false);
        $response->assertSee('value="111111111"', false);
        $response->assertSee('value="María Pérez"', false);
        $response->assertSee('value="madre@example.test"', false);
    }

    public function test_ratificar_crea_prematricula_nueva_sin_borrar_la_anterior_y_reutiliza_al_mismo_estudiante(): void
    {
        Mail::fake();
        $e = $this->prepararEscenario();

        $payload = [
            'est_nombre' => 'Juan', 'est_apellido' => 'Pérez', 'est_cedula' => '111111111',
            'est_nacimiento' => '2012-01-01', 'est_provincia' => 'Alajuela', 'est_canton' => 'Los Chiles',
            'est_distrito' => 'Los Chiles', 'est_poblado' => 'Centro', 'est_adecuacion' => 'No aplica',
            'nivel_id' => $e['nivel8Activo']->id, 'seccion_id' => $e['seccion8Activo']->id, 'grupo_taller' => 'B',
            'colegio_procedencia' => 'CTP Los Chiles', 'anio_cursado_anterior' => (string) (now()->year - 1),
            'modalidad_id' => $e['modalidad']->id,
            'fisico_cedula' => '1', 'fisico_notas' => '1', 'fisico_cedula_encargado' => '1',
            'tut_nombre' => 'María Pérez', 'tut_relacion' => 'Madre', 'tut_cedula' => '222222222',
            'tut_telefono' => '88888888', 'tut_email' => 'madre@example.test',
            'tut_provincia' => 'Alajuela', 'tut_canton' => 'Los Chiles', 'tut_distrito' => 'Los Chiles',
            'tut_poblado' => 'Centro', 'principal' => '1',
        ];

        $response = $this->actingAs($e['docente'])
            ->post('/prematricula/ratificar/' . $e['estudiante']->id, $payload);

        $response->assertRedirect(route('prematricula.index'));

        $this->assertSame(1, Estudiante::count(), 'no debe crear un estudiante duplicado');
        $this->assertSame(2, Prematricula::count(), 'debe quedar la del período anterior + la nueva');
        $this->assertDatabaseHas('prematriculas', [
            'estudiante_id' => $e['estudiante']->id,
            'periodo_id' => $e['periodoActivo']->id,
            'nivel_id' => $e['nivel8Activo']->id,
        ]);
        $this->assertDatabaseHas('prematriculas', [
            'id' => $e['prematriculaAnterior']->id,
            'periodo_id' => $e['periodoAnterior']->id,
        ]);
    }

    public function test_ratificar_respeta_el_cupo_disponible(): void
    {
        Mail::fake();
        $e = $this->prepararEscenario(); // taller A del nivel 8 activo tiene capacidad 1

        // Ocupa el único cupo del grupo A con otra prematrícula ya existente en el período activo.
        $otroEstudiante = Estudiante::create([
            'nombre' => 'Otro', 'apellido' => 'Estudiante', 'cedula' => '333333333',
            'fecha_nacimiento' => '2011-01-01', 'direccion' => 'Alajuela',
        ]);
        $otroTutor = Tutor::create([
            'user_id' => $e['docente']->id, 'nombre_completo' => 'Otro Tutor', 'relacion' => 'Padre',
            'cedula' => '444444444', 'telefono_principal' => '87777777', 'email' => 'otro@example.test',
        ]);
        Prematricula::create([
            'codigo' => Prematricula::generarCodigo(), 'user_id' => $e['docente']->id, 'periodo_id' => $e['periodoActivo']->id,
            'estudiante_id' => $otroEstudiante->id, 'tutor_id' => $otroTutor->id, 'nivel_id' => $e['nivel8Activo']->id,
            'seccion_id' => $e['seccion8Activo']->id, 'grupo_taller' => 'A', 'modalidad_id' => $e['modalidad']->id,
            'colegio_procedencia' => 'X', 'anio_cursado_anterior' => '2024',
        ]);

        $payload = [
            'est_nombre' => 'Juan', 'est_apellido' => 'Pérez', 'est_cedula' => '111111111',
            'est_nacimiento' => '2012-01-01', 'est_provincia' => 'Alajuela', 'est_canton' => 'Los Chiles',
            'est_distrito' => 'Los Chiles', 'est_poblado' => 'Centro', 'est_adecuacion' => 'No aplica',
            'nivel_id' => $e['nivel8Activo']->id, 'seccion_id' => $e['seccion8Activo']->id, 'grupo_taller' => 'A',
            'colegio_procedencia' => 'CTP Los Chiles', 'anio_cursado_anterior' => (string) (now()->year - 1),
            'modalidad_id' => $e['modalidad']->id,
            'fisico_cedula' => '1', 'fisico_notas' => '1', 'fisico_cedula_encargado' => '1',
            'tut_nombre' => 'María Pérez', 'tut_relacion' => 'Madre', 'tut_cedula' => '222222222',
            'tut_telefono' => '88888888', 'tut_email' => 'madre@example.test',
            'tut_provincia' => 'Alajuela', 'tut_canton' => 'Los Chiles', 'tut_distrito' => 'Los Chiles',
            'tut_poblado' => 'Centro', 'principal' => '1',
        ];

        $response = $this->actingAs($e['docente'])
            ->post('/prematricula/ratificar/' . $e['estudiante']->id, $payload);

        $response->assertSessionHasErrors('grupo_taller');
        $this->assertSame(2, Prematricula::count(), 'no debe crear la prematrícula si el grupo ya está lleno');
    }
}
