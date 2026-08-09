<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Comptes de démonstration pour tester rapidement les trois espaces
     * (mot de passe identique pour tous : "password").
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@laquintinie.cm'],
            [
                'prenom' => 'Admin',
                'nom' => 'Système',
                'telephone' => '+237600000001',
                'mot_de_passe' => Hash::make('password'),
                'email_verifie_le' => now(),
            ]
        );
        $admin->assignRole('admin');

        $secretaire = User::firstOrCreate(
            ['email' => 'accueil@laquintinie.cm'],
            [
                'prenom' => 'Sylvie',
                'nom' => 'Manga',
                'telephone' => '+237600000002',
                'mot_de_passe' => Hash::make('password'),
                'email_verifie_le' => now(),
            ]
        );
        $secretaire->assignRole('secretaire');

        $patient = User::firstOrCreate(
            ['email' => 'walter.d@gmail.com'],
            [
                'prenom' => 'Walter',
                'nom' => 'Djoko',
                'telephone' => '+237677123456',
                'mot_de_passe' => Hash::make('password'),
                'email_verifie_le' => now(),
            ]
        );
        $patient->assignRole('patient');
    }
}
