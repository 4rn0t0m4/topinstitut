<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvisController;
use App\Http\Controllers\CategorieAutocompleteController;
use App\Http\Controllers\ComparerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\EtablissementController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InscriptionEtablissementController;
use App\Http\Controllers\PhoneController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\PrestationVilleController;
use App\Http\Controllers\RechercheController;
use App\Http\Controllers\RevendicationController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\VilleAutocompleteController;
use App\Http\Controllers\VilleController;
use App\Models\Department;
use App\Models\Establishment;
use Illuminate\Support\Facades\Route;

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Search
Route::get('/recherche', [RechercheController::class, 'index'])->name('recherche');

// Comparator
Route::get('/comparer', [ComparerController::class, 'index'])->name('comparer');

// Quiz
Route::get('/quiz', [QuizController::class, 'index'])->name('quiz');
Route::post('/quiz', [QuizController::class, 'submit'])->middleware('throttle:20,1')->name('quiz.submit');

// Guides éditoriaux
Route::get('/guides', [GuideController::class, 'index'])->name('guides.index');
Route::get('/guides/{slug}', [GuideController::class, 'show'])->name('guides.show');

// Establishment registration
Route::get('/ajouter-un-institut-de-beaute', [InscriptionEtablissementController::class, 'create'])->name('etablissement.create');
Route::post('/ajouter-un-institut-de-beaute', [InscriptionEtablissementController::class, 'store'])->middleware('bot')->name('etablissement.store');

// Contact
Route::get('/contact', [ContactController::class, 'showGeneral'])->name('contact');
Route::post('/contact', [ContactController::class, 'sendGeneral'])->middleware(['bot', 'throttle:5,1'])->name('contact.send');
Route::get('/contact-etablissement/{establishment}', [ContactController::class, 'showEstablishment'])->name('contact.etablissement');
Route::post('/contact-etablissement/{establishment}', [ContactController::class, 'sendEstablishment'])->middleware(['bot', 'throttle:5,1'])->name('contact.etablissement.send');

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Static pages
Route::view('/mentions-legales', 'pages.mentions-legales')->name('mentions-legales');
Route::view('/confidentialite', 'pages.confidentialite')->name('confidentialite');
Route::view('/cgv', 'pages.cgv')->name('cgv');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::get('/mot-de-passe-oublie', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::get('/deconnexion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/validation/{id}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');

// Autocomplete & AJAX
Route::get('/ajax/villes', VilleAutocompleteController::class)->name('villes.autocomplete');
Route::get('/ajax/categories', CategorieAutocompleteController::class)->name('categories.autocomplete');
Route::post('/ajax/phone', [PhoneController::class, 'reveal'])->name('phone.reveal');
Route::post('/ajax/avis-utile', [AvisController::class, 'toggleHelpful'])->middleware('auth')->name('avis.utile');

// Reviews
Route::post('/avis', [AvisController::class, 'store'])->middleware(['bot', 'throttle:5,1'])->name('avis.store');
Route::get('/avis/confirmer/{token}', [AvisController::class, 'confirmEmail'])->name('review.confirm');

// Claim ownership (open to guests, email verification required)
Route::post('/revendiquer/{establishment}', [RevendicationController::class, 'store'])->middleware(['bot', 'throttle:3,1'])->name('revendication.store');
Route::get('/revendication/confirmer/{token}', [RevendicationController::class, 'confirmEmail'])->name('revendication.confirm');

// Booking
Route::post('/rdv/{establishment}', [\App\Http\Controllers\BookingController::class, 'store'])->middleware(['bot', 'throttle:5,1'])->name('booking.store');

// Favorites
Route::post('/ajax/favorites/{establishment}', [\App\Http\Controllers\FavoriteController::class, 'toggle'])->name('favorites.toggle');

// Legacy .html redirects — MUST be before the establishment detail routes
// (otherwise /spa/xxx.html would match /spa/{slug} with slug="xxx.html")
Route::get('/institut-de-beaute/{slug}.html', fn ($slug) => redirect("/institut-de-beaute/$slug", 301));
Route::get('/estheticienne-a-domicile/{slug}.html', fn ($slug) => redirect("/estheticienne-a-domicile/$slug", 301));
Route::get('/spa/{slug}.html', fn ($slug) => redirect("/spa/$slug", 301));
Route::get('/thalasso/{slug}.html', fn ($slug) => redirect("/thalasso/$slug", 301));
Route::get('/recherche_institut.html', fn () => redirect('/recherche', 301));
Route::get('/recherche-institut', fn () => redirect('/recherche', 301));
Route::get('/ajouter-un-institut-de-beaute.html', fn () => redirect('/ajouter-un-institut-de-beaute', 301));
Route::get('/contact-top-institut.html', fn () => redirect('/contact', 301));
Route::get('/mentions_legales.html', fn () => redirect('/mentions-legales', 301));
Route::get('/confidentialite.html', fn () => redirect('/confidentialite', 301));
Route::get('/cgv.html', fn () => redirect('/cgv', 301));

// Establishment detail pages (2 segments, literal prefix matches before /{dept}/{city})
Route::get('/institut-de-beaute/{slug}', [EtablissementController::class, 'show'])->defaults('type', 0)->name('etablissement.show.institut');
Route::get('/estheticienne-a-domicile/{slug}', [EtablissementController::class, 'show'])->defaults('type', 1)->name('etablissement.show.estheticienne');
Route::get('/spa/{slug}', [EtablissementController::class, 'show'])->defaults('type', 2)->name('etablissement.show.spa');
Route::get('/thalasso/{slug}', [EtablissementController::class, 'show'])->defaults('type', 3)->name('etablissement.show.thalasso');

// Legacy department URL: /departement-{slug} → /{slug}
Route::get('/departement-{slug}', function ($slug) {
    return redirect("/$slug", 301);
});
Route::get('/departement-{slug}.html', fn ($slug) => redirect("/$slug", 301));

// Legacy city URL: /les-instituts-de-beaute-a-{slug} → /{dept}/{city}
Route::get('/les-instituts-de-beaute-a-{slug}', function ($slug) {
    $city = \App\Models\City::where('slug', $slug)->with('department')->first();
    if (! $city || ! $city->department) abort(404);
    return redirect("/{$city->department->slug}/{$city->slug}", 301);
});
Route::get('/les-instituts-de-beaute-a-{slug}.html', function ($slug) {
    $city = \App\Models\City::where('slug', $slug)->with('department')->first();
    if (! $city || ! $city->department) abort(404);
    return redirect("/{$city->department->slug}/{$city->slug}", 301);
});

// Legacy prestation×ville: /{prestation}-a-{city} → /{dept}/{city}/{prestation}
Route::get('/{slug}', function ($slug) {
    if (! preg_match('/^([a-z0-9-]+)-a-([a-z0-9-]+)$/', $slug)) abort(404);

    // Try every split at -a-
    $offset = 0;
    while (($pos = strpos($slug, '-a-', $offset)) !== false) {
        $prestationSlug = substr($slug, 0, $pos);
        $citySlug = substr($slug, $pos + 3);
        $city = \App\Models\City::where('slug', $citySlug)->with('department')->first();
        if ($city && $city->department) {
            return redirect("/{$city->department->slug}/{$city->slug}/{$prestationSlug}", 301);
        }
        $offset = $pos + 1;
    }
    abort(404);
})->where('slug', '[a-z0-9-]+-a-[a-z0-9-]+');

// ── Hierarchical routes: /{dept} → /{dept}/{city} → /{dept}/{city}/{prestation} → /{dept}/{city}/{type}/{slug} ──
// Placed last so literal routes match first.

// Reserved slugs that must not match {dept} (handled by admin.php, client.php, or other)
Route::pattern('dept', '(?!(?:admin|espace-client|up|deploy|test\.php|api)(?:$|/))[a-z][a-z0-9-]*');

Route::get('/{dept}/{city}/{type}/{slug}', [EtablissementController::class, 'showHierarchical'])
    ->where('type', 'institut-de-beaute|estheticienne-a-domicile|spa|thalasso')
    ->name('etablissement.show');

Route::get('/{dept}/{city}/{prestation}', PrestationVilleController::class)
    ->name('prestation.ville');

Route::get('/{dept}/{city}', [VilleController::class, 'show'])->name('ville.show');

Route::get('/{dept}', [DepartementController::class, 'show'])->name('departement.show');
