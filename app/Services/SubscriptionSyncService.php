<?php

namespace App\Services;

use App\Models\Establishment;
use Illuminate\Support\Facades\Log;

/**
 * Synchronise l'état d'abonnement Premium d'un établissement avec Stripe
 * (déclenché par les webhooks).
 */
class SubscriptionSyncService
{
    public function __construct(private StripeService $stripe) {}

    /**
     * Checkout terminé : active Premium pour l'établissement référencé en metadata.
     */
    public function onCheckoutCompleted(object $session): void
    {
        if ($session->mode !== 'subscription' || ! $session->subscription) {
            return;
        }

        $sub = $this->stripe->client()->subscriptions->retrieve($session->subscription);

        $establishmentId = (int) ($sub->metadata->establishment_id ?? 0);
        if (! $establishmentId) {
            Log::warning('Stripe checkout completed sans establishment_id', ['session' => $session->id]);

            return;
        }

        Establishment::where('id', $establishmentId)->update([
            'subscription_tier' => 'premium',
            'subscription_ends_at' => $sub->current_period_end
                ? now()->createFromTimestamp($sub->current_period_end)
                : null,
            'stripe_subscription_id' => $sub->id,
            'is_verified_owner' => true,
        ]);
    }

    /**
     * Subscription créée (par checkout ou directement dans le dashboard Stripe).
     * Requiert metadata.establishment_id sur la subscription.
     */
    public function onSubscriptionCreated(object $sub): void
    {
        $establishmentId = (int) ($sub->metadata->establishment_id ?? 0);

        if (! $establishmentId) {
            Log::info('Stripe subscription.created sans establishment_id metadata', [
                'subscription' => $sub->id,
                'customer' => $sub->customer ?? null,
            ]);

            return;
        }

        $etab = Establishment::find($establishmentId);
        if (! $etab) {
            Log::warning('Stripe subscription.created : etablissement introuvable', [
                'establishment_id' => $establishmentId,
                'subscription' => $sub->id,
            ]);

            return;
        }

        $isActive = in_array($sub->status, ['active', 'trialing']);

        $etab->update([
            'subscription_tier' => $isActive ? 'premium' : 'free',
            'subscription_ends_at' => $sub->current_period_end
                ? now()->createFromTimestamp($sub->current_period_end)
                : null,
            'stripe_subscription_id' => $sub->id,
            'is_verified_owner' => $isActive ? true : $etab->is_verified_owner,
        ]);

        // Lier le customer Stripe à l'owner si pas encore fait.
        if ($sub->customer ?? null) {
            $owner = $etab->owners()->whereNull('stripe_customer_id')->first();
            if ($owner) {
                $owner->update(['stripe_customer_id' => $sub->customer]);
            }
        }
    }

    public function onSubscriptionUpdated(object $sub): void
    {
        $etab = Establishment::where('stripe_subscription_id', $sub->id)->first();
        if (! $etab) {
            return;
        }

        $isActive = in_array($sub->status, ['active', 'trialing']);

        $etab->update([
            'subscription_tier' => $isActive ? 'premium' : 'free',
            'subscription_ends_at' => $sub->current_period_end
                ? now()->createFromTimestamp($sub->current_period_end)
                : null,
        ]);
    }

    public function onSubscriptionDeleted(object $sub): void
    {
        Establishment::where('stripe_subscription_id', $sub->id)->update([
            'subscription_tier' => 'free',
            'subscription_ends_at' => now(),
        ]);
    }
}
