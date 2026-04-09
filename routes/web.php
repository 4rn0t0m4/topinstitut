<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EtablissementController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\VilleController;
use App\Http\Controllers\RechercheController;
use App\Http\Controllers\AvisController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InscriptionEtablissementController;
use App\Http\Controllers\PhoneController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Establishment detail pages (SEO-compatible .html suffix)
Route::get('/institut-de-beaute/{slug}.html', [EtablissementController::class, 'show'])->defaults('type', 0)->name('etablissement.show.institut');
Route::get('/estheticienne-a-domicile/{slug}.html', [EtablissementController::class, 'show'])->defaults('type', 1)->name('etablissement.show.estheticienne');
Route::get('/spa/{slug}.html', [EtablissementController::class, 'show'])->defaults('type', 2)->name('etablissement.show.spa');
Route::get('/thalasso/{slug}.html', [EtablissementController::class, 'show'])->defaults('type', 3)->name('etablissement.show.thalasso');

// Department & city pages
Route::get('/departement-{slug}.html', [DepartementController::class, 'show'])->name('departement.show');
Route::get('/les-instituts-de-beaute-a-{slug}.html', [VilleController::class, 'show'])->name('ville.show');

// Search
Route::get('/recherche_institut.html', [RechercheController::class, 'index'])->name('recherche');

// Phone reveal (audiotel)
Route::post('/ajax/phone', [PhoneController::class, 'reveal'])->name('phone.reveal');

// Reviews
Route::post('/avis', [AvisController::class, 'store'])->name('avis.store');
Route::get('/avis/confirmer/{token}', [AvisController::class, 'confirmerEmail'])->name('avis.confirmer');
Route::post('/ajax/avis-utile', [AvisController::class, 'toggleUtile'])->middleware('auth')->name('avis.utile');

// Establishment registration
Route::get('/ajouter-un-institut-de-beaute.html', [InscriptionEtablissementController::class, 'create'])->name('etablissement.create');
Route::post('/ajouter-un-institut-de-beaute', [InscriptionEtablissementController::class, 'store'])->name('etablissement.store');

// Contact
Route::get('/contact-top-institut.html', [ContactController::class, 'showGeneral'])->name('contact');
Route::post('/contact', [ContactController::class, 'sendGeneral'])->name('contact.send');
Route::get('/contact-etablissement/{etablissement}', [ContactController::class, 'showEtablissement'])->name('contact.etablissement');
Route::post('/contact-etablissement/{etablissement}', [ContactController::class, 'sendEtablissement'])->name('contact.etablissement.send');

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Static pages
Route::view('/mentions_legales.html', 'pages.mentions-legales')->name('mentions-legales');
Route::view('/confidentialite.html', 'pages.confidentialite')->name('confidentialite');
Route::view('/cgv.html', 'pages.cgv')->name('cgv');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login']);
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register']);
    Route::get('/mot-de-passe-oublie', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::get('/deconnexion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/validation/{id}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');
