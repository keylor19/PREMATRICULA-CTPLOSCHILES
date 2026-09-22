<?php

namespace Tests\Feature\Authorization;

use App\Models\Periodo;
use App\Models\Prematricula;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Solo el superadmin puede borrar un período de matrícula
 * (App\Http\Middleware\EsSuperAdmin, aplicado a la ruta periodos.destroy).
 * Un admin normal conserva el resto de sus permisos de siempre.
 */
class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    private function periodoSinMatriculas(): Periodo
    {
        return Periodo::create([
            'anio' => now()->year, 'nombre' => 'Período de prueba',
            'fecha_inicio' => now()->subDay(), 'fecha_fin' => now()->addDay(), 'activo' => false,
        ]);
    }

    public function test_admin_normal_no_puede_borrar_un_periodo(): void
    {
        $admin = User::factory()->admin()->create();
        $periodo = $this->periodoSinMatriculas();

        $response = $this->actingAs($admin)->delete("/admin/periodos/{$periodo->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('periodos', ['id' => $periodo->id]);
    }

    public function test_superadmin_si_puede_borrar_un_periodo_sin_matriculas(): void
    {
        $superadmin = User::factory()->superAdmin()->create();
        $periodo = $this->periodoSinMatriculas();

        $response = $this->actingAs($superadmin)->delete("/admin/periodos/{$periodo->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('periodos', ['id' => $periodo->id]);
    }

    public function test_docente_no_puede_borrar_un_periodo(): void
    {
        $docente = User::factory()->docente()->create();
        $periodo = $this->periodoSinMatriculas();

        $response = $this->actingAs($docente)->delete("/admin/periodos/{$periodo->id}");

        $response->assertForbidden();
    }

    public function test_superadmin_conserva_todos_los_permisos_de_admin(): void
    {
        $superadmin = User::factory()->superAdmin()->create();

        $this->actingAs($superadmin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($superadmin)->get('/admin/docentes')->assertOk();
        $this->actingAs($superadmin)->get('/admin/periodos')->assertOk();
    }

    public function test_boton_eliminar_periodo_solo_aparece_para_superadmin(): void
    {
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superAdmin()->create();
        $this->periodoSinMatriculas();

        $this->actingAs($admin)->get('/admin/periodos')->assertDontSee('Eliminar');
        $this->actingAs($superadmin)->get('/admin/periodos')->assertSee('Eliminar');
    }

    public function test_no_se_puede_crear_una_cuenta_superadmin_desde_el_formulario_de_docentes(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/docentes', [
            'name' => 'Intento', 'email' => 'intento@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'rol' => 'superadmin',
        ]);

        $response->assertSessionHasErrors('rol');
        $this->assertDatabaseMissing('users', ['email' => 'intento@example.test']);
    }

    public function test_un_admin_normal_no_puede_eliminar_la_cuenta_del_superadmin(): void
    {
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->delete("/admin/docentes/{$superadmin->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }

    public function test_no_se_puede_borrar_un_periodo_que_ya_tiene_prematriculas_ni_siquiera_el_superadmin(): void
    {
        $superadmin = User::factory()->superAdmin()->create();
        $periodo = $this->periodoSinMatriculas();

        \App\Models\Estudiante::create([
            'nombre' => 'X', 'apellido' => 'Y', 'cedula' => '999999999',
            'fecha_nacimiento' => '2010-01-01', 'direccion' => 'X',
        ]);
        $tutor = \App\Models\Tutor::create([
            'user_id' => $superadmin->id, 'nombre_completo' => 'T', 'relacion' => 'Madre',
            'cedula' => '888888888', 'telefono_principal' => '88888888', 'email' => 't@example.test',
        ]);
        Prematricula::create([
            'codigo' => Prematricula::generarCodigo(), 'user_id' => $superadmin->id, 'periodo_id' => $periodo->id,
            'estudiante_id' => \App\Models\Estudiante::first()->id, 'tutor_id' => $tutor->id,
            'colegio_procedencia' => 'X', 'anio_cursado_anterior' => '2024',
        ]);

        $response = $this->actingAs($superadmin)->delete("/admin/periodos/{$periodo->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('periodos', ['id' => $periodo->id]);
    }
}
