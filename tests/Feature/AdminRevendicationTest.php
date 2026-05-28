<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AdminRevendicationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_refuse_marks_claim_as_rejected(): void
    {
        $claim = Claim::create([
            'establishment_id' => Establishment::factory()->create()->id,
            'email' => 'pro@example.com',
            'manager_name' => 'Pro',
            'status' => 'pending',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/revendications/'.$claim->id.'/moderer', ['action' => 'refuser'])
            ->assertRedirect('/admin/revendications');

        $this->assertSame('rejected', $claim->fresh()->status);
    }

    public function test_approve_attaches_owner_and_starts_trial(): void
    {
        $owner = User::factory()->create();
        $establishment = Establishment::factory()->create(['subscription_tier' => 'free']);
        $claim = Claim::create([
            'establishment_id' => $establishment->id,
            'user_id' => $owner->id,
            'email' => $owner->email,
            'manager_name' => 'Le gérant',
            'status' => 'pending',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/revendications/'.$claim->id.'/moderer', ['action' => 'approuver'])
            ->assertRedirect('/admin/revendications');

        $claim->refresh();
        $establishment->refresh();

        $this->assertSame('approved', $claim->status);
        $this->assertTrue($establishment->owners->contains($owner->id), 'Le propriétaire doit être attaché');
        $this->assertTrue($establishment->is_premium, 'Premium doit être activé via le trial');
        $this->assertTrue($establishment->is_in_trial, 'L\'établissement doit être en période d\'essai');
        $this->assertNotNull($establishment->trial_started_at);
    }

    public function test_approve_creates_user_when_none_exists(): void
    {
        Notification::fake();
        // Empêche l'appel réseau Password::sendResetLink
        Password::shouldReceive('sendResetLink')->once()->andReturn(Password::RESET_LINK_SENT);

        $establishment = Establishment::factory()->create(['subscription_tier' => 'free']);
        $claim = Claim::create([
            'establishment_id' => $establishment->id,
            'user_id' => null,
            'email' => 'nouveau@example.com',
            'manager_name' => 'Nouveau Pro',
            'status' => 'pending',
            'email_verified_at' => now(),
        ]);

        $this->assertSame(0, User::where('email', 'nouveau@example.com')->count());

        $this->actingAs($this->admin())
            ->post('/admin/revendications/'.$claim->id.'/moderer', ['action' => 'approuver'])
            ->assertRedirect('/admin/revendications');

        $user = User::where('email', 'nouveau@example.com')->first();
        $this->assertNotNull($user, 'Un compte doit avoir été créé');
        $this->assertSame('Nouveau Pro', $user->first_name);
        $this->assertNotNull($user->email_verified_at);

        $claim->refresh();
        $this->assertSame($user->id, $claim->user_id);
        $this->assertTrue($establishment->fresh()->owners->contains($user->id));
        $this->assertTrue($establishment->fresh()->is_in_trial);
    }

    public function test_approve_reuses_existing_user_when_email_matches(): void
    {
        Password::shouldReceive('sendResetLink')->zeroOrMoreTimes(); // ne devrait pas être appelée
        $existing = User::factory()->create(['email' => 'deja@example.com']);

        $establishment = Establishment::factory()->create(['subscription_tier' => 'free']);
        $claim = Claim::create([
            'establishment_id' => $establishment->id,
            'user_id' => null,
            'email' => 'deja@example.com',
            'manager_name' => 'Pro existant',
            'status' => 'pending',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/revendications/'.$claim->id.'/moderer', ['action' => 'approuver'])
            ->assertRedirect('/admin/revendications');

        $this->assertSame(2, User::count(), 'Pas de nouveau compte créé (un admin + un existant)');
        $this->assertSame($existing->id, $claim->fresh()->user_id);
        $this->assertTrue($establishment->fresh()->owners->contains($existing->id));
    }

    public function test_approve_does_not_restart_trial_if_already_used(): void
    {
        $owner = User::factory()->create();
        $past = now()->subDays(40);
        $establishment = Establishment::factory()->create([
            'subscription_tier' => 'free',
            'trial_started_at' => $past,
            'subscription_ends_at' => $past->copy()->addMonth(), // essai terminé
        ]);
        $claim = Claim::create([
            'establishment_id' => $establishment->id,
            'user_id' => $owner->id,
            'email' => $owner->email,
            'manager_name' => 'X',
            'status' => 'pending',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/revendications/'.$claim->id.'/moderer', ['action' => 'approuver']);

        $establishment->refresh();
        // Pas de nouveau Premium offert (trial déjà consommé).
        $this->assertFalse($establishment->is_premium, 'L\'établissement doit rester non-Premium');
        $this->assertNotNull($establishment->trial_started_at, 'trial_started_at préservé');
        $this->assertGreaterThan(20, $establishment->trial_started_at->diffInDays(now()), 'trial_started_at non remplacé par now()');
    }
}
