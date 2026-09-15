<?php

namespace App\Domains\Booking\Application\Actions;

use App\Domains\Booking\Application\Data\CreatePublicBookingData;
use App\Models\Booking;
use App\Services\BookingService;

/**
 * Dépôt d'une demande de réservation depuis le tunnel public.
 *
 * Délègue pour l'instant à BookingService::create(), qui reste la seule implémentation
 * de la création (courses simples, aller-retour, abonnements). Le corps de ce cas
 * d'usage y sera rapatrié lorsque les vues Blade qui appellent encore le service
 * auront disparu — pas avant, pour ne pas maintenir deux logiques en parallèle.
 */
final class CreatePublicBooking
{
    public function __construct(private readonly BookingService $bookings) {}

    public function execute(CreatePublicBookingData $data): Booking
    {
        return $this->bookings->create($data->toServicePayload());
    }
}
