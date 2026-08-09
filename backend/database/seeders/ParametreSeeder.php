<?php

namespace Database\Seeders;

use App\Models\Parametre;
use Illuminate\Database\Seeder;

class ParametreSeeder extends Seeder
{
    public function run(): void
    {
        $defauts = [
            'delai_annulation_heures' => 24,
            'delai_reponse_medecin_heures' => 24,
            'duree_consultation_defaut_min' => 30,
            'nom_hopital' => 'Hôpital Laquintinie de Douala',
            'adresse_hopital' => 'Boulevard de la Liberté, Akwa, Douala',
        ];

        foreach ($defauts as $cle => $valeur) {
            Parametre::firstOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        }
    }
}
