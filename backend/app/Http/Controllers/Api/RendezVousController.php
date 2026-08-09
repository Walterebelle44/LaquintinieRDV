<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RendezVous\RepondreRendezVousRequest;
use App\Http\Requests\RendezVous\StoreRendezVousRequest;
use App\Models\ActivityLog;
use App\Models\ListeAttente;
use App\Models\Medecin;
use App\Models\RendezVous;
use App\Models\Ticket;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RendezVousController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }

    /**
     * Liste des rendez-vous du patient connecté.
     */
    public function mesRendezVous(Request $request)
    {
        $rdvs = $request->user()->rendezVousPatient()
            ->with(['medecin.user', 'medecin.specialites', 'ticket'])
            ->orderByDesc('date')
            ->orderByDesc('heure_debut')
            ->paginate(10);

        return response()->json($rdvs);
    }

    /**
     * Liste des rendez-vous reçus par le médecin connecté.
     */
    public function rendezVousMedecin(Request $request)
    {
        $medecin = $request->user()->medecin;

        if (! $medecin) {
            return response()->json(['message' => 'Aucun profil médecin associé à ce compte.'], 404);
        }

        $query = $medecin->rendezVous()->with(['patient', 'ticket']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date('date'));
        }

        return response()->json($query->orderBy('date')->orderBy('heure_debut')->paginate(15));
    }

    /**
     * Supervision globale de tous les rendez-vous de la plateforme — réservé admin
     * (cahier des charges §4.1 : "il pourra tous voir").
     */
    public function tousLesRendezVous(Request $request)
    {
        $query = RendezVous::with(['patient', 'medecin.user']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }
        if ($request->filled('medecin_id')) {
            $query->where('medecin_id', $request->integer('medecin_id'));
        }
        if ($request->filled('q')) {
            $term = '%' . $request->string('q') . '%';
            $query->whereHas('patient', fn ($q) => $q->where('nom', 'like', $term)->orWhere('prenom', 'like', $term));
        }

        return response()->json($query->orderByDesc('date')->paginate($request->integer('per_page', 20)));
    }

    /**
     * Prise de rendez-vous par un patient authentifié.
     * Verrouillage transactionnel pour éviter les doubles réservations (règle de gestion #2 et #3).
     */
    public function store(StoreRendezVousRequest $request)
    {
        $data = $request->validated();
        $patient = $request->user();

        $medecin = Medecin::with('user')->findOrFail($data['medecin_id']);

        if (! $medecin->estActif()) {
            return response()->json(['message' => 'Ce médecin n\'est pas disponible actuellement.'], 422);
        }

        $date = Carbon::parse($data['date']);
        $heureDebut = $data['heure_debut'];
        $heureFin = Carbon::parse($heureDebut)->addMinutes($medecin->duree_consultation_defaut)->format('H:i');

        try {
            $rdv = DB::transaction(function () use ($medecin, $patient, $date, $heureDebut, $heureFin, $data) {
                // Verrou pessimiste : on empêche une double réservation concurrente sur le même créneau
                $conflit = RendezVous::where('medecin_id', $medecin->id)
                    ->whereDate('date', $date)
                    ->where('heure_debut', $heureDebut)
                    ->whereIn('statut', ['en_attente', 'confirme', 'reprogramme'])
                    ->lockForUpdate()
                    ->exists();

                if ($conflit) {
                    throw new \RuntimeException('CRENEAU_INDISPONIBLE');
                }

                // Un même patient ne peut pas avoir deux RDV chevauchants (règle #1)
                $chevauchement = RendezVous::where('patient_id', $patient->id)
                    ->whereDate('date', $date)
                    ->where('heure_debut', $heureDebut)
                    ->whereIn('statut', ['en_attente', 'confirme', 'reprogramme'])
                    ->exists();

                if ($chevauchement) {
                    throw new \RuntimeException('PATIENT_DEJA_RESERVE');
                }

                return RendezVous::create([
                    'patient_id' => $patient->id,
                    'medecin_id' => $medecin->id,
                    'date' => $date,
                    'heure_debut' => $heureDebut,
                    'heure_fin' => $heureFin,
                    'motif' => $data['motif'] ?? null,
                    'statut' => 'en_attente',
                ]);
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'CRENEAU_INDISPONIBLE') {
                return response()->json(['message' => 'Ce créneau vient d\'être réservé par un autre patient. Merci d\'en choisir un autre.'], 409);
            }
            if ($e->getMessage() === 'PATIENT_DEJA_RESERVE') {
                return response()->json(['message' => 'Vous avez déjà un rendez-vous sur ce créneau.'], 409);
            }
            throw $e;
        }

        $this->notifications->rdvCree($rdv);
        $this->notifications->nouvelleDemandePourMedecin($rdv);

        ActivityLog::enregistrer("Nouveau rendez-vous demandé par {$patient->email} (Dr {$medecin->user->nom})", 'rdv', $rdv);

        return response()->json(['data' => $rdv->load(['medecin.user', 'patient'])], 201);
    }

    /**
     * Le médecin accepte, refuse ou reprogramme une demande de rendez-vous.
     */
    public function repondre(RepondreRendezVousRequest $request, RendezVous $rendezVous)
    {
        $medecin = $request->user()->medecin;

        if (! $medecin || $rendezVous->medecin_id !== $medecin->id) {
            return response()->json(['message' => 'Ce rendez-vous ne vous appartient pas.'], 403);
        }

        $data = $request->validated();

        switch ($data['action']) {
            case 'accepter':
                $rendezVous->update(['statut' => 'confirme', 'repondu_le' => now()]);
                $this->genererTicket($rendezVous);
                $this->notifications->rdvAccepte($rendezVous);
                ActivityLog::enregistrer("Rendez-vous {$rendezVous->uuid} confirmé", 'rdv', $rendezVous);
                break;

            case 'refuser':
                $rendezVous->update([
                    'statut' => 'refuse',
                    'motif_refus' => $data['motif_refus'],
                    'repondu_le' => now(),
                ]);
                $this->notifications->rdvRefuse($rendezVous);
                ActivityLog::enregistrer("Rendez-vous {$rendezVous->uuid} refusé", 'rdv', $rendezVous);
                break;

            case 'reprogrammer':
                $rendezVous->update([
                    'date' => $data['nouvelle_date'],
                    'heure_debut' => $data['nouvelle_heure'],
                    'heure_fin' => Carbon::parse($data['nouvelle_heure'])->addMinutes($medecin->duree_consultation_defaut)->format('H:i'),
                    'statut' => 'confirme',
                    'repondu_le' => now(),
                ]);
                $this->genererTicket($rendezVous);
                $this->notifications->rdvReprogramme($rendezVous);
                ActivityLog::enregistrer("Rendez-vous {$rendezVous->uuid} reprogrammé", 'rdv', $rendezVous);
                break;
        }

        return response()->json(['data' => $rendezVous->fresh(['patient', 'medecin.user', 'ticket'])]);
    }

    /**
     * Annulation par le patient, dans le respect du délai configurable (règle de gestion #5).
     */
    public function annuler(Request $request, RendezVous $rendezVous)
    {
        if ($rendezVous->patient_id !== $request->user()->id) {
            return response()->json(['message' => 'Ce rendez-vous ne vous appartient pas.'], 403);
        }

        if (! $rendezVous->estAnnulableParPatient()) {
            $delai = config('rdv.delai_annulation_heures', 24);
            return response()->json([
                'message' => "L'annulation n'est plus possible moins de {$delai}h avant le rendez-vous.",
            ], 422);
        }

        $rendezVous->update([
            'statut' => 'annule',
            'annule_le' => now(),
            'annule_par' => 'patient',
        ]);

        $this->notifications->rdvAnnule($rendezVous);
        $this->notifierListeAttente($rendezVous);

        ActivityLog::enregistrer("Rendez-vous {$rendezVous->uuid} annulé par le patient", 'rdv', $rendezVous);

        return response()->json(['data' => $rendezVous]);
    }

    private function genererTicket(RendezVous $rendezVous): void
    {
        if ($rendezVous->ticket) {
            return;
        }

        Ticket::create([
            'rendez_vous_id' => $rendezVous->id,
            'numero' => Ticket::genererNumero(),
            'genere_le' => now(),
        ]);
    }

    private function notifierListeAttente(RendezVous $rendezVous): void
    {
        $attente = ListeAttente::where('medecin_id', $rendezVous->medecin_id)
            ->whereDate('date_souhaitee', $rendezVous->date)
            ->where('statut', 'en_attente')
            ->orderBy('created_at')
            ->first();

        if ($attente) {
            $attente->update(['statut' => 'notifie']);
            $this->notifications->creneauLibere($attente);
        }
    }
}
