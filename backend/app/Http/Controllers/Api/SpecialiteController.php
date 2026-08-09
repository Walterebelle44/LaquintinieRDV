<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Specialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SpecialiteController extends Controller
{
    /** Liste publique des spécialités actives, avec nombre de médecins actifs rattachés. */
    public function index()
    {
        $specialites = Specialite::actives()
            ->withCount(['medecins' => fn ($q) => $q->whereHas('user', fn ($u) => $u->where('statut', 'actif'))])
            ->orderBy('nom')
            ->get();

        return response()->json(['data' => $specialites]);
    }

    public function show(Specialite $specialite)
    {
        return response()->json(['data' => $specialite]);
    }

    /** Réservé admin. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100', 'unique:specialites,nom'],
            'description' => ['nullable', 'string'],
            'icone' => ['nullable', 'string', 'max:50'],
        ]);

        $specialite = Specialite::create($data);

        ActivityLog::enregistrer("Ajout de la spécialité « {$specialite->nom} »", 'config', $specialite);

        return response()->json(['data' => $specialite], 201);
    }

    /** Réservé admin. */
    public function update(Request $request, Specialite $specialite)
    {
        $data = $request->validate([
            'nom' => ['sometimes', 'string', 'max:100', 'unique:specialites,nom,' . $specialite->id],
            'description' => ['nullable', 'string'],
            'icone' => ['nullable', 'string', 'max:50'],
            'actif' => ['sometimes', 'boolean'],
        ]);

        $specialite->update($data);

        ActivityLog::enregistrer("Modification de la spécialité « {$specialite->nom} »", 'config', $specialite);

        return response()->json(['data' => $specialite]);
    }

    /** Réservé admin. Refusé si des médecins actifs y sont rattachés (règle de gestion #10). */
    public function destroy(Specialite $specialite)
    {
        if ($specialite->medecins()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer : des médecins sont encore rattachés à cette spécialité.',
            ], 422);
        }

        $nom = $specialite->nom;
        $specialite->delete();

        ActivityLog::enregistrer("Suppression de la spécialité « {$nom} »", 'config');

        return response()->json(['message' => 'Spécialité supprimée.']);
    }
}
