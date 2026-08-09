<?php

namespace Database\Seeders;

use App\Models\Medecin;
use App\Models\Specialite;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MedecinSeeder extends Seeder
{
    public function run(): void
    {
        $medecins = [
            ['prenom' => 'Jean-Paul', 'nom' => 'Ekwalla', 'specialite' => 'Cardiologie', 'ordre' => 'OM-2011-0142', 'exp' => 15, 'tarif' => 15000],
            ['prenom' => 'Aïcha', 'nom' => 'Mballa', 'specialite' => 'Médecine générale', 'ordre' => 'OM-2017-0389', 'exp' => 9, 'tarif' => 8000],
            ['prenom' => 'Bernard', 'nom' => 'Tchoumi', 'specialite' => 'Dentaire', 'ordre' => 'OD-2015-0064', 'exp' => 11, 'tarif' => 12000],
            ['prenom' => 'Fatimatou', 'nom' => 'Njoya', 'specialite' => 'Pédiatrie', 'ordre' => 'OM-2019-0511', 'exp' => 7, 'tarif' => 10000],
            ['prenom' => 'Marceline', 'nom' => 'Etoundi', 'specialite' => 'Gynécologie', 'ordre' => 'OM-2013-0227', 'exp' => 13, 'tarif' => 14000],
        ];

        foreach ($medecins as $i => $m) {
            $email = strtolower($m['prenom'][0] . '.' . $m['nom']) . '@laquintinie.cm';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'prenom' => $m['prenom'],
                    'nom' => $m['nom'],
                    'telephone' => '+2376900000' . (10 + $i),
                    'mot_de_passe' => Hash::make('password'),
                    'email_verifie_le' => now(),
                ]
            );
            $user->assignRole('medecin');

            $medecin = Medecin::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'numero_ordre' => $m['ordre'],
                    'annees_experience' => $m['exp'],
                    'tarif_consultation' => $m['tarif'],
                    'duree_consultation_defaut' => 30,
                    'biographie' => "Spécialiste en {$m['specialite']}, {$m['exp']} ans d'expérience au CHU.",
                    'salle' => 'Bâtiment B — Salle ' . (200 + $i),
                ]
            );

            $specialite = Specialite::where('nom', $m['specialite'])->first();
            if ($specialite) {
                $medecin->specialites()->syncWithoutDetaching([$specialite->id]);
            }

            // Disponibilités par défaut : Lun-Ven 08h-15h
            foreach ([1, 2, 3, 4, 5] as $jour) {
                $medecin->disponibilites()->firstOrCreate([
                    'jour_semaine' => $jour,
                    'heure_debut' => '08:00',
                    'heure_fin' => '15:00',
                ]);
            }
        }
    }
}
