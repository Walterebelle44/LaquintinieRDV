<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListeAttente extends Model
{
    protected $table = 'liste_attentes';

    protected $fillable = ['patient_id', 'medecin_id', 'date_souhaitee', 'statut'];

    protected $casts = ['date_souhaitee' => 'date'];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }
}
