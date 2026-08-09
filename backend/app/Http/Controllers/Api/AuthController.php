<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouveau patient.
     * Le rôle "patient" est attribué automatiquement.
     * Les comptes médecin/admin/secrétaire sont créés uniquement par un administrateur.
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'email' => $data['email'],
            'telephone' => $data['telephone'],
            'mot_de_passe' => Hash::make($data['mot_de_passe']),
            'date_naissance' => $data['date_naissance'] ?? null,
            'sexe' => $data['sexe'] ?? null,
        ]);

        $user->assignRole('patient');

        // TODO: envoyer un email/SMS de vérification (job asynchrone en file d'attente)

        ActivityLog::enregistrer("Inscription du patient {$user->email}", 'connexion', $user);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'user' => $this->formatUser($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['mot_de_passe'], $user->mot_de_passe)) {
            ActivityLog::enregistrer("Tentative de connexion échouée pour {$data['email']}", 'alerte');

            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        if (! $user->estActif()) {
            throw ValidationException::withMessages([
                'email' => ['Ce compte est bloqué. Contactez l\'administration.'],
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;

        ActivityLog::enregistrer("Connexion réussie ({$user->email})", 'connexion', $user);

        return response()->json([
            'message' => 'Connexion réussie.',
            'user' => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->formatUser($request->user())]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'prenom' => ['sometimes', 'string', 'max:100'],
            'nom' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'telephone' => ['sometimes', 'string', 'unique:users,telephone,' . $user->id],
            'photo' => ['sometimes', 'nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('avatars', 'public');
            $data['photo'] = $path;
        }

        $user->update($data);

        ActivityLog::enregistrer('Mise à jour du profil', 'securite', $user);

        return response()->json(['message' => 'Profil mis à jour.', 'user' => $this->formatUser($user)]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'mot_de_passe_actuel' => ['required'],
            'mot_de_passe' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['mot_de_passe_actuel'], $user->mot_de_passe)) {
            throw ValidationException::withMessages([
                'mot_de_passe_actuel' => ['Mot de passe actuel incorrect.'],
            ]);
        }

        $user->update(['mot_de_passe' => Hash::make($data['mot_de_passe'])]);

        ActivityLog::enregistrer('Modification du mot de passe', 'securite', $user);

        // On invalide les autres sessions/tokens par sécurité
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // TODO: générer un token de réinitialisation et l'envoyer par email/SMS (job en file d'attente).
        // On répond volontairement de façon identique que l'email existe ou non (anti-énumération).

        return response()->json([
            'message' => 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.',
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'prenom' => $user->prenom,
            'nom' => $user->nom,
            'email' => $user->email,
            'telephone' => $user->telephone,
            'photo' => $user->photo,
            'statut' => $user->statut,
            'roles' => $user->getRoleNames(),
            'medecin_id' => $user->medecin?->id,
        ];
    }
}
