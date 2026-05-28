<?php

namespace App\Http\Middleware;

use App\Models\Establishment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePremiumEstablishment
{
    public function handle(Request $request, Closure $next): Response
    {
        $etab = $request->route('etablissement');

        if ($etab instanceof Establishment && ! $etab->is_premium) {
            return redirect()
                ->route('client.abonnement.index')
                ->with('premium_required', 'Cette fonctionnalité (planning, prestations, praticiens) est réservée aux établissements Premium.');
        }

        return $next($request);
    }
}
