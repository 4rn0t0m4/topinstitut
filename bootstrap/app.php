<?php

use App\Http\Middleware\AdminMiddleware;
use App\Models\Etablissement;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', AdminMiddleware::class])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('espace-client')
                ->name('client.')
                ->group(base_path('routes/client.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, $exception, $request) {
            if ($response->getStatusCode() === 404 && ! $request->expectsJson()) {
                $suggestions = Etablissement::valide()
                    ->where('nb_avis', '>', 0)
                    ->orderByDesc('moyenne')
                    ->take(5)
                    ->get();

                return response()->view('errors.404', ['suggestions' => $suggestions], 404);
            }

            return $response;
        });
    })->create();
