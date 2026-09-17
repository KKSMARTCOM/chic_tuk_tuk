<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Amorce la base.
     *
     * Les rôles et permissions passent par un seul seeder de référence. Il a
     * remplacé RoleAndPermissionSeeder et ses deux rustines successives
     * (AddMissingPermissionsSeeder, AddOwnerRoleSeeder) : cet empilement était le
     * mécanisme même de la dérive entre environnements que la référence corrige.
     *
     * L'ordre compte : UserSeeder attribue des rôles, qui doivent donc exister.
     */
    public function run(): void
    {
        $this->call([
            ReferenceRolesAndPermissionsSeeder::class,
            UserSeeder::class,
        ]);
    }
}
