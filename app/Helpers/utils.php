<?php

use Carbon\Carbon;

if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if (!function_exists('formatDateFr')) {
    function formatDateFr($date)
    {
        return Carbon::parse($date)
            ->locale('fr')
            ->translatedFormat('d M Y');
    }
}

if (!function_exists('formatTimeFr')) {
    function formatTimeFr($date)
    {
        return Carbon::parse($date)
            ->locale('fr')
            ->translatedFormat('H\hi');
    }
}

if (!function_exists('formatDateTimeFr')) {
    function formatDateTimeFr($date)
    {
        return Carbon::parse($date)
            ->locale('fr')
            ->translatedFormat('d M Y à H\hi');
    }
}

if (!function_exists('bookingStatusBadge')) {
    function bookingStatusBadge(string $status): string
    {
        return match ($status) {
            'pending'     => 'bg-yellow-100 text-yellow-800',
            'confirmed'   => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-indigo-100 text-indigo-800',
            'completed'   => 'bg-green-100 text-green-800',
            'cancelled'   => 'bg-red-100 text-red-800',
            default       => 'bg-gray-100 text-gray-800',
        };
    }
}

if (!function_exists('bookingStatusLabel')) {
    function bookingStatusLabel(string $status): string
    {
        return match ($status) {
            'pending'     => 'En attente',
            'confirmed'   => 'Confirmée',
            'in_progress' => 'En cours',
            'completed'   => 'Terminée',
            'cancelled'   => 'Annulée',
            default       => 'Inconnu',
        };
    }
}
