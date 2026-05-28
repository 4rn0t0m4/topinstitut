<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\User;
use App\Services\StripeService;
use App\Services\SubscriptionSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\StripeClient;
use Tests\TestCase;

class SubscriptionSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private function sync(): SubscriptionSyncService
    {
        // Les méthodes onSubscriptionCreated/Updated/Deleted n'appellent pas Stripe :
        // un mock vide suffit. onCheckoutCompleted utilise son propre mock par test.
        return new SubscriptionSyncService(Mockery::mock(StripeService::class));
    }

    private function fakeSub(array $overrides = []): object
    {
        return (object) array_merge([
            'id' => 'sub_test_123',
            'status' => 'active',
            'current_period_end' => now()->addMonth()->timestamp,
            'customer' => 'cus_test_42',
            'metadata' => (object) ['establishment_id' => 0],
        ], $overrides);
    }

    public function test_subscription_created_activates_premium_when_status_active(): void
    {
        $etab = Establishment::factory()->create(['subscription_tier' => 'free']);

        $sub = $this->fakeSub(['metadata' => (object) ['establishment_id' => $etab->id]]);
        $this->sync()->onSubscriptionCreated($sub);

        $etab->refresh();
        $this->assertTrue($etab->is_premium);
        $this->assertSame('sub_test_123', $etab->stripe_subscription_id);
        $this->assertTrue($etab->is_verified_owner);
        // ~1 mois dans le futur (tolérance pour la conversion timezone DB).
        $this->assertGreaterThan(now()->addDays(20), $etab->subscription_ends_at);
        $this->assertLessThan(now()->addDays(40), $etab->subscription_ends_at);
    }

    public function test_subscription_created_without_metadata_is_ignored(): void
    {
        $etab = Establishment::factory()->create(['subscription_tier' => 'free']);

        $sub = $this->fakeSub(['metadata' => (object) []]);
        $this->sync()->onSubscriptionCreated($sub);

        $this->assertSame('free', $etab->fresh()->subscription_tier);
    }

    public function test_subscription_created_with_unknown_establishment_is_ignored(): void
    {
        $sub = $this->fakeSub(['metadata' => (object) ['establishment_id' => 99999]]);

        // Ne lève pas d'exception
        $this->sync()->onSubscriptionCreated($sub);
        $this->assertTrue(true);
    }

    public function test_subscription_created_with_inactive_status_marks_free(): void
    {
        $etab = Establishment::factory()->create(['subscription_tier' => 'free']);

        $sub = $this->fakeSub([
            'status' => 'incomplete_expired',
            'metadata' => (object) ['establishment_id' => $etab->id],
        ]);
        $this->sync()->onSubscriptionCreated($sub);

        $this->assertSame('free', $etab->fresh()->subscription_tier);
    }

    public function test_subscription_created_links_customer_to_owner_without_one(): void
    {
        $owner = User::factory()->create(['stripe_customer_id' => null]);
        $etab = Establishment::factory()->create();
        $etab->owners()->attach($owner->id);

        $sub = $this->fakeSub(['metadata' => (object) ['establishment_id' => $etab->id]]);
        $this->sync()->onSubscriptionCreated($sub);

        $this->assertSame('cus_test_42', $owner->fresh()->stripe_customer_id);
    }

    public function test_subscription_updated_extends_subscription_end(): void
    {
        $etab = Establishment::factory()->create([
            'subscription_tier' => 'premium',
            'stripe_subscription_id' => 'sub_to_update',
            'subscription_ends_at' => now()->subDay(),
        ]);

        $sub = $this->fakeSub([
            'id' => 'sub_to_update',
            'current_period_end' => now()->addDays(30)->timestamp,
        ]);
        $this->sync()->onSubscriptionUpdated($sub);

        $etab->refresh();
        $this->assertTrue($etab->is_premium);
        $this->assertGreaterThan(now()->addDays(20), $etab->subscription_ends_at);
    }

    public function test_subscription_updated_with_canceled_status_downgrades(): void
    {
        $etab = Establishment::factory()->create([
            'subscription_tier' => 'premium',
            'stripe_subscription_id' => 'sub_canceled',
            'subscription_ends_at' => now()->addMonth(),
        ]);

        $sub = $this->fakeSub(['id' => 'sub_canceled', 'status' => 'canceled']);
        $this->sync()->onSubscriptionUpdated($sub);

        $this->assertSame('free', $etab->fresh()->subscription_tier);
    }

    public function test_subscription_updated_noop_when_no_matching_establishment(): void
    {
        $sub = $this->fakeSub(['id' => 'sub_unknown']);
        $this->sync()->onSubscriptionUpdated($sub);
        $this->assertTrue(true); // ne plante pas
    }

    public function test_subscription_deleted_marks_free_and_ends_now(): void
    {
        $etab = Establishment::factory()->create([
            'subscription_tier' => 'premium',
            'stripe_subscription_id' => 'sub_deleted',
            'subscription_ends_at' => now()->addMonth(),
        ]);

        $this->sync()->onSubscriptionDeleted($this->fakeSub(['id' => 'sub_deleted']));

        $etab->refresh();
        $this->assertSame('free', $etab->subscription_tier);
        $this->assertLessThan(now()->addMinute(), $etab->subscription_ends_at);
    }

    public function test_checkout_completed_retrieves_subscription_and_activates_premium(): void
    {
        $etab = Establishment::factory()->create(['subscription_tier' => 'free']);

        $fakeSub = $this->fakeSub([
            'metadata' => (object) ['establishment_id' => $etab->id],
        ]);

        // Mock du StripeClient (typé) avec sa propriété publique `subscriptions`.
        $subsApi = Mockery::mock();
        $subsApi->shouldReceive('retrieve')->with('sub_test_123')->andReturn($fakeSub);
        $client = Mockery::mock(StripeClient::class);
        $client->subscriptions = $subsApi;

        $stripe = Mockery::mock(StripeService::class);
        $stripe->shouldReceive('client')->andReturn($client);

        $session = (object) ['id' => 'cs_test_999', 'mode' => 'subscription', 'subscription' => 'sub_test_123'];

        (new SubscriptionSyncService($stripe))->onCheckoutCompleted($session);

        $etab->refresh();
        $this->assertTrue($etab->is_premium);
        $this->assertSame('sub_test_123', $etab->stripe_subscription_id);
        $this->assertTrue($etab->is_verified_owner);
    }

    public function test_checkout_completed_ignores_non_subscription_modes(): void
    {
        $etab = Establishment::factory()->create(['subscription_tier' => 'free']);
        $session = (object) ['id' => 'cs_payment', 'mode' => 'payment', 'subscription' => null];

        $this->sync()->onCheckoutCompleted($session); // ne doit rien faire
        $this->assertSame('free', $etab->fresh()->subscription_tier);
    }
}
