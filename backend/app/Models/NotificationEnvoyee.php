<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationEnvoyee extends Model
{
    protected $table = 'notifications_envoyees';

    protected $fillable = [
        'user_id', 'rendez_vous_id', 'type', 'canal', 'contenu', 'statut_envoi', 'envoye_le',
    ];

    protected $casts = ['envoye_le' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class);
    }
}
