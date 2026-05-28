<?php

use App\Http\Controllers\Client\AbonnementController;
use App\Http\Controllers\Client\ActualiteController;
use App\Http\Controllers\Client\AvisController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\EtablissementController;
use App\Http\Controllers\Client\PhotoController;
use App\Http\Controllers\Client\ProfilController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('profil', [ProfilController::class, 'edit'])->name('profil.edit');
Route::put('profil', [ProfilController::class, 'update'])->name('profil.update');

Route::prefix('etablissement/{etablissement}')->name('etablissement.')->scopeBindings()->group(function () {
    Route::get('/', [EtablissementController::class, 'edit'])->name('edit');
    Route::put('/', [EtablissementController::class, 'update'])->name('update');
    Route::get('presentation', [EtablissementController::class, 'editPresentation'])->name('presentation');
    Route::put('presentation', [EtablissementController::class, 'updatePresentation'])->name('presentation.update');
    Route::get('horaires', [EtablissementController::class, 'editHoraires'])->name('horaires');
    Route::put('horaires', [EtablissementController::class, 'updateHoraires'])->name('horaires.update');
    Route::get('localisation', [EtablissementController::class, 'editLocalisation'])->name('localisation');
    Route::put('localisation', [EtablissementController::class, 'updateLocalisation'])->name('localisation.update');

    Route::get('photos', [PhotoController::class, 'index'])->name('photos');
    Route::post('photos', [PhotoController::class, 'store'])->name('photos.store');
    Route::delete('photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
    Route::post('photos/reorder', [PhotoController::class, 'reorder'])->name('photos.reorder');

    Route::get('actualite', [ActualiteController::class, 'edit'])->name('actualite');
    Route::put('actualite', [ActualiteController::class, 'update'])->name('actualite.update');

    Route::get('avis', [AvisController::class, 'index'])->name('avis');
    Route::get('avis/{review}/repondre', [AvisController::class, 'repondre'])->name('avis.repondre');
    Route::post('avis/{review}/repondre', [AvisController::class, 'storeReponse'])->name('avis.reponse');

    Route::get('faq', [\App\Http\Controllers\Client\FaqController::class, 'index'])->name('faq');
    Route::post('faq', [\App\Http\Controllers\Client\FaqController::class, 'store'])->name('faq.store');
    Route::put('faq/{faq}', [\App\Http\Controllers\Client\FaqController::class, 'update'])->name('faq.update');
    Route::delete('faq/{faq}', [\App\Http\Controllers\Client\FaqController::class, 'destroy'])->name('faq.destroy');

    // Réservation en ligne — réservé aux établissements Premium
    Route::middleware('premium-establishment')->group(function () {
        Route::get('prestations', [\App\Http\Controllers\Client\ServicesController::class, 'edit'])->name('prestations');
        Route::put('prestations', [\App\Http\Controllers\Client\ServicesController::class, 'update'])->name('prestations.update');

        Route::get('praticiens', [\App\Http\Controllers\Client\PractitionerController::class, 'index'])->name('praticiens');
        Route::post('praticiens', [\App\Http\Controllers\Client\PractitionerController::class, 'store'])->name('praticiens.store');
        Route::put('praticiens/{practitioner}', [\App\Http\Controllers\Client\PractitionerController::class, 'update'])->name('praticiens.update');
        Route::delete('praticiens/{practitioner}', [\App\Http\Controllers\Client\PractitionerController::class, 'destroy'])->name('praticiens.destroy');
        Route::get('praticiens/{practitioner}/horaires', [\App\Http\Controllers\Client\PractitionerController::class, 'editSchedules'])->name('praticiens.horaires');
        Route::put('praticiens/{practitioner}/horaires', [\App\Http\Controllers\Client\PractitionerController::class, 'updateSchedules'])->name('praticiens.horaires.update');

        Route::get('agenda', [\App\Http\Controllers\Client\AppointmentController::class, 'index'])->name('agenda');
        Route::post('agenda/manuel', [\App\Http\Controllers\Client\AppointmentController::class, 'storeManual'])->name('agenda.manuel');
        Route::patch('agenda/{appointment}/statut', [\App\Http\Controllers\Client\AppointmentController::class, 'updateStatus'])->name('agenda.statut');
        Route::post('agenda/blocage', [\App\Http\Controllers\Client\AppointmentController::class, 'storeTimeOff'])->name('agenda.blocage');
        Route::delete('agenda/blocage/{practitioner}/{timeOff}', [\App\Http\Controllers\Client\AppointmentController::class, 'destroyTimeOff'])->name('agenda.blocage.destroy');
    });
});

Route::get('mes-avis', [AvisController::class, 'mesAvis'])->name('mes-avis');

Route::get('favoris', [\App\Http\Controllers\FavoriteController::class, 'index'])->name('favoris');

// Abonnements Premium (Stripe)
Route::get('abonnement', [AbonnementController::class, 'index'])->name('abonnement.index');
Route::post('abonnement/{establishment}/checkout', [AbonnementController::class, 'checkout'])->name('abonnement.checkout');
Route::get('abonnement/{establishment}/succes', [AbonnementController::class, 'success'])->name('abonnement.success');
Route::get('abonnement/portail', [AbonnementController::class, 'portal'])->name('abonnement.portal');
