<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Medecin;
use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MedecinController extends Controller
{
    /**
     * Annuaire public des médecins actifs, filtrable par spécialité et recherche texte.
     */
    public function index(Request $request)
    {
        $query = Medecin::query()
            ->with(['user', 'specialites'])
            ->whereHas('user', fn ($q) => $q->where('statut', 'actif'));

        if ($request->filled('specialite')) {
            $query->whereHas('specialites', fn ($q) => $q->where('slug', $request->string('specialite')));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->string('q') . '%';
            $query->whereHas('user', fn ($q) => $q->where('prenom', 'like', $term)->orWhere('nom', 'like', $term))
                ->orWhereHas('specialites', fn ($q) => $q->where('nom', 'like', $term));
        }

        $medecins = $query->paginate($request->integer('per_page', 12));

        return response()->json($medecins);
    }

    public function show(Medecin $medecin)
    {
        $medecin->load(['user', 'specialites', 'disponibilites' => fn ($q) => $q->where('actif', true)]);

        return response()->json(['data' => $medecin]);
    }

    /**
     * Calcule les créneaux disponibles d'un médecin sur une date donnée,
     * en croisant ses disponibilités récurrentes, ses indisponibilités
     * ponctuelles, les jours fériés et les rendez-vous déjà pris.
     */
    public function creneauxDisponibles(Request $request, Medecin $medecin)
    {
        $data = $request->validate(['date' => ['required', 'date', 'after_or_equal:today']]);
        $date = Carbon::parse($data['date']);

        if (\App\Models\JourFerie::whereDate('date', $date)->exists()) {
            return response()->json(['data' => [], 'message' => 'Hôpital fermé ce jour (jour férié).']);
        }

        $jourSemaine = $date->isoWeekday(); // 1 = lundi ... 7 = dimanche

        $dispo = $medecin->disponibilites()
            ->where('jour_semaine', $jourSemaine)
            ->where('actif', true)
            ->get();

        if ($dispo->isEmpty()) {
            return response()->json(['data' => [], 'message' => 'Médecin non disponible ce jour.']);
        }

        // Indisponibilités ponctuelles (congé, urgence) qui recouvrent la date
        $indispos = $medecin->indisponibilites()
            ->whereDate('debut', '<=', $date)
            ->whereDate('fin', '>=', $date)
            ->get();

        // Créneaux déjà occupés (statuts actifs uniquement)
        $occupes = RendezVous::where('medecin_id', $medecin->id)
            ->whereDate('date', $date)
            ->whereIn('statut', ['en_attente', 'confirme', 'reprogramme'])
            ->pluck('heure_debut')
            ->map(fn ($h) => substr($h, 0, 5))
            ->all();

        $duree = $medecin->duree_consultation_defaut;
        $creneaux = [];

        foreach ($dispo as $plage) {
            $curseur = Carbon::parse($plage->heure_debut);
            $fin = Carbon::parse($plage->heure_fin);

            while ($curseur->copy()->addMinutes($duree)->lte($fin)) {
                $heureStr = $curseur->format('H:i');
                $bloque = $indispos->contains(function ($i) use ($date, $curseur) {
                    $moment = $date->copy()->setTimeFromTimeString($curseur->format('H:i:s'));
                    return $moment->between($i->debut, $i->fin);
                });

                $creneaux[] = [
                    'heure' => $heureStr,
                    'disponible' => ! $bloque && ! in_array($heureStr, $occupes),
                ];

                $curseur->addMinutes($duree);
            }
        }

        return response()->json(['data' => $creneaux]);
    }

    /**
     * Création d'un compte médecin — réservé à l'administrateur.
     */
    public function store(\App\Http\Requests\Admin\StoreMedecinRequest $request)
    {
        $data = $request->validated();

        $user = \App\Models\User::create([
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'email' => $data['email'],
            'telephone' => $data['telephone'],
            'mot_de_passe' => Hash::make(str()->random(12)), // mot de passe temporaire
        ]);
        $user->assignRole('medecin');

        $medecin = Medecin::create([
            'user_id' => $user->id,
            'numero_ordre' => $data['numero_ordre'],
            'biographie' => $data['biographie'] ?? null,
            'annees_experience' => $data['annees_experience'] ?? 0,
            'tarif_consultation' => $data['tarif_consultation'] ?? 0,
            'duree_consultation_defaut' => $data['duree_consultation_defaut'] ?? 30,
        ]);

        $medecin->specialites()->sync($data['specialite_ids']);

        // TODO: envoyer les identifiants provisoires au médecin par email (job asynchrone)

        ActivityLog::enregistrer("Création du compte médecin {$user->email}", 'securite', $user);

        return response()->json(['data' => $medecin->load(['user', 'specialites'])], 201);
    }

    /** Blocage/déblocage d'un médecin — réservé admin. */
    public function toggleBlock(Medecin $medecin)
    {
        $user = $medecin->user;
        $user->statut = $user->statut === 'actif' ? 'bloque' : 'actif';
        $user->save();

        ActivityLog::enregistrer(
            ($user->statut === 'bloque' ? 'Blocage' : 'Déblocage') . " du compte médecin {$user->email}",
            'securite',
            $user
        );

        return response()->json(['data' => $medecin->fresh('user')]);
    }
}
