<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Establishment;
use App\Models\User;
use App\Services\ClaimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClaimServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClaimService $claims;

    private Establishment $establishment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->claims = app(ClaimService::class);
        $this->establishment = Establishment::factory()->create();
        Mail::fake();
    }

    public function test_is_already_owner_returns_false_for_guest(): void
    {
        $this->assertFalse($this->claims->isAlreadyOwner(null, $this->establishment));
    }

    public function test_is_already_owner_detects_existing_link(): void
    {
        $user = User::factory()->create();
        $this->establishment->owners()->attach($user->id);

        $this->assertTrue($this->claims->isAlreadyOwner($user, $this->establishment));
    }

    public function test_is_already_owner_returns_false_when_no_link(): void
    {
        $this->assertFalse($this->claims->isAlreadyOwner(User::factory()->create(), $this->establishment));
    }

    public function test_has_pending_claim_detects_existing_pending(): void
    {
        Claim::create([
            'establishment_id' => $this->establishment->id,
            'email' => 'pro@example.com',
            'manager_name' => 'Pro',
            'status' => 'pending',
        ]);

        $this->assertTrue($this->claims->hasPendingClaim($this->establishment, 'pro@example.com'));
        $this->assertFalse($this->claims->hasPendingClaim($this->establishment, 'autre@example.com'));
    }

    public function test_has_pending_claim_ignores_rejected_or_approved(): void
    {
        Claim::create([
            'establishment_id' => $this->establishment->id,
            'email' => 'pro@example.com',
            'manager_name' => 'Pro',
            'status' => 'rejected',
        ]);

        $this->assertFalse($this->claims->hasPendingClaim($this->establishment, 'pro@example.com'));
    }

    public function test_submit_as_logged_in_user_with_own_email_marks_verified_and_notifies_admin(): void
    {
        $user = User::factory()->create(['email' => 'pro@example.com']);

        $message = $this->claims->submit($this->establishment, $user, [
            'manager_name' => 'Pro',
            'email' => 'pro@example.com',
        ]);

        $claim = Claim::first();
        $this->assertNotNull($claim);
        $this->assertNotNull($claim->email_verified_at, 'Email auto-vérifié si correspond au compte');
        $this->assertNull($claim->verification_token);
        $this->assertStringContainsString('été envoyée', $message);

    }

    public function test_submit_with_different_email_requires_verification(): void
    {
        $user = User::factory()->create(['email' => 'compte@example.com']);

        $message = $this->claims->submit($this->establishment, $user, [
            'manager_name' => 'Pro',
            'email' => 'institut@example.com', // différent du compte
        ]);

        $claim = Claim::first();
        $this->assertNull($claim->email_verified_at, 'Doit exiger une vérification email');
        $this->assertNotNull($claim->verification_token);
        $this->assertStringContainsString('email de confirmation', $message);

    }

    public function test_submit_as_guest_creates_unverified_claim(): void
    {
        $message = $this->claims->submit($this->establishment, null, [
            'manager_name' => 'Pro',
            'email' => 'invite@example.com',
        ]);

        $claim = Claim::first();
        $this->assertNull($claim->user_id);
        $this->assertNull($claim->email_verified_at);
        $this->assertNotNull($claim->verification_token);
        $this->assertStringContainsString('invite@example.com', $message);
    }

    public function test_verify_email_marks_claim_and_notifies_admin(): void
    {
        $claim = Claim::create([
            'establishment_id' => $this->establishment->id,
            'email' => 'pro@example.com',
            'manager_name' => 'Pro',
            'status' => 'pending',
            'verification_token' => 'abc123',
        ]);

        $this->claims->verifyEmail($claim);

        $claim->refresh();
        $this->assertNotNull($claim->email_verified_at);
        $this->assertNull($claim->verification_token);

    }
}
