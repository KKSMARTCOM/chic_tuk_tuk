<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FcmNotificationService
{
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $messaging = Firebase::messaging();

        foreach ($user->fcmTokens as $fcmToken) {
            try {
                $message = CloudMessage::withTarget('token', $fcmToken->token)
                    ->withNotification(FcmNotification::create($title, $body))
                    ->withData($data);

                $messaging->send($message);
            } catch (\Exception $e) {
                // Token invalide/expiré → le supprimer
                $fcmToken->delete();
            }
        }
    }

    public function sendToDrivers(string $title, string $body, array $data = []): void
    {
        User::where('profil', 'driver')
            ->with('fcmTokens')
            ->get()
            ->each(fn($driver) => $this->sendToUser($driver, $title, $body, $data));
    }
}
