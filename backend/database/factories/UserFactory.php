<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition(): array
    {
        return [
            'prenom' => fake('fr_FR')->firstName(),
            'nom' => fake('fr_FR')->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'telephone' => '+2376' . fake()->unique()->numerify('########'),
            'mot_de_passe' => Hash::make('password'),
            'statut' => 'actif',
            'email_verifie_le' => now(),
        ];
    }
}
