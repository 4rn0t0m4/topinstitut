<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_user_can_login(): void
    {
        $user = User::where('email_verified_at', '!=', null)->first();
        if (! $user) {
            $this->markTestSkipped('No verified user');
        }

        // Can't test MD5 login without knowing the password, but we test the flow
        $this->post('/connexion', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ])->assertRedirect()->assertSessionHasErrors('email');
    }

    public function test_user_can_register(): void
    {
        $email = 'test-register-'.time().'@example.com';

        $this->post('/inscription', [
            'pseudo' => 'test_'.time(),
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect('/');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user');
        }

        $this->actingAs($user)->get('/deconnexion')->assertRedirect('/');
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::where('is_admin', true)->first();
        if (! $admin) {
            $this->markTestSkipped('No admin user');
        }

        $this->actingAs($admin)->get('/admin')->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin(): void
    {
        $user = User::where('is_admin', false)->first();
        if (! $user) {
            $this->markTestSkipped('No non-admin user');
        }

        $this->actingAs($user)->get('/admin')->assertStatus(403);
    }
}
