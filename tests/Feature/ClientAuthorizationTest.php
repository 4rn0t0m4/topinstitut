<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_owner_cannot_access_prestations_edit(): void
    {
        $user = User::factory()->create();
        $establishment = Establishment::factory()->create();

        $this->actingAs($user)
            ->get('/espace-client/etablissement/'.$establishment->id.'/prestations')
            ->assertForbidden();
    }

    public function test_owner_can_access_prestations_edit(): void
    {
        $owner = User::factory()->create();
        $establishment = Establishment::factory()->create();
        $owner->establishments()->attach($establishment->id);

        $this->actingAs($owner)
            ->get('/espace-client/etablissement/'.$establishment->id.'/prestations')
            ->assertOk();
    }

    public function test_non_owner_cannot_update_services(): void
    {
        $user = User::factory()->create();
        $establishment = Establishment::factory()->create();

        $this->actingAs($user)
            ->put('/espace-client/etablissement/'.$establishment->id.'/prestations', [
                'services' => [['name' => 'X', 'price' => '10€']],
            ])
            ->assertForbidden();
    }

    public function test_owner_can_update_services(): void
    {
        $owner = User::factory()->create();
        $establishment = Establishment::factory()->create();
        $owner->establishments()->attach($establishment->id);

        $this->actingAs($owner)
            ->put('/espace-client/etablissement/'.$establishment->id.'/prestations', [
                'services' => [['name' => 'Manucure', 'price' => '25€', 'duration_minutes' => 30]],
            ])
            ->assertRedirect();

        $service = $establishment->fresh()->services->first();
        $this->assertSame('Manucure', $service->name);
        $this->assertSame(30, $service->duration_minutes);
    }
}
