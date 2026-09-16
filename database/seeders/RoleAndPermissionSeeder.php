<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;


class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Define permissions by feature
        $permissions = [
            // User Management
            'users' => [
                [
                    'label' => 'Voir les utilisateurs',
                    'name' => 'view-users',
                    'description' => 'Voir les utilisateurs',
                ],
                [
                    'label' => 'Créer un utilisateur',
                    'name' => 'create-users',
                    'description' => 'Créer un utilisateur',
                ],
                [
                    'label' => 'Modifier un utilisateur',
                    'name' => 'edit-users',
                    'description' => 'Modifier un utilisateur',
                ],
                [
                    'label' => 'Supprimer un utilisateur',
                    'name' => 'delete-users',
                    'description' => 'Supprimer un utilisateur',
                ]
            ],

            // Booking Management
            'bookings' => [
                [
                    'label' => 'Voir les réservations',
                    'name' => 'view-bookings',
                    'description' => 'Voir les réservations',
                ],
                [
                    'label' => 'Créer une réservation',
                    'name' => 'create-bookings',
                    'description' => 'Créer une réservation',
                ],
                [
                    'label' => 'Modifier une réservation',
                    'name' => 'edit-bookings',
                    'description' => 'Modifier une réservation',
                ],
                [
                    'label' => 'Supprimer une réservation',
                    'name' => 'delete-bookings',
                    'description' => 'Supprimer une réservation',
                ],
                [
                    'label' => 'Gérer les réservations',
                    'name' => 'manage-bookings',
                    'description' => 'Gérer les réservations',
                ],
            ],

            // Driver Management
            'drivers' => [
                [
                    'label' => 'Voir les chauffeurs',
                    'name' => 'view-drivers',
                    'description' => 'Voir les chauffeurs',
                ],
                [
                    'label' => 'Créer un chauffeur',
                    'name' => 'create-drivers',
                    'description' => 'Créer un chauffeur',
                ],
                [
                    'label' => 'Modifier un chauffeur',
                    'name' => 'edit-drivers',
                    'description' => 'Modifier un chauffeur',
                ],
                [
                    'label' => 'Supprimer un chauffeur',
                    'name' => 'delete-drivers',
                    'description' => 'Supprimer un chauffeur',
                ],
            ],

            // Payment Management
            'payments' => [
                [
                    'label' => 'Voir les paiements',
                    'name' => 'view-payments',
                    'description' => 'Voir les paiements',
                ],
                [
                    'label' => 'Gérer les paiements',
                    'name' => 'manage-payments',
                    'description' => 'Gérer les paiements',
                ],
                [
                    'label' => 'Créer un paiement',
                    'name' => 'create-payments',
                    'description' => 'Créer un paiement',
                ],

            ],

            'pricing' => [
                [
                    'label' => 'Voir les tarifs',
                    'name' => 'view-pricing',
                    'description' => 'Voir les tarifs',
                ],
                [
                    'label' => 'Gérer les tarifs',
                    'name' => 'manage-pricing',
                    'description' => 'Gérer les tarifs',
                ],
                [
                    'label' => 'Créer un tarif',
                    'name' => 'create-pricing',
                    'description' => 'Créer un tarif',
                ],
                [
                    'label' => 'Modifier un tarif',
                    'name' => 'edit-pricing',
                    'description' => 'Modifier un tarif',
                ],
            ],

            // Zone Management
            'zones' => [
                [
                    'label' => 'Voir les zones',
                    'name' => 'view-zones',
                    'description' => 'Voir les zones',
                ],
                [
                    'label' => 'Gérer les zones',
                    'name' => 'manage-zones',
                    'description' => 'Gérer les zones',
                ],
                [
                    'label' => 'Créer une zone',
                    'name' => 'create-zones',
                    'description' => 'Créer une zone',
                ],
                [
                    'label' => 'Modifier une zone',
                    'name' => 'edit-zones',
                    'description' => 'Modifier une zone',
                ],
            ],

            // Commission Management
            'commissions' => [
                [
                    'label' => 'Voir les commissions',
                    'name' => 'view-commissions',
                    'description' => 'Voir les commissions',
                ],
                [
                    'label' => 'Gérer les commissions',
                    'name' => 'manage-commissions',
                    'description' => 'Gérer les commissions',
                ],
            ],

            // Reports
            'reports' => [
                [
                    'label' => 'Voir les rapports',
                    'name' => 'view-reports',
                    'description' => 'Voir les rapports',
                ],
                [
                    'label' => 'Exporter les rapports',
                    'name' => 'export-reports',
                    'description' => 'Exporter les rapports',
                ],
            ],

            // Settings
            'manage-settings' => [
                [
                    'label' => 'Gérer les paramètres',
                    'name' => 'manage-settings',
                    'description' => 'Gérer les paramètres',
                ],
            ],

            // Testimonials
            'testimonials' => [
                [
                    'label' => 'Voir les avis',
                    'name' => 'view-testimonials',
                    'description' => 'Voir les avis',
                ],
                [
                    'label' => 'Modérer les avis',
                    'name' => 'moderate-testimonials',
                    'description' => 'Modérer les avis',
                ],
            ],
        ];

        // Créer toutes les permissions
        foreach ($permissions as $group => $items) {
            foreach ($items as $item) {
                Permission::firstOrCreate(
                    ['name' => $item['name']], // clé de recherche unique
                    [
                        'label'       => $item['label'],
                        'description' => $item['description'],
                        'guard_name'  => 'web',
                    ]
                );
            }
        }

        // Rôle Admin — toutes les permissions
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'label'       => 'Administrateur',
                'description' => 'Accès complet à toutes les fonctionnalités du système',
                'guard_name'  => 'web',
            ]
        );
        $adminRole->syncPermissions(Permission::all());

        // Rôle Driver
        $driverRole = Role::firstOrCreate(
            ['name' => 'driver'],
            [
                'label'       => 'Agent',
                'description' => 'Accès aux fonctionnalités liées aux réservations et aux trajets',
                'guard_name'  => 'web',
            ]
        );
        $driverRole->syncPermissions([
            'view-bookings',
            'create-bookings',
            'edit-bookings',
            'view-payments',
            'view-pricing',
            'view-zones',
            'view-testimonials',
        ]);

        // Rôle Client
        $clientRole = Role::firstOrCreate(
            ['name' => 'client'],
            [
                'label'       => 'Client',
                'description' => 'Accès aux fonctionnalités liées aux réservations',
                'guard_name'  => 'web',
            ]
        );
        $clientRole->syncPermissions([
            'create-bookings',
            'view-bookings',
            'edit-bookings',
            'view-payments',
            'view-pricing',
            'view-zones',
        ]);

        $this->command->info('Rôles et permissions créés avec succès!');
    }
}
