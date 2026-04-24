<?php

use App\Http\Controllers\Admin\AvisController;
use App\Http\Controllers\Admin\CategorieController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EtablissementController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\RevendicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('etablissements', EtablissementController::class);
Route::post('etablissements/{etablissement}/valider', [EtablissementController::class, 'valider'])->name('etablissements.valider');

Route::get('avis', [AvisController::class, 'index'])->name('avis.index');
Route::get('avis/{avis}', [AvisController::class, 'show'])->name('avis.show');
Route::post('avis/{avis}/moderer', [AvisController::class, 'moderer'])->name('avis.moderer');

Route::resource('categories', CategorieController::class)->except(['show']);

Route::get('revendications', [RevendicationController::class, 'index'])->name('revendications.index');
Route::post('revendications/{revendication}/moderer', [RevendicationController::class, 'moderer'])->name('revendications.moderer');

Route::get('imports', [ImportController::class, 'index'])->name('imports.index');

// Recherche Google Places
Route::view('recherche-entreprises', 'admin.recherche-entreprises')->name('recherche-entreprises');
Route::prefix('pj')->name('pj.')->group(function () {
    Route::get('google', [\App\Http\Controllers\PagesJaunesController::class, 'googleSearch'])->name('google');
    Route::get('google/detail', [\App\Http\Controllers\PagesJaunesController::class, 'googleDetail'])->name('google.detail');
});
