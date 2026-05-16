<?php

namespace App\Services;

use App\Models\Establishment;
use App\Models\User;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Customer;
use Stripe\StripeClient;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function client(): StripeClient
    {
        return $this->stripe;
    }

    /**
     * Récupère ou crée le Customer Stripe pour cet utilisateur.
     * Si l'ID stocké n'existe plus côté Stripe (ex : bascule live↔test), recrée.
     */
    public function ensureCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            try {
                $existing = $this->stripe->customers->retrieve($user->stripe_customer_id);
                if (! ($existing->deleted ?? false)) {
                    return $user->stripe_customer_id;
                }
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // Customer introuvable dans ce mode → on recrée plus bas
            }
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->username,
            'metadata' => ['user_id' => $user->id],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    /**
     * Crée une session de checkout pour l'abonnement Premium d'un établissement.
     */
    public function createPremiumCheckout(User $user, Establishment $establishment, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $customerId = $this->ensureCustomer($user);
        $priceId = config('services.stripe.premium_price_id');

        return $this->stripe->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $customerId,
            'line_items' => [['price' => $priceId, 'quantity' => 1]],
            'success_url' => $successUrl.'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'subscription_data' => [
                'metadata' => [
                    'establishment_id' => $establishment->id,
                    'user_id' => $user->id,
                ],
            ],
            'allow_promotion_codes' => true,
        ]);
    }

    /**
     * Crée une session billing portal pour gérer l'abonnement (modification CB, annulation, factures).
     */
    public function createBillingPortal(User $user, string $returnUrl): string
    {
        $customerId = $this->ensureCustomer($user);

        $session = $this->stripe->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);

        return $session->url;
    }
}
