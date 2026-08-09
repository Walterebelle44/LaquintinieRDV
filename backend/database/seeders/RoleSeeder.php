<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Rôles du système — cahier des charges §4 (Acteurs et rôles).
     */
    public function run(): void
    {
        foreach (['admin', 'medecin', 'patient', 'secretaire'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
