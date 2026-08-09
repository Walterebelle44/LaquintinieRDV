<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'prenom', 'nom', 'email', 'telephone', 'mot_de_passe',
        'photo', 'date_naissance', 'sexe', 'statut',
    ];

    protected $hidden = [
        'mot_de_passe', 'remember_token',
    ];

    protected $casts = [
        'email_verifie_le' => 'datetime',
        'telephone_verifie_le' => 'datetime',
        'date_naissance' => 'date',
    ];

    /**
     * Laravel s'attend par défaut à la colonne "password" : on la fait
     * pointer vers "mot_de_passe" pour garder un schéma en français.
     */
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function medecin()
    {
        return $this->hasOne(Medecin::class);
    }

    public function rendezVousPatient()
    {
        return $this->hasMany(RendezVous::class, 'patient_id');
    }

    public function estActif(): bool
    {
        return $this->statut === 'actif';
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }
}
