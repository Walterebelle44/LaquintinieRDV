<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RendezVous extends Model
{
    use HasFactory;

    protected $table = 'rendez_vous';

    protected $fillable = [
        'uuid', 'patient_id', 'medecin_id', 'cree_par_id', 'date', 'heure_debut', 'heure_fin',
        'motif', 'statut', 'motif_refus', 'repondu_le', 'annule_le', 'annule_par',
    ];

    protected $casts = [
        'date' => 'date',
        'repondu_le' => 'datetime',
        'annule_le' => 'datetime',
    ];

    public const STATUTS = [
        'en_attente', 'confirme', 'refuse', 'reprogramme', 'termine', 'annule', 'absent',
    ];

    protected static function booted(): void
    {
        static::creating(function (RendezVous $rdv) {
            $rdv->uuid = $rdv->uuid ?: (string) Str::uuid();
        });
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }

    public function creePar()
    {
        return $this->belongsTo(User::class, 'cree_par_id');
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }

    public function scopeActifs($query)
    {
        return $query->whereNotIn('statut', ['annule', 'refuse']);
    }

    public function estAnnulableParPatient(): bool
    {
        $delai = (int) config('rdv.delai_annulation_heures', 24);
        $debut = $this->date->copy()->setTimeFromTimeString($this->heure_debut);

        return in_array($this->statut, ['en_attente', 'confirme'])
            && now()->diffInHours($debut, false) >= $delai;
    }
}
