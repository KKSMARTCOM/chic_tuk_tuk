<?php

namespace App\Domains\Booking\Application\Data;

use App\Models\Booking;
use App\Shared\Data\BaseData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Une page d'historique.
 *
 * L'enveloppe de pagination est écrite à la main plutôt que déléguée au format par
 * défaut de Laravel : ce dernier embarque une quinzaine de champs dont des URL absolues
 * pointant vers le domaine de l'API, inutiles à un front qui construit ses propres liens.
 */
final class BookingHistoryPageData extends BaseData
{
    public function __construct(
        /** @var array<int, BookingHistoryData> */
        public array $data,
        public int $currentPage,
        public int $lastPage,
        public int $perPage,
        public int $total,
    ) {}

    public static function fromPaginator(LengthAwarePaginator $page): self
    {
        return new self(
            data: collect($page->items())
                ->map(fn (Booking $booking) => BookingHistoryData::fromModel($booking))
                ->all(),
            currentPage: $page->currentPage(),
            lastPage: $page->lastPage(),
            perPage: $page->perPage(),
            total: $page->total(),
        );
    }
}
