<?php

use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\LogController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DisponibiliteController;
use App\Http\Controllers\Api\MedecinController;
use App\Http\Controllers\Api\RendezVousController;
use App\Http\Controllers\Api\SpecialiteController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques (visiteur non connecté)
|--------------------------------------------------------------------------
| Consultation libre de l'annuaire médical, comme prévu au cahier des
| charges : "le client pourra consulter la liste des médecins" sans
| obligation de compte. La réservation, elle, exige une authentification.
*/
Route::post('/inscription', [AuthController::class, 'register']);
Route::post('/connexion', [AuthController::class, 'login']);
Route::post('/mot-de-passe/oublie', [AuthController::class, 'forgotPassword']);

Route::get('/specialites', [SpecialiteController::class, 'index']);
Route::get('/specialites/{specialite}', [SpecialiteController::class, 'show']);

Route::get('/medecins', [MedecinController::class, 'index']);
Route::get('/medecins/{medecin}', [MedecinController::class, 'show']);
Route::get('/medecins/{medecin}/creneaux', [MedecinController::class, 'creneauxDisponibles']);

/*
|--------------------------------------------------------------------------
| Routes authentifiées (tous rôles confondus)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/deconnexion', [AuthController::class, 'logout']);
    Route::get('/moi', [AuthController::class, 'me']);
    Route::put('/mon-profil', [AuthController::class, 'updateProfile']);
    Route::put('/mon-mot-de-passe', [AuthController::class, 'updatePassword']);

    /*
    |----------------------------------------------------------------
    | Espace Patient
    |----------------------------------------------------------------
    */
    Route::middleware('role:patient')->prefix('patient')->group(function () {
        Route::get('/rendez-vous', [RendezVousController::class, 'mesRendezVous']);
        Route::post('/rendez-vous', [RendezVousController::class, 'store']);
        Route::post('/rendez-vous/{rendezVous}/annuler', [RendezVousController::class, 'annuler']);
        Route::get('/rendez-vous/{rendezVous}/ticket', [TicketController::class, 'telecharger']);
    });

    /*
    |----------------------------------------------------------------
    | Espace Médecin
    |----------------------------------------------------------------
    */
    Route::middleware('role:medecin')->prefix('medecin')->group(function () {
        Route::get('/rendez-vous', [RendezVousController::class, 'rendezVousMedecin']);
        Route::post('/rendez-vous/{rendezVous}/repondre', [RendezVousController::class, 'repondre']);

        Route::get('/disponibilites', [DisponibiliteController::class, 'index']);
        Route::put('/disponibilites', [DisponibiliteController::class, 'synchroniser']);
        Route::post('/indisponibilites', [DisponibiliteController::class, 'bloquer']);
        Route::delete('/indisponibilites/{indisponibilite}', [DisponibiliteController::class, 'debloquer']);
    });

    /*
    |----------------------------------------------------------------
    | Espace Secrétaire / Agent d'accueil
    |----------------------------------------------------------------
    | Peut créer un RDV au nom d'un patient et imprimer les tickets,
    | sans accès aux logs système ni aux statistiques globales.
    */
    Route::middleware('role:secretaire,admin')->prefix('secretariat')->group(function () {
        Route::post('/rendez-vous', [RendezVousController::class, 'store']);
        Route::get('/rendez-vous/{rendezVous}/ticket', [TicketController::class, 'telecharger']);
    });

    /*
    |----------------------------------------------------------------
    | Espace Administrateur — accès complet (cahier des charges §4.1)
    |----------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/tableau-de-bord', [DashboardController::class, 'index']);
        Route::get('/export/rendez-vous', [DashboardController::class, 'exportRendezVous']);

        // Utilisateurs : voir/ajouter/modifier/bloquer/supprimer tout compte
        Route::get('/utilisateurs', [UserController::class, 'index']);
        Route::post('/utilisateurs', [UserController::class, 'store']);
        Route::get('/utilisateurs/{user}', [UserController::class, 'show']);
        Route::put('/utilisateurs/{user}', [UserController::class, 'update']);
        Route::patch('/utilisateurs/{user}/bloquer', [UserController::class, 'toggleBlock']);
        Route::patch('/utilisateurs/{user}/role', [UserController::class, 'changeRole']);
        Route::delete('/utilisateurs/{user}', [UserController::class, 'destroy']);

        // Médecins
        Route::post('/medecins', [MedecinController::class, 'store']);
        Route::patch('/medecins/{medecin}/bloquer', [MedecinController::class, 'toggleBlock']);

        // Spécialités (types de médecins)
        Route::post('/specialites', [SpecialiteController::class, 'store']);
        Route::put('/specialites/{specialite}', [SpecialiteController::class, 'update']);
        Route::delete('/specialites/{specialite}', [SpecialiteController::class, 'destroy']);

        // Supervision de tous les rendez-vous de la plateforme
        Route::get('/rendez-vous', [RendezVousController::class, 'tousLesRendezVous']);

        // Journal des logs (traçabilité complète)
        Route::get('/logs', [LogController::class, 'index']);
    });
});
