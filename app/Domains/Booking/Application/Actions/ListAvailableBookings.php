<?php

namespace App\Domains\Booking\Application\Actions;

use App\Models\Booking;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Les courses qu'un agent peut prendre.
 *
 * Implémentation NEUVE de BookingService::getAvailableBookings(), prouvée équivalente par
 * AvailableBookingsDifferentialTest — dix formes, trois observateurs, listes ordonnées.
 * Les neuf branches de l'originale y sont nommées une par une : c'est la seule différence
 * assumée, et elle ne change aucune condition.
 *
 * Règle de lecture : `subscription_driver_id` RÉSERVE une course qui reste `pending` ;
 * `driver_id` désigne l'agent qui l'a acceptée. Confondre les deux fait basculer la
 * moitié de la matrice.
 */
final class ListAvailableBookings
{
    /** @return Collection<int, Booking> */
    public function __invoke(string $driverId): Collection
    {
        return Booking::with(['parentBooking.user'])
            ->where('status', 'pending')
            ->where(fn (Builder $q) => $this->visibilite($q, $driverId))
            // PostgreSQL : additionner une date et une heure donne un timestamp. Cet
            // ordre est celui du Blade, et le test différentiel le compare.
            ->orderByRaw('(pickup_date::date + pickup_time::time) ASC')
            ->get();
    }

    private function visibilite(Builder $query, string $driverId): void
    {
        $query
            ->where(fn (Builder $q) => $this->courseUniqueAllerSimple($q))
            ->orWhere(fn (Builder $q) => $this->courseUniqueAllerRetour($q))
            ->orWhere(fn (Builder $q) => $this->abonnementParentSansTitulaire($q))
            ->orWhere(fn (Builder $q) => $this->abonnementParentDeCetAgent($q, $driverId))
            ->orWhere(fn (Builder $q) => $this->enfantAbonnementDeCetAgent($q, $driverId))
            ->orWhere(fn (Builder $q) => $this->enfantAbonnementRevoque($q))
            ->orWhere(fn (Builder $q) => $this->retourSimpleDeCetAgent($q, $driverId))
            ->orWhere(fn (Builder $q) => $this->retourAbonnementDeCetAgent($q, $driverId))
            ->orWhere(fn (Builder $q) => $this->retourRevoque($q));
    }

    /** 1. Course unique aller simple, sans aller-retour → tout le monde. */
    private function courseUniqueAllerSimple(Builder $q): void
    {
        $q->where('is_recurring', false)
            ->whereNull('parent_booking_id')
            ->where('trip_type', 'go')
            ->where('round_trip', false);
    }

    /** 2. Course unique aller avec aller-retour → tout le monde. */
    private function courseUniqueAllerRetour(Builder $q): void
    {
        $q->where('is_recurring', false)
            ->whereNull('parent_booking_id')
            ->where('trip_type', 'go')
            ->where('round_trip', true);
    }

    /** 3. Abonnement parent sans titulaire → tout le monde. */
    private function abonnementParentSansTitulaire(Builder $q): void
    {
        $q->where('is_recurring', true)
            ->whereNull('parent_booking_id')
            ->whereNull('subscription_driver_id')
            ->where('is_revoked', false);
    }

    /** 4. Abonnement parent lié à cet agent → lui seul. */
    private function abonnementParentDeCetAgent(Builder $q, string $driverId): void
    {
        $q->where('is_recurring', true)
            ->whereNull('parent_booking_id')
            ->where('subscription_driver_id', $driverId)
            ->where('is_revoked', false);
    }

    /** 5. Enfant d'abonnement lié à cet agent → lui seul. */
    private function enfantAbonnementDeCetAgent(Builder $q, string $driverId): void
    {
        $q->whereNotNull('parent_booking_id')
            ->where('is_recurring', false)
            ->where('trip_type', 'go')
            ->where('subscription_driver_id', $driverId)
            ->where('is_revoked', false)
            ->whereHas('parentBooking', fn ($p) => $p->where('is_recurring', true));
    }

    /** 6. Enfant d'abonnement révoqué → tout le monde. */
    private function enfantAbonnementRevoque(Builder $q): void
    {
        $q->whereNotNull('parent_booking_id')
            ->where('is_recurring', false)
            ->where('is_revoked', true)
            ->whereHas('parentBooking', fn ($p) => $p->where('is_recurring', true));
    }

    /**
     * 7. Course retour simple liée à cet agent → lui seul.
     *
     * Rendue visible par AcceptBooking au moment où l'aller est accepté : c'est ce qui
     * distingue cette branche de la n°3 de la matrice, où la course retour n'a pas encore
     * de titulaire et n'est donc visible de personne.
     */
    private function retourSimpleDeCetAgent(Builder $q, string $driverId): void
    {
        $q->whereNotNull('parent_booking_id')
            ->where('trip_type', 'return')
            ->where('is_recurring', false)
            ->where('subscription_driver_id', $driverId)
            ->whereHas('parentBooking', fn ($p) => $p->where('is_recurring', false));
    }

    /** 8. Course retour d'abonnement liée à cet agent → lui seul. */
    private function retourAbonnementDeCetAgent(Builder $q, string $driverId): void
    {
        $q->whereNotNull('parent_booking_id')
            ->where('trip_type', 'return')
            ->where('is_recurring', false)
            ->where('subscription_driver_id', $driverId)
            ->where('is_revoked', false)
            ->whereHas('parentBooking', fn ($p) => $p->where('is_recurring', true));
    }

    /**
     * 9. Course retour révoquée → tout le monde.
     *
     * Volontairement SANS condition sur parent_booking_id ni sur le parent, à l'inverse
     * des branches 7 et 8 : c'est ainsi dans l'originale. Ajouter la condition qui semble
     * manquer changerait la matrice.
     */
    private function retourRevoque(Builder $q): void
    {
        $q->where('trip_type', 'return')->where('is_revoked', true);
    }
}
