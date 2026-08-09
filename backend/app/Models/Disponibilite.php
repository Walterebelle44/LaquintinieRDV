<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disponibilite extends Model
{
    protected $fillable = ['medecin_id', 'jour_semaine', 'heure_debut', 'heure_fin', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }
}
