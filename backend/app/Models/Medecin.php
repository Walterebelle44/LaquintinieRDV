<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'numero_ordre', 'biographie', 'annees_experience',
        'tarif_consultation', 'duree_consultation_defaut', 'salle',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialites()
    {
        return $this->belongsToMany(Specialite::class, 'medecin_specialite');
    }

    public function disponibilites()
    {
        return $this->hasMany(Disponibilite::class);
    }

    public function indisponibilites()
    {
        return $this->hasMany(Indisponibilite::class);
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class);
    }

    public function estActif(): bool
    {
        return $this->user?->estActif() ?? false;
    }
}
