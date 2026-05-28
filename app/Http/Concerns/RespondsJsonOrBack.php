<?php

namespace App\Http\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Réponse symétrique JSON / redirect-with-errors pour les contrôleurs qui servent
 * à la fois des formulaires classiques et des appels AJAX/fetch.
 */
trait RespondsJsonOrBack
{
    /**
     * Erreur 422 (validation) → JSON conforme à Laravel ou back-with-errors.
     *
     * @param  array<string, string|array<string>>|string  $errors  champ→message ou message global (clé 'message')
     */
    protected function errorJsonOrBack(Request $request, array|string $errors, ?string $message = null): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $errorsArr = is_string($errors) ? ['message' => [$errors]] : array_map(
            fn ($v) => is_array($v) ? $v : [$v],
            $errors
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message ?? (is_string($errors) ? $errors : reset($errorsArr)[0]),
                'errors' => $errorsArr,
            ], 422);
        }

        return back()->withInput()->withErrors(array_map(fn ($v) => $v[0], $errorsArr));
    }

    /**
     * Succès → JSON ou back-with-success flash.
     */
    protected function successJsonOrBack(Request $request, string $message, array $extra = []): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(array_merge(['success' => true, 'message' => $message], $extra));
        }

        return back()->with('success', $message);
    }
}
