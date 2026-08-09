<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Disponibilite;
use App\Models\Indisponibilite;
use Illuminate\Http\Request;

class DisponibiliteController extends Controller
{
    /**
     * Liste des disponibilités récurrentes du médecin connecté.
     */
    public function index(Request $request)
    {
        $medecin = $request->user()->medecin;

        return response()->json(['data' => $medecin->disponibilites]);
    }

    /**
     * Définition/mise à jour des disponibilités récurrentes hebdomadaires.
     * Remplace l'ensemble des créneaux pour simplifier la gestion côté frontend
     * (le médecin envoie sa grille complète : jours + heures).
     */
    public function synchroniser(Request $request)
    {
        $medecin = $request->user()->medecin;

        $data = $request->validate([
            'creneaux' => ['required', 'array'],
            'creneaux.*.jour_semaine' => ['required', 'integer', 'between:1,7'],
            'creneaux.*.heure_debut' => ['required', 'date_format:H:i'],
            'creneaux.*.heure_fin' => ['required', 'date_format:H:i', 'after:creneaux.*.heure_debut'],
        ]);

        $medecin->disponibilites()->delete();

        foreach ($data['creneaux'] as $creneau) {
            Disponibilite::create([
                'medecin_id' => $medecin->id,
                'jour_semaine' => $creneau['jour_semaine'],
                'heure_debut' => $creneau['heure_debut'],
                'heure_fin' => $creneau['heure_fin'],
            ]);
        }

        ActivityLog::enregistrer('Mise à jour des disponibilités récurrentes', 'config', $medecin);

        return response()->json(['data' => $medecin->fresh('disponibilites')]);
    }

    /**
     * Blocage ponctuel d'un créneau (urgence, absence imprévue) — règle de gestion.
     */
    public function bloquer(Request $request)
    {
        $medecin = $request->user()->medecin;

        $data = $request->validate([
            'debut' => ['required', 'date'],
            'fin' => ['required', 'date', 'after:debut'],
            'motif' => ['nullable', 'string', 'max:255'],
        ]);

        $indispo = Indisponibilite::create([
            'medecin_id' => $medecin->id,
            'debut' => $data['debut'],
            'fin' => $data['fin'],
            'motif' => $data['motif'] ?? null,
        ]);

        ActivityLog::enregistrer('Blocage d\'un créneau (' . ($data['motif'] ?? 'sans motif') . ')', 'rdv', $medecin);

        return response()->json(['data' => $indispo], 201);
    }

    public function debloquer(Request $request, Indisponibilite $indisponibilite)
    {
        if ($indisponibilite->medecin_id !== $request->user()->medecin->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $indisponibilite->delete();

        return response()->json(['message' => 'Créneau débloqué.']);
    }
}
