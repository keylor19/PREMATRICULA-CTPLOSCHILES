<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `rol` decide si un usuario es admin o docente (ver App\Http\Middleware\EsAdmin).
 * No debe poder asignarse por mass assignment: si algún día alguien escribe
 * User::create($request->all()) o $user->update($request->all()), un usuario
 * cualquiera no debe poder auto-otorgarse el rol admin metiendo el campo
 * "rol" en el formulario.
 */
class UserMassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_rol_is_not_mass_assignable_on_create(): void
    {
        $user = User::create([
            'name'     => 'Cualquiera',
            'email'    => 'cualquiera@example.com',
            'password' => 'password',
            'rol'      => 'admin',
        ]);

        $this->assertSame('docente', $user->fresh()->rol);
    }

    public function test_rol_is_not_mass_assignable_on_update(): void
    {
        $user = User::factory()->docente()->create();

        $user->update(['rol' => 'admin']);

        $this->assertSame('docente', $user->fresh()->rol);
    }

    public function test_rol_can_still_be_set_explicitly(): void
    {
        $user = User::factory()->docente()->create();

        $user->rol = 'admin';
        $user->save();

        $this->assertTrue($user->fresh()->esAdmin());
    }
}
