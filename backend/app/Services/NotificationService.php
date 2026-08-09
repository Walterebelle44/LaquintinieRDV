<?php

namespace App\Services;

use App\Jobs\EnvoyerNotificationJob;
use App\Models\ListeAttente;
use App\Models\NotificationEnvoyee;
use App\Models\RendezVous;

/**
 * Centralise la création des notifications liées au parcours de rendez-vous.
 * Chaque appel enregistre une entrée en base puis délègue l'envoi réel
 * (email/SMS) à un job asynchrone (file d'attente Laravel), afin de ne
 * jamais bloquer la requête HTTP appelante.
 */
class NotificationService
{
    public function rdvCree(RendezVous $rdv): void
    {
        $this->creer($rdv->patient_id, $rdv->id, 'confirmation_rdv', 'email',
            "Votre demande de rendez-vous avec Dr {$rdv->medecin->user->nom} le {$rdv->date->format('d/m/Y')} à {$rdv->heure_debut} a bien été enregistrée. Elle est en attente de confirmation.");
    }

    public function nouvelleDemandePourMedecin(RendezVous $rdv): void
    {
        $this->creer($rdv->medecin->user_id, $rdv->id, 'nouvelle_demande_medecin', 'email',
            "Nouvelle demande de rendez-vous de {$rdv->patient->nom_complet} le {$rdv->date->format('d/m/Y')} à {$rdv->heure_debut}.");
    }

    public function rdvAccepte(RendezVous $rdv): void
    {
        $this->creer($rdv->patient_id, $rdv->id, 'rdv_accepte', 'email',
            "Votre rendez-vous avec Dr {$rdv->medecin->user->nom} le {$rdv->date->format('d/m/Y')} à {$rdv->heure_debut} est confirmé. Ticket : {$rdv->ticket?->numero}.");
        $this->creer($rdv->patient_id, $rdv->id, 'rdv_accepte', 'sms',
            "MediRDV: RDV confirmé avec Dr {$rdv->medecin->user->nom} le {$rdv->date->format('d/m/Y')} à {$rdv->heure_debut}.");
    }

    public function rdvRefuse(RendezVous $rdv): void
    {
        $this->creer($rdv->patient_id, $rdv->id, 'rdv_refuse', 'email',
            "Votre demande de rendez-vous avec Dr {$rdv->medecin->user->nom} a été refusée. Motif : {$rdv->motif_refus}.");
    }

    public function rdvReprogramme(RendezVous $rdv): void
    {
        $this->creer($rdv->patient_id, $rdv->id, 'rdv_reprogramme', 'email',
            "Votre rendez-vous avec Dr {$rdv->medecin->user->nom} a été reprogrammé au {$rdv->date->format('d/m/Y')} à {$rdv->heure_debut}.");
        $this->creer($rdv->patient_id, $rdv->id, 'rdv_reprogramme', 'sms',
            "MediRDV: RDV reprogrammé au {$rdv->date->format('d/m/Y')} à {$rdv->heure_debut}.");
    }

    public function rdvAnnule(RendezVous $rdv): void
    {
        $this->creer($rdv->medecin->user_id, $rdv->id, 'rdv_annule', 'email',
            "Le rendez-vous du {$rdv->date->format('d/m/Y')} à {$rdv->heure_debut} avec {$rdv->patient->nom_complet} a été annulé.");
    }

    public function creneauLibere(ListeAttente $attente): void
    {
        $this->creer($attente->patient_id, null, 'liste_attente_liberee', 'sms',
            "MediRDV: un créneau s'est libéré le {$attente->date_souhaitee->format('d/m/Y')}. Connectez-vous pour réserver.");
    }

    public function rappel(RendezVous $rdv, string $type): void
    {
        $this->creer($rdv->patient_id, $rdv->id, $type, 'sms',
            "MediRDV: rappel — RDV avec Dr {$rdv->medecin->user->nom} le {$rdv->date->format('d/m/Y')} à {$rdv->heure_debut}.");
    }

    private function creer(int $userId, ?int $rdvId, string $type, string $canal, string $contenu): void
    {
        $notification = NotificationEnvoyee::create([
            'user_id' => $userId,
            'rendez_vous_id' => $rdvId,
            'type' => $type,
            'canal' => $canal,
            'contenu' => $contenu,
            'statut_envoi' => 'en_attente',
        ]);

        EnvoyerNotificationJob::dispatch($notification);
    }
}
