<?php

namespace Tests\Unit;

use App\Models\Establishment;
use App\Models\User;
use App\Policies\EstablishmentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstablishmentPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_any_establishment(): void
    {
        $admin = User::factory()->admin()->create();
        $establishment = Establishment::factory()->create();

        $this->assertTrue((new EstablishmentPolicy)->manage($admin, $establishment));
    }

    public function test_owner_can_manage_their_establishment(): void
    {
        $owner = User::factory()->create();
        $establishment = Establishment::factory()->create();
        $owner->establishments()->attach($establishment->id);

        $this->assertTrue((new EstablishmentPolicy)->manage($owner, $establishment));
    }

    public function test_non_owner_cannot_manage(): void
    {
        $user = User::factory()->create();
        $establishment = Establishment::factory()->create();

        $this->assertFalse((new EstablishmentPolicy)->manage($user, $establishment));
    }
}
