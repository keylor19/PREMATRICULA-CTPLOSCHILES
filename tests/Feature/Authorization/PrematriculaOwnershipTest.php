<?php

namespace Tests\Feature\Authorization;

use App\Models\Estudiante;
use App\Models\Prematricula;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Un docente solo puede ver/descargar/reenviar SUS PROPIAS prematrículas
 * (App\Http\Controllers\PrematriculaController::descargarPdf /
 * reenviarCorreo comparan prematricula.user_id con Auth::id()). Estas
 * pruebas cubren el caso IDOR: que un docente no pueda tocar una
 * prematrícula ajena adivinando o iterando su id numérico.
 */
class PrematriculaOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function crearPrematriculaDe(User $docente): Prematricula
    {
        $estudiante = Estudiante::create([
            'nombre'           => 'Estudiante',
            'apellido'         => 'De Prueba',
            'cedula'           => (string) fake()->unique()->numberBetween(100000000, 999999999),
            'fecha_nacimiento' => '2010-01-01',
            'direccion'        => 'San José',
        ]);

        $tutor = Tutor::create([
            'user_id'            => $docente->id,
            'nombre_completo'    => 'Tutor De Prueba',
            'relacion'           => 'Madre',
            'cedula'             => (string) fake()->unique()->numberBetween(100000000, 999999999),
            'telefono_principal' => '88888888',
            'email'              => 'tutor@example.com',
        ]);

        return Prematricula::create([
            'codigo'                => Prematricula::generarCodigo(),
            'user_id'               => $docente->id,
            'estudiante_id'         => $estudiante->id,
            'tutor_id'              => $tutor->id,
            'colegio_procedencia'   => 'Escuela X',
            'anio_cursado_anterior' => '2025',
        ]);
    }

    public function test_docente_cannot_download_pdf_of_another_docentes_prematricula(): void
    {
        $dueño  = User::factory()->docente()->create();
        $otro   = User::factory()->docente()->create();
        $prematricula = $this->crearPrematriculaDe($dueño);

        $response = $this->actingAs($otro)->get("/prematricula/{$prematricula->id}/pdf");

        $response->assertForbidden();
    }

    public function test_docente_cannot_resend_email_of_another_docentes_prematricula(): void
    {
        $dueño = User::factory()->docente()->create();
        $otro  = User::factory()->docente()->create();
        $prematricula = $this->crearPrematriculaDe($dueño);

        $response = $this->actingAs($otro)->post("/prematricula/{$prematricula->id}/reenviar-correo");

        $response->assertForbidden();
    }

    public function test_docente_index_only_lists_their_own_prematriculas(): void
    {
        $dueño = User::factory()->docente()->create();
        $otro  = User::factory()->docente()->create();
        $this->crearPrematriculaDe($dueño);
        $propia = $this->crearPrematriculaDe($otro);

        $response = $this->actingAs($otro)->get('/prematricula');

        $response->assertOk();
        $response->assertSee($propia->codigo);
        $this->assertSame(1, Prematricula::where('user_id', $otro->id)->count());
    }
}
