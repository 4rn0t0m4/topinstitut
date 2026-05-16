<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook invalid signature: '.$e->getMessage());

            return response('Invalid', 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->onCheckoutCompleted($event->data->object),
            'customer.subscription.created' => $this->onSubscriptionCreated($event->data->object),
            'customer.subscription.updated' => $this->onSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->onSubscriptionDeleted($event->data->object),
            default => null,
        };

        return response('OK', 200);
    }

    private function onCheckoutCompleted($session): void
    {
        if ($session->mode !== 'subscription' || ! $session->subscription) {
            return;
        }

        $subscriptionId = $session->subscription;
        $client = app(\App\Services\StripeService::class)->client();
        $sub = $client->subscriptions->retrieve($subscriptionId);

        $establishmentId = (int) ($sub->metadata->establishment_id ?? 0);
        if (! $establishmentId) {
            Log::warning('Stripe checkout completed sans establishment_id', ['session' => $session->id]);
            return;
        }

        Establishment::where('id', $establishmentId)->update([
            'subscription_tier' => 'premium',
            'subscription_ends_at' => $sub->current_period_end ? now()->createFromTimestamp($sub->current_period_end) : null,
            'stripe_subscription_id' => $subscriptionId,
            'is_verified_owner' => true,
        ]);
    }

    /**
     * Subscription créée directement dans le dashboard Stripe (vente manuelle).
     * Requiert metadata.establishment_id sur la subscription.
     */
    private function onSubscriptionCreated($sub): void
    {
        $establishmentId = (int) ($sub->metadata->establishment_id ?? 0);

        if (! $establishmentId) {
            Log::info('Stripe subscription.created sans establishment_id metadata', [
                'subscription' => $sub->id,
                'customer' => $sub->customer,
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
            'subscription_ends_at' => $sub->current_period_end ? now()->createFromTimestamp($sub->current_period_end) : null,
            'stripe_subscription_id' => $sub->id,
            'is_verified_owner' => $isActive ? true : $etab->is_verified_owner,
        ]);

        // Lier le customer Stripe à l'owner si pas encore fait
        if ($sub->customer) {
            $owner = $etab->owners()->whereNull('stripe_customer_id')->first();
            if ($owner) {
                $owner->update(['stripe_customer_id' => $sub->customer]);
            }
        }
    }

    private function onSubscriptionUpdated($sub): void
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

    private function onSubscriptionDeleted($sub): void
    {
        Establishment::where('stripe_subscription_id', $sub->id)->update([
            'subscription_tier' => 'free',
            'subscription_ends_at' => now(),
        ]);
    }
}
