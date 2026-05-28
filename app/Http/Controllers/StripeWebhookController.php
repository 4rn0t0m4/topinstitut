<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(private SubscriptionSyncService $sync) {}

    public function handle(Request $request)
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret')
            );
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook invalid signature: '.$e->getMessage());

            return response('Invalid', 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->sync->onCheckoutCompleted($event->data->object),
            'customer.subscription.created' => $this->sync->onSubscriptionCreated($event->data->object),
            'customer.subscription.updated' => $this->sync->onSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->sync->onSubscriptionDeleted($event->data->object),
            default => null,
        };

        return response('OK', 200);
    }
}
