<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Bienvenue sur Chic Tuk Tuk',
                'message' => 'Votre compte a été créé avec succès. Vous pouvez maintenant réserver vos trajets.',
                'type' => 'success',
                'is_read' => false,
            ]);

            if ($user->role === 'driver') {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Nouveau trajet disponible',
                    'message' => 'Un nouveau trajet est disponible dans votre zone. Vérifiez vos courses disponibles.',
                    'type' => 'info',
                    'is_read' => false,
                ]);
            }

            Notification::create([
                'user_id' => $user->id,
                'title' => 'Mise à jour de l\'application',
                'message' => 'Une nouvelle version de l\'application est disponible avec des améliorations.',
                'type' => 'warning',
                'is_read' => true,
            ]);
        }
    }
}
