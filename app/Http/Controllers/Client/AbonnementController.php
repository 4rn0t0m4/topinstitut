<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Services\StripeService;
use Illuminate\Http\Request;

class AbonnementController extends Controller
{
    public function index(Request $request)
    {
        $establishments = $request->user()->establishments()->with([])->get();

        return view('client.abonnement.index', compact('establishments'));
    }

    public function checkout(Request $request, Establishment $establishment, StripeService $stripe)
    {
        $this->authorizeOwner($request, $establishment);

        if (! config('services.stripe.secret') || ! config('services.stripe.premium_price_id')) {
            return back()->withErrors(['stripe' => 'Le paiement Stripe n\'est pas encore configuré. Contactez-nous pour souscrire.']);
        }

        $session = $stripe->createPremiumCheckout(
            $request->user(),
            $establishment,
            route('client.abonnement.success', $establishment),
            route('client.abonnement.index'),
        );

        return redirect($session->url);
    }

    public function success(Request $request, Establishment $establishment)
    {
        $this->authorizeOwner($request, $establishment);

        // L'activation réelle se fait via le webhook (checkout.session.completed) — ici on remercie juste.
        return view('client.abonnement.success', compact('establishment'));
    }

    public function portal(Request $request, StripeService $stripe)
    {
        if (! $request->user()->stripe_customer_id) {
            return back()->withErrors(['stripe' => 'Aucun abonnement Stripe lié à votre compte.']);
        }

        $url = $stripe->createBillingPortal($request->user(), route('client.abonnement.index'));

        return redirect($url);
    }

    private function authorizeOwner(Request $request, Establishment $establishment): void
    {
        if (! $establishment->owners()->where('user_id', $request->user()->id)->exists()) {
            abort(403, 'Vous n\'êtes pas propriétaire de cet établissement.');
        }
    }
}
