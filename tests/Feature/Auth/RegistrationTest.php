<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El registro público está deshabilitado a propósito: las cuentas de
 * docentes las crea el administrador desde el panel interno (ver
 * routes/auth.php). Estas pruebas confirman que esa decisión se mantiene.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_not_available(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_users_cannot_self_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(404);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
    }
}
