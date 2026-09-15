/**
 * Contrats de l'API Laravel consommés par le landing.
 *
 * Écrits à la main pour l'instant : ils reflètent les classes Data du backend
 * (backend : app/Domains/Booking/Application/Data/*). Ils seront remplacés par des
 * types générés depuis ces classes (spatie/laravel-typescript-transformer) — toute
 * modification d'un côté doit être répercutée de l'autre d'ici là.
 */

export type WeekDays = 'lun_ven' | 'lun_sam' | 'lun_dim'

/** PriceQuoteData — GET /public/pricing/quote */
export interface PriceQuote {
  distance_km: number
  base_price: number
  go_price: number
  return_price: number | null
  trip_price: number
  days: number
  total_price: number
  surcharge_amount: number
  surcharge_free_window: string
}

/** BookingConfirmationData — POST /public/bookings (201) */
export interface BookingConfirmation {
  booking_number: string
  status: string
  status_label: string
  from_location: string
  to_location: string
  pickup_date: string
  pickup_time: string
  round_trip: boolean
  return_time: string | null
  is_recurring: boolean
  days: number
  total_price: number
  message: string
}

/** Enveloppe d'erreur normalisée — App\Shared\Http\ApiExceptionRenderer */
export interface ApiErrorBody {
  message: string
  code: string
  errors?: Record<string, string[]>
}
