<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bot protection middleware combining 2 anti-spam techniques :
 *
 *  1. **Honeypot** : un champ caché (`website`) qui doit rester vide.
 *     Les bots remplissent automatiquement tous les champs → on les détecte.
 *
 *  2. **Timing** : un timestamp `form_t` envoyé à l'affichage. Si la
 *     soumission arrive < 3s après le rendu, c'est probablement un bot
 *     qui ne lit pas la page mais POST direct.
 *
 *  Les requêtes suspectes reçoivent 200 OK avec un faux flash success
 *  (pour ne pas signaler au bot qu'il a été détecté).
 */
class BotProtection
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Honeypot
        if ($request->filled('website')) {
            Log::info('Bot caught (honeypot)', [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
                'route' => $request->route()?->getName(),
            ]);

            return back()->with('success', 'Votre message a bien été envoyé.');
        }

        // 2. Timing (form_t = timestamp affichage en secondes Unix)
        $formTime = (int) $request->input('form_t', 0);
        if ($formTime > 0) {
            $elapsed = time() - $formTime;
            if ($elapsed < 3) {
                Log::info('Bot caught (timing)', [
                    'ip' => $request->ip(),
                    'elapsed' => $elapsed,
                    'route' => $request->route()?->getName(),
                ]);

                return back()->with('success', 'Votre message a bien été envoyé.');
            }
        }

        return $next($request);
    }
}
