<?php

use App\Http\Controllers\Admin\AvisController;
use App\Http\Controllers\Admin\CategorieController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EtablissementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('etablissements', EtablissementController::class);
Route::post('etablissements/{etablissement}/valider', [EtablissementController::class, 'valider'])->name('etablissements.valider');

Route::get('avis', [AvisController::class, 'index'])->name('avis.index');
Route::get('avis/{avis}', [AvisController::class, 'show'])->name('avis.show');
Route::post('avis/{avis}/moderer', [AvisController::class, 'moderer'])->name('avis.moderer');

Route::resource('categories', CategorieController::class)->except(['show']);
