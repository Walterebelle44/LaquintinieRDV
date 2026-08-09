<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SpecialiteSeeder::class,
            UserSeeder::class,
            MedecinSeeder::class,
            ParametreSeeder::class,
        ]);
    }
}
