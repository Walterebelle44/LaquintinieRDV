<?php

namespace App\Console\Commands;

use App\Models\RendezVous;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Envoie les rappels automatiques avant chaque rendez-vous confirmé,
 * selon les échéances définies dans RDV_RAPPEL_HEURES_AVANT (.env),
 * ex. "24,1" pour un rappel 24h avant puis 1h avant (cahier des charges §5.3).
 *
 * À planifier dans routes/console.php :
 *   Schedule::command('rdv:rappels')->everyFifteenMinutes();
 */
class EnvoyerRappelsRendezVous extends Command
{
    protected $signature = 'rdv:rappels';
    protected $description = 'Envoie les rappels programmés (24h / 1h) pour les rendez-vous confirmés à venir';

    public function handle(NotificationService $notifications): int
    {
        $echeances = array_map('intval', explode(',', config('rdv.rappel_heures_avant', '24,1')));

        foreach ($echeances as $heures) {
            $cible = now()->addHours($heures);

            $rdvs = RendezVous::where('statut', 'confirme')
                ->whereDate('date', $cible->toDateString())
                ->get()
                ->filter(function (RendezVous $rdv) use ($cible) {
                    $debut = Carbon::parse($rdv->date->toDateString() . ' ' . $rdv->heure_debut);
                    // fenêtre de 15 minutes autour de l'échéance pour matcher le pas du scheduler
                    return $debut->between($cible->copy()->subMinutes(7), $cible->copy()->addMinutes(7));
                });

            $type = $heures >= 24 ? 'rappel_24h' : 'rappel_1h';

            foreach ($rdvs as $rdv) {
                $dejaEnvoye = $rdv->id && \App\Models\NotificationEnvoyee::where('rendez_vous_id', $rdv->id)
                    ->where('type', $type)->exists();

                if (! $dejaEnvoye) {
                    $notifications->rappel($rdv, $type);
                    $this->info("Rappel {$type} envoyé pour le RDV {$rdv->uuid}");
                }
            }
        }

        return self::SUCCESS;
    }
}
