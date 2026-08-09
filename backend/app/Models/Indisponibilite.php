<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indisponibilite extends Model
{
    protected $fillable = ['medecin_id', 'debut', 'fin', 'motif'];

    protected $casts = ['debut' => 'datetime', 'fin' => 'datetime'];

    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }
}
