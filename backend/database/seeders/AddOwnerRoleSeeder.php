<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddOwnerRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        // Permissions propriétaire
        $permissions = [
            ['name' => 'view-own-vehicles',   'label' => 'Voir ses véhicules',         'description' => 'Voir les véhicules du propriétaire'],
            ['name' => 'view-own-payments',   'label' => 'Voir ses paiements',          'description' => 'Voir les paiements liés à ses véhicules'],
            ['name' => 'view-own-leaves',     'label' => 'Voir les pauses véhicule',    'description' => 'Voir les pauses liées à ses véhicules'],
            ['name' => 'view-own-contracts',  'label' => 'Voir ses contrats',           'description' => 'Voir ses contrats véhicule'],
        ];

        foreach ($permissions as $item) {
            Permission::firstOrCreate(
                ['name' => $item['name']],
                [
                    'label'       => $item['label'],
                    'description' => $item['description'],
                    'guard_name'  => 'web',
                ]
            );
        }

        // Rôle propriétaire
        $ownerRole = Role::firstOrCreate(
            ['name' => 'proprietaire'],
            [
                'label'       => 'Propriétaire',
                'description' => 'Propriétaire de véhicule',
                'guard_name'  => 'web',
            ]
        );

        $ownerRole->givePermissionTo([
            'view-own-vehicles',
            'view-own-payments',
            'view-own-leaves',
            'view-own-contracts',
        ]);

        // Permissions admin pour la gestion des véhicules/contrats
        $adminPermissions = [
            ['name' => 'view-vehicles',    'label' => 'Voir les véhicules',      'description' => 'Voir tous les véhicules'],
            ['name' => 'create-vehicles',  'label' => 'Créer un véhicule',       'description' => 'Créer un véhicule'],
            ['name' => 'edit-vehicles',    'label' => 'Modifier un véhicule',    'description' => 'Modifier un véhicule'],
            ['name' => 'delete-vehicles',  'label' => 'Supprimer un véhicule',   'description' => 'Supprimer un véhicule'],
            ['name' => 'manage-contracts', 'label' => 'Gérer les contrats',      'description' => 'Gérer les contrats véhicule et agent'],
            ['name' => 'manage-vehicle-pauses', 'label' => 'Gérer les pauses véhicule', 'description' => 'Gérer les pauses des véhicules'],
        ];

        foreach ($adminPermissions as $item) {
            Permission::firstOrCreate(
                ['name' => $item['name']],
                [
                    'label'       => $item['label'],
                    'description' => $item['description'],
                    'guard_name'  => 'web',
                ]
            );
        }

        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(array_column($adminPermissions, 'name'));

        $this->command->info('Rôle propriétaire et permissions ajoutés avec succès !');
    }
}
