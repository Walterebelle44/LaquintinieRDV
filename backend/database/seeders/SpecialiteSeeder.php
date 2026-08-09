<?php

namespace Database\Seeders;

use App\Models\Specialite;
use Illuminate\Database\Seeder;

class SpecialiteSeeder extends Seeder
{
    public function run(): void
    {
        $specialites = [
            ['nom' => 'Cardiologie', 'icone' => 'heart', 'description' => "Cœur & système vasculaire"],
            ['nom' => 'Médecine générale', 'icone' => 'stethoscope', 'description' => 'Consultations générales'],
            ['nom' => 'Dentaire', 'icone' => 'tooth', 'description' => 'Soins bucco-dentaires'],
            ['nom' => 'Pédiatrie', 'icone' => 'baby', 'description' => "Santé de l'enfant"],
            ['nom' => 'Gynécologie', 'icone' => 'flower', 'description' => 'Santé de la femme'],
            ['nom' => 'Dermatologie', 'icone' => 'sparkle', 'description' => 'Peau, cheveux & ongles'],
            ['nom' => 'Ophtalmologie', 'icone' => 'eye', 'description' => 'Yeux & vision'],
            ['nom' => 'Orthopédie', 'icone' => 'bone', 'description' => 'Os, muscles & articulations'],
        ];

        foreach ($specialites as $s) {
            Specialite::firstOrCreate(['nom' => $s['nom']], $s);
        }
    }
}
