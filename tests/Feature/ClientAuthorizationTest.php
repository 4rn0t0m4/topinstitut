<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function premiumEstablishment(): Establishment
    {
        return Establishment::factory()->create([
            'subscription_tier' => 'premium',
            'subscription_ends_at' => null,
        ]);
    }

    public function test_non_owner_cannot_access_prestations_edit(): void
    {
        $user = User::factory()->create();
        $establishment = $this->premiumEstablishment();

        $this->actingAs($user)
            ->get('/espace-client/etablissement/'.$establishment->id.'/prestations')
            ->assertForbidden();
    }

    public function test_owner_can_access_prestations_edit(): void
    {
        $owner = User::factory()->create();
        $establishment = $this->premiumEstablishment();
        $owner->establishments()->attach($establishment->id);

        $this->actingAs($owner)
            ->get('/espace-client/etablissement/'.$establishment->id.'/prestations')
            ->assertOk();
    }

    public function test_non_owner_cannot_update_services(): void
    {
        $user = User::factory()->create();
        $establishment = $this->premiumEstablishment();

        $this->actingAs($user)
            ->put('/espace-client/etablissement/'.$establishment->id.'/prestations', [
                'services' => [['name' => 'X', 'price' => '10€']],
            ])
            ->assertForbidden();
    }

    public function test_owner_can_update_services(): void
    {
        $owner = User::factory()->create();
        $establishment = $this->premiumEstablishment();
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

    public function test_owner_can_create_category_and_assign_service(): void
    {
        $owner = User::factory()->create();
        $establishment = $this->premiumEstablishment();
        $owner->establishments()->attach($establishment->id);

        $this->actingAs($owner)
            ->put('/espace-client/etablissement/'.$establishment->id.'/prestations', [
                'categories' => [
                    ['cid' => 'new1', 'id' => null, 'name' => 'Épilation', 'description' => 'Zones du corps'],
                ],
                'services' => [
                    ['id' => null, 'name' => 'Sourcils', 'category_cid' => 'new1', 'duration_minutes' => 10, 'price' => '9', 'is_bookable' => 1],
                ],
            ])
            ->assertRedirect();

        $establishment->refresh();
        $category = $establishment->serviceCategories->first();
        $this->assertNotNull($category, 'La catégorie devrait être créée');
        $this->assertSame('Épilation', $category->name);
        $this->assertSame('Zones du corps', $category->description);

        $service = $establishment->services->first();
        $this->assertSame($category->id, $service->service_category_id, 'La prestation devrait être liée à la catégorie');
    }

    public function test_empty_string_ids_like_real_form(): void
    {
        $owner = User::factory()->create();
        $establishment = $this->premiumEstablishment();
        $owner->establishments()->attach($establishment->id);

        $resp = $this->actingAs($owner)
            ->put('/espace-client/etablissement/'.$establishment->id.'/prestations', [
                'categories' => [
                    ['cid' => 'new1', 'id' => '', 'name' => 'Épilation', 'description' => ''],
                ],
                'services' => [
                    ['id' => '', 'name' => 'Sourcils', 'category_cid' => 'new1', 'duration_minutes' => '10', 'price' => '9', 'is_bookable' => '1'],
                ],
            ]);
        $resp->assertSessionHasNoErrors();
        $resp->assertRedirect();
        $this->assertSame('Épilation', $establishment->fresh()->serviceCategories->first()?->name);
    }

    public function test_non_premium_owner_is_redirected_to_subscription(): void
    {
        $owner = User::factory()->create();
        $establishment = Establishment::factory()->create(['subscription_tier' => 'free']);
        $owner->establishments()->attach($establishment->id);

        $this->actingAs($owner)
            ->get('/espace-client/etablissement/'.$establishment->id.'/prestations')
            ->assertRedirect('/espace-client/abonnement');
    }

    public function test_start_trial_grants_one_month_premium(): void
    {
        $establishment = Establishment::factory()->create(['subscription_tier' => 'free']);

        $this->assertFalse($establishment->is_premium);
        $this->assertTrue($establishment->startTrialIfEligible());

        $establishment->refresh();
        $this->assertTrue($establishment->is_premium);
        $this->assertTrue($establishment->is_in_trial);
        $this->assertNotNull($establishment->trial_started_at);
        $this->assertGreaterThan(now()->addDays(28), $establishment->subscription_ends_at);
        $this->assertLessThan(now()->addDays(32), $establishment->subscription_ends_at);

        // Pas de second essai
        $this->assertFalse($establishment->fresh()->startTrialIfEligible());
    }
}
