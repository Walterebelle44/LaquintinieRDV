<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Liste complète des utilisateurs (patients, médecins, secrétaires, admins)
     * avec recherche et filtre par rôle — vue globale réservée à l'administrateur.
     */
    public function index(Request $request)
    {
        $query = User::query()->with('roles');

        if ($request->filled('role')) {
            $query->role($request->string('role'));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->string('q') . '%';
            $query->where(fn ($q) => $q->where('prenom', 'like', $term)
                ->orWhere('nom', 'like', $term)
                ->orWhere('email', 'like', $term));
        }

        return response()->json($query->orderByDesc('created_at')->paginate($request->integer('per_page', 15)));
    }

    public function show(User $user)
    {
        return response()->json(['data' => $user->load('roles', 'medecin')]);
    }

    /**
     * Création d'un compte par l'admin (admin, secrétaire — les patients
     * s'inscrivent eux-mêmes, les médecins passent par MedecinController::store).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'prenom' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'telephone' => ['required', 'string', 'unique:users,telephone'],
            'role' => ['required', 'in:admin,secretaire,patient'],
        ]);

        $user = User::create([
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'email' => $data['email'],
            'telephone' => $data['telephone'],
            'mot_de_passe' => Hash::make(str()->random(12)),
        ]);
        $user->assignRole($data['role']);

        ActivityLog::enregistrer("Création du compte {$data['role']} {$user->email}", 'securite', $user);

        // TODO: envoyer les identifiants provisoires par email (job asynchrone)

        return response()->json(['data' => $user->load('roles')], 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'prenom' => ['sometimes', 'string', 'max:100'],
            'nom' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'telephone' => ['sometimes', 'string', 'unique:users,telephone,' . $user->id],
        ]);

        $user->update($data);

        ActivityLog::enregistrer("Modification du compte {$user->email}", 'securite', $user);

        return response()->json(['data' => $user]);
    }

    /**
     * Blocage / déblocage — un compte bloqué ne peut plus se connecter (règle de gestion #6).
     */
    public function toggleBlock(User $user)
    {
        $user->statut = $user->statut === 'actif' ? 'bloque' : 'actif';
        $user->save();

        if ($user->statut === 'bloque') {
            $user->tokens()->delete(); // invalide les sessions actives immédiatement
        }

        ActivityLog::enregistrer(
            ($user->statut === 'bloque' ? 'Blocage' : 'Déblocage') . " du compte {$user->email}",
            'securite',
            $user
        );

        return response()->json(['data' => $user]);
    }

    public function destroy(User $user)
    {
        $email = $user->email;
        $user->tokens()->delete();
        $user->delete(); // soft delete

        ActivityLog::enregistrer("Suppression du compte {$email}", 'securite');

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    /** Changement de rôle d'un compte existant. */
    public function changeRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:admin,secretaire,patient,medecin'],
        ]);

        $user->syncRoles([$data['role']]);

        ActivityLog::enregistrer("Changement de rôle de {$user->email} vers {$data['role']}", 'securite', $user);

        return response()->json(['data' => $user->fresh('roles')]);
    }
}
