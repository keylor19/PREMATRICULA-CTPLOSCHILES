<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Todo /admin/* debe estar detrás del middleware `admin`
 * (App\Http\Middleware\EsAdmin). Un docente autenticado no es
 * suficiente: solo rol=admin puede entrar.
 */
class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_docente_gets_403_from_admin_dashboard(): void
    {
        $docente = User::factory()->docente()->create();

        $response = $this->actingAs($docente)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
    }

    public function test_docente_gets_403_from_docentes_management(): void
    {
        $docente = User::factory()->docente()->create();

        $response = $this->actingAs($docente)->get('/admin/docentes');

        $response->assertForbidden();
    }

    public function test_docente_cannot_create_other_users_via_admin_route(): void
    {
        $docente = User::factory()->docente()->create();

        $response = $this->actingAs($docente)->post('/admin/docentes', [
            'name'                  => 'Nuevo',
            'email'                 => 'nuevo@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'rol'                   => 'admin',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'nuevo@example.com']);
    }

    public function test_admin_created_docente_gets_exactly_the_requested_rol(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/docentes', [
            'name'                  => 'Nueva Docente',
            'email'                 => 'docente.nueva@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'rol'                   => 'admin',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'docente.nueva@example.com',
            'rol'   => 'admin',
        ]);
    }
}
