<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddMissingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $permissions = [
            // Dashboard
            ['name' => 'view-dashboard',    'label' => 'Voir le tableau de bord',       'description' => 'Accès au tableau de bord'],

            // Drivers — manquants
            ['name' => 'export-drivers',    'label' => 'Exporter les agents',            'description' => 'Exporter la liste des agents'],
            ['name' => 'import-drivers',    'label' => 'Importer des agents',            'description' => 'Importer des agents via fichier'],

            // Circuits
            ['name' => 'view-circuits',     'label' => 'Voir les circuits',              'description' => 'Voir la liste des circuits touristiques'],
            ['name' => 'create-circuits',   'label' => 'Créer un circuit',               'description' => 'Créer un circuit touristique'],
            ['name' => 'edit-circuits',     'label' => 'Modifier un circuit',            'description' => 'Modifier un circuit touristique'],
            ['name' => 'delete-circuits',   'label' => 'Supprimer un circuit',           'description' => 'Supprimer un circuit touristique'],

            // Promo Codes
            ['name' => 'view-promo-codes',  'label' => 'Voir les codes promo',           'description' => 'Voir la liste des codes promo'],
            ['name' => 'create-promo-codes', 'label' => 'Créer un code promo',            'description' => 'Créer un code promo'],
            ['name' => 'edit-promo-codes',  'label' => 'Modifier un code promo',         'description' => 'Modifier un code promo'],
            ['name' => 'delete-promo-codes', 'label' => 'Supprimer un code promo',        'description' => 'Supprimer un code promo'],

            // Leaves
            ['name' => 'view-leaves',       'label' => 'Voir les congés',                'description' => 'Voir les congés des agents'],
            ['name' => 'create-leaves',     'label' => 'Créer un congé',                 'description' => 'Ajouter un congé à un agent'],
            ['name' => 'delete-leaves',     'label' => 'Révoquer un congé',              'description' => 'Révoquer un congé d\'un agent'],

            // Leave Requests
            ['name' => 'view-leave-requests',   'label' => 'Voir les demandes de congé',    'description' => 'Voir les demandes de congé'],
            ['name' => 'approve-leave-requests', 'label' => 'Approuver une demande',         'description' => 'Approuver une demande de congé'],
            ['name' => 'reject-leave-requests',  'label' => 'Rejeter une demande',           'description' => 'Rejeter une demande de congé'],

            // Roles
            ['name' => 'view-roles',        'label' => 'Voir les rôles',                 'description' => 'Voir la liste des rôles'],
            ['name' => 'create-roles',      'label' => 'Créer un rôle',                  'description' => 'Créer un nouveau rôle'],
            ['name' => 'edit-roles',        'label' => 'Modifier un rôle',               'description' => 'Modifier un rôle existant'],
            ['name' => 'delete-roles',      'label' => 'Supprimer un rôle',              'description' => 'Supprimer un rôle'],

            // Permissions
            ['name' => 'view-permissions',  'label' => 'Voir les permissions',           'description' => 'Voir la liste des permissions'],
            ['name' => 'create-permissions', 'label' => 'Créer une permission',           'description' => 'Créer une nouvelle permission'],
            ['name' => 'edit-permissions',  'label' => 'Modifier une permission',        'description' => 'Modifier une permission existante'],
            ['name' => 'delete-permissions', 'label' => 'Supprimer une permission',       'description' => 'Supprimer une permission'],
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

        // Assigner toutes les permissions au rôle admin
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(array_column($permissions, 'name'));

        $this->command->info('Permissions manquantes ajoutées et assignées à admin avec succès !');
    }
}
