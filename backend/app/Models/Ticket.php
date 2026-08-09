<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['rendez_vous_id', 'numero', 'qr_code_path', 'fichier_pdf_path', 'genere_le'];

    protected $casts = ['genere_le' => 'datetime'];

    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class);
    }

    public static function genererNumero(): string
    {
        $annee = now()->year;
        $dernier = static::where('numero', 'like' , "LQT-{$annee}-%")->orderByDesc('id')->first();
        $sequence = $dernier ? ((int) substr($dernier->numero, -5)) + 1 : 1;

        return sprintf('LQT-%d-%05d', $annee, $sequence);
    }
}
