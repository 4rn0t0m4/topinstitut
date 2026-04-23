<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->post('/connexion', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ])->assertSessionHasErrors('email');
    }

    public function test_user_can_register(): void
    {
        $email = 'test-register-'.uniqid().'@example.com';

        $this->post('/inscription', [
            'username' => 'test_'.uniqid(),
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => $email]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/deconnexion')->assertRedirect('/');
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_non_admin_is_forbidden_on_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
}
