<?php

namespace App\Jobs;

use App\Models\NotificationEnvoyee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envoi asynchrone d'une notification (email ou SMS) via la file d'attente
 * Laravel (Queues), pour ne jamais bloquer les requêtes HTTP synchrones.
 *
 * L'envoi SMS s'appuie sur la passerelle configurée dans .env
 * (SMS_GATEWAY_URL / SMS_GATEWAY_KEY) — à brancher sur le fournisseur
 * retenu par l'hôpital (ex. Nexah, Twilio, Orange/MTN API).
 */
class EnvoyerNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public NotificationEnvoyee $notification)
    {
    }

    public function handle(): void
    {
        try {
            if ($this->notification->canal === 'email') {
                $this->envoyerEmail();
            } else {
                $this->envoyerSms();
            }

            $this->notification->update(['statut_envoi' => 'envoye', 'envoye_le' => now()]);
        } catch (\Throwable $e) {
            Log::error('Échec envoi notification #' . $this->notification->id . ' : ' . $e->getMessage());
            $this->notification->update(['statut_envoi' => 'echoue']);
            throw $e;
        }
    }

    private function envoyerEmail(): void
    {
        $user = $this->notification->user;
        if (! $user?->email) {
            return;
        }

        Mail::raw($this->notification->contenu, function ($message) use ($user) {
            $message->to($user->email)
                ->subject('MediRDV — Hôpital Laquintinie');
        });
    }

    private function envoyerSms(): void
    {
        $url = config('services.sms.gateway_url');
        if (! $url) {
            Log::info('SMS (simulation, passerelle non configurée) : ' . $this->notification->contenu);
            return;
        }

        \Illuminate\Support\Facades\Http::post($url, [
            'to' => $this->notification->user->telephone,
            'message' => $this->notification->contenu,
            'sender' => config('services.sms.sender_id'),
            'key' => config('services.sms.gateway_key'),
        ]);
    }
}
