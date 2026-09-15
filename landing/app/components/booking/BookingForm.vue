<script setup lang="ts">
/**
 * Formulaire de réservation en 3 étapes + écran de succès.
 * Transposition du formulaire et du script jQuery de backend/resources/views/pages/index.blade.php.
 *
 * Différence de fond avec l'original : aucun prix n'est calculé ici. Le récapitulatif
 * affiche le devis renvoyé par l'API (GET /public/pricing/quote), qui applique la
 * même logique que la création de la réservation. L'original recalculait la
 * majoration horaire en JavaScript et avait divergé du serveur (tranche 7h au lieu de 6h).
 */
import type { BookingConfirmation, PriceQuote, WeekDays } from '~/types/api'
import { ApiRequestError } from '~/composables/useApi'

type Step = 1 | 2 | 3 | 4

const WEEK_DAYS_OPTIONS: { value: WeekDays, label: string }[] = [
  { value: 'lun_ven', label: 'Lun → Ven (5j/7)' },
  { value: 'lun_sam', label: 'Lun → Sam (6j/7)' },
  { value: 'lun_dim', label: 'Lun → Dim (7j/7)' },
]

/** Étape portant chaque champ de l'API, pour y ramener l'utilisateur en cas d'erreur. */
const FIELD_STEP: Record<string, Step> = {
  from_location: 1,
  to_location: 1,
  pickup_date: 2,
  pickup_time: 2,
  days: 2,
  week_days: 2,
  round_trip: 2,
  return_time: 2,
  phone: 3,
  special_requests: 3,
}

/** Les coordonnées sont des champs cachés : leurs erreurs s'affichent sous le champ de lieu. */
const FIELD_ALIASES: Record<string, string> = {
  from_lat: 'from_location',
  from_lng: 'from_location',
  to_lat: 'to_location',
  to_lng: 'to_location',
}

const PHONE_PATTERN = /^[0-9+\-\s()]{10,20}$/

function initialForm() {
  return {
    fromLocation: '',
    fromLat: null as number | null,
    fromLng: null as number | null,
    toLocation: '',
    toLat: null as number | null,
    toLng: null as number | null,
    pickupDate: '',
    pickupTime: '',
    // Course simple
    simpleRoundTrip: false,
    simpleReturnTime: '',
    // Abonnement
    multiDay: false,
    daysInput: '2',
    weekDays: 'lun_ven' as WeekDays,
    roundTrip: false,
    returnTime: '',
    // Détails
    phone: '',
    specialRequests: '',
  }
}

const { request } = useApi()

const step = ref<Step>(1)
const form = reactive(initialForm())
const errors = reactive<Record<string, string>>({})
const validationErrors = ref<string[]>([])
const generalError = ref('')
const submitting = ref(false)

const quote = ref<PriceQuote | null>(null)
const quoteStatus = ref<'idle' | 'loading' | 'ready' | 'error'>('idle')

// Date minimale : demain. Calculée dans le navigateur pour ne pas dépendre du fuseau du serveur SSR.
const minDate = ref('')
onMounted(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  minDate.value = toLocalIsoDate(tomorrow)
})

// ------------------------------------------------------------------
// Valeurs dérivées
// ------------------------------------------------------------------

const days = computed(() => {
  if (!form.multiDay) return 1
  const value = Number.parseInt(form.daysInput, 10)
  return Number.isNaN(value) || value < 2 ? 2 : value
})

const isRoundTrip = computed(() => (form.multiDay ? form.roundTrip : form.simpleRoundTrip))
const returnTime = computed(() => (form.multiDay ? form.returnTime : form.simpleReturnTime))

const hasRoute = computed(() =>
  form.fromLat !== null && form.fromLng !== null && form.toLat !== null && form.toLng !== null,
)

const weekDaysLabel = computed(() => WEEK_DAYS_OPTIONS.find(o => o.value === form.weekDays)?.label ?? '--')

const surchargeNotice = computed(() => quote.value
  ? `Une majoration de ${formatNumber(quote.value.surcharge_amount)} FCFA s'applique en dehors de la tranche ${quote.value.surcharge_free_window}.`
  : 'Une majoration s\'applique en dehors de la tranche horaire normale.')

const readyQuote = computed(() => (quoteStatus.value === 'ready' ? quote.value : null))

const totalLabel = computed(() => {
  if (quoteStatus.value === 'error') return 'Erreur de calcul'
  return readyQuote.value ? formatFcfa(readyQuote.value.total_price) : '-- FCFA'
})

// ------------------------------------------------------------------
// Devis
// ------------------------------------------------------------------

let quoteDebounce: ReturnType<typeof setTimeout> | undefined
let latestQuote = 0

async function fetchQuote() {
  const quoteId = ++latestQuote

  try {
    const result = await request<PriceQuote>('/public/pricing/quote', {
      query: {
        from_lng: form.fromLng,
        from_lat: form.fromLat,
        to_lng: form.toLng,
        to_lat: form.toLat,
        pickup_time: form.pickupTime || undefined,
        round_trip: isRoundTrip.value ? 1 : 0,
        return_time: isRoundTrip.value ? (returnTime.value || undefined) : undefined,
        days: days.value,
      },
    })
    if (quoteId !== latestQuote) return

    quote.value = result
    quoteStatus.value = 'ready'
  }
  catch {
    if (quoteId !== latestQuote) return

    quote.value = null
    quoteStatus.value = 'error'
  }
}

/*
 * Le prix dépend du trajet, mais aussi des horaires (majoration), de l'aller-retour et
 * de la durée d'un abonnement : le devis est redemandé à chaque changement, après un
 * court délai. La distance est mise en cache côté API — ces appels répétés ne
 * consomment pas le quota OpenRouteService.
 */
watch(
  () => [form.fromLat, form.fromLng, form.toLat, form.toLng, form.pickupTime, isRoundTrip.value, returnTime.value, days.value],
  () => {
    clearTimeout(quoteDebounce)

    if (!hasRoute.value) {
      latestQuote++
      quote.value = null
      quoteStatus.value = 'idle'
      return
    }

    quoteStatus.value = 'loading'
    quoteDebounce = setTimeout(fetchQuote, 300)
  },
)

onBeforeUnmount(() => clearTimeout(quoteDebounce))

// ------------------------------------------------------------------
// Options aller-retour / abonnement (mêmes règles d'exclusion que l'original)
// ------------------------------------------------------------------

watch(() => form.multiDay, (multiDay) => {
  if (multiDay) {
    form.simpleRoundTrip = false
    form.simpleReturnTime = ''
  }
})

watch(() => form.simpleRoundTrip, (checked) => {
  if (!checked) form.simpleReturnTime = ''
})

watch(() => form.roundTrip, (checked) => {
  if (!checked) form.returnTime = ''
})

function onDaysInput() {
  form.daysInput = form.daysInput.replace(/[^0-9]/g, '')
}

function onDaysBlur() {
  form.daysInput = String(days.value)
}

// ------------------------------------------------------------------
// Effacement des erreurs à la correction
// ------------------------------------------------------------------

/*
 * Une erreur disparaît dès que l'utilisateur modifie le champ concerné, sans attendre le
 * clic suivant. L'original la laissait affichée jusqu'à la validation suivante — y compris
 * « Veuillez choisir une ville » sous une ville pourtant correctement sélectionnée.
 * Les erreurs renvoyées par l'API suivent la même règle.
 */
const FIELD_SOURCES: Record<string, () => unknown> = {
  from_location: () => [form.fromLocation, form.fromLat],
  to_location: () => [form.toLocation, form.toLat],
  pickup_date: () => form.pickupDate,
  pickup_time: () => form.pickupTime,
  return_time: () => [form.simpleReturnTime, form.returnTime, isRoundTrip.value],
  days: () => form.daysInput,
  week_days: () => form.weekDays,
  phone: () => form.phone,
  special_requests: () => form.specialRequests,
}

for (const [field, source] of Object.entries(FIELD_SOURCES)) {
  watch(source, () => { delete errors[field] })
}

// ------------------------------------------------------------------
// Validation par étape (côté navigateur ; l'API reste la source de vérité)
// ------------------------------------------------------------------

const STEP_FIELDS: Record<1 | 2 | 3, string[]> = {
  1: ['from_location', 'to_location'],
  2: ['pickup_date', 'pickup_time', 'return_time'],
  3: ['phone'],
}

function validateStep(current: 1 | 2 | 3): boolean {
  for (const field of STEP_FIELDS[current]) delete errors[field]

  if (current === 1) {
    if (!form.fromLocation.trim()) errors.from_location = 'Ce champ est obligatoire'
    else if (form.fromLat === null) errors.from_location = 'Veuillez choisir une ville dans la liste de suggestions.'

    if (!form.toLocation.trim()) errors.to_location = 'Ce champ est obligatoire'
    else if (form.toLat === null) errors.to_location = 'Veuillez choisir une ville dans la liste de suggestions.'
  }

  if (current === 2) {
    if (!form.pickupDate) errors.pickup_date = 'Ce champ est obligatoire'
    else if (minDate.value && form.pickupDate < minDate.value) errors.pickup_date = 'Choisissez une date à partir de demain'

    if (!form.pickupTime) errors.pickup_time = 'Ce champ est obligatoire'

    if (isRoundTrip.value) {
      if (!returnTime.value) errors.return_time = 'L\'heure de retour est obligatoire'
      else if (form.pickupTime && returnTime.value <= form.pickupTime) errors.return_time = 'L\'heure de retour doit être après l\'heure de départ'
    }
  }

  if (current === 3) {
    if (!form.phone.trim()) errors.phone = 'Ce champ est obligatoire'
    else if (!PHONE_PATTERN.test(form.phone.trim())) errors.phone = 'Numéro invalide'
  }

  return STEP_FIELDS[current].every(field => !errors[field])
}

function nextStep(target: 2 | 3) {
  if (step.value === 4 || !validateStep(step.value)) return
  step.value = target
}

function prevStep(target: 1 | 2) {
  step.value = target
}

// ------------------------------------------------------------------
// Envoi
// ------------------------------------------------------------------

function payload() {
  return {
    from_location: form.fromLocation,
    to_location: form.toLocation,
    from_lat: form.fromLat,
    from_lng: form.fromLng,
    to_lat: form.toLat,
    to_lng: form.toLng,
    pickup_date: form.pickupDate,
    pickup_time: form.pickupTime,
    phone: form.phone.trim(),
    days: days.value,
    round_trip: isRoundTrip.value,
    return_time: isRoundTrip.value ? returnTime.value : null,
    week_days: form.multiDay ? form.weekDays : null,
    special_requests: form.specialRequests.trim() || null,
  }
}

async function onSubmit() {
  // Entrée dans un champ des étapes 1 et 2 : passer à l'étape suivante, pas envoyer.
  if (step.value === 1) return nextStep(2)
  if (step.value === 2) return nextStep(3)
  if (step.value !== 3 || submitting.value || !validateStep(3)) return

  submitting.value = true
  validationErrors.value = []
  generalError.value = ''

  try {
    await request<BookingConfirmation>('/public/bookings', { method: 'POST', body: payload() })
    step.value = 4
  }
  catch (error) {
    if (error instanceof ApiRequestError && error.status === 422 && error.hasFieldErrors) {
      let firstStep: Step = 3

      for (const [apiField, messages] of Object.entries(error.errors)) {
        const field = FIELD_ALIASES[apiField] ?? apiField
        errors[field] = messages[0] ?? ''
        firstStep = Math.min(firstStep, FIELD_STEP[field] ?? 3) as Step
      }

      validationErrors.value = Object.values(error.errors).flat()
      step.value = firstStep
    }
    else {
      generalError.value = error instanceof ApiRequestError
        ? error.message
        : 'Une erreur est survenue. Veuillez réessayer !'
    }
  }
  finally {
    submitting.value = false
  }
}

function newBooking() {
  latestQuote++
  clearTimeout(quoteDebounce)
  Object.assign(form, initialForm())
  for (const field of Object.keys(errors)) delete errors[field]
  validationErrors.value = []
  generalError.value = ''
  quote.value = null
  quoteStatus.value = 'idle'
  step.value = 1
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Indicateur d'étapes : l'écran de succès (4) garde l'étape 3 active, comme l'original.
const activeIndicator = computed(() => (step.value === 4 ? 3 : step.value))

function indicatorClass(index: number) {
  return index === activeIndicator.value ? 'step-active' : 'bg-[#FFE7C1] text-gray-600'
}

const inputClass = 'w-full px-4 py-3 border rounded-lg outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent'
const borderFor = (field: string) => (errors[field] ? 'border-red-500' : 'border-gray-300')
</script>

<template>
  <div class="bg-white rounded-2xl shadow-2xl p-8 max-h-[600px] overflow-y-auto custom-scrollbar">
    <h3 class="text-2xl font-bold text-gray-800 mb-6">Réservez votre course</h3>

    <div v-if="generalError" class="bg-red-100 text-red-700 p-3 mb-3 rounded">
      {{ generalError }}
    </div>

    <div v-if="validationErrors.length" class="bg-red-50 border-l-4 border-red-600 p-4 mb-4">
      <ul class="text-sm text-red-800">
        <li v-for="message in validationErrors" :key="message">• {{ message }}</li>
      </ul>
    </div>

    <!-- Indicateur d'étapes -->
    <div class="flex justify-between mb-8">
      <div class="flex flex-col items-center flex-1">
        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold mb-2" :class="indicatorClass(1)">1</div>
        <span class="text-xs text-gray-600">Trajet</span>
      </div>
      <div class="flex-1 flex items-center justify-center" style="margin-bottom: 26px;">
        <div class="h-1 bg-gray-300 w-full" />
      </div>
      <div class="flex flex-col items-center flex-1">
        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold mb-2" :class="indicatorClass(2)">2</div>
        <span class="text-xs text-gray-600">Date & Heure</span>
      </div>
      <div class="flex-1 flex items-center justify-center" style="margin-bottom: 26px;">
        <div class="h-1 bg-gray-300 w-full" />
      </div>
      <div class="flex flex-col items-center flex-1">
        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold mb-2" :class="indicatorClass(3)">3</div>
        <span class="text-xs text-gray-600">Détails</span>
      </div>
    </div>

    <form novalidate @submit.prevent="onSubmit">
      <!-- Étape 1: Trajet -->
      <div v-show="step === 1">
        <BookingPlaceAutocomplete
          v-model="form.fromLocation"
          v-model:lat="form.fromLat"
          v-model:lng="form.fromLng"
          input-id="from_input"
          label="Point de départ"
          placeholder="Entrez votre ville de départ"
          :error="errors.from_location"
        />

        <BookingPlaceAutocomplete
          v-model="form.toLocation"
          v-model:lat="form.toLat"
          v-model:lng="form.toLng"
          input-id="to_input"
          label="Destination"
          placeholder="Où souhaitez-vous aller ?"
          :error="errors.to_location"
        />

        <div v-if="quoteStatus !== 'idle'" class="my-4 text-sm text-gray-700">
          Prix estimé :
          <span v-if="quoteStatus === 'loading'" class="block p-3 space-y-2">
            <span class="block h-4 bg-gray-200 animate-pulse rounded" />
            <span class="block h-4 bg-gray-200 animate-pulse rounded" />
            <span class="block h-4 bg-gray-200 animate-pulse rounded" />
          </span>
          <span v-else-if="quoteStatus === 'error'">Erreur de calcul</span>
          <span v-else-if="quote">{{ formatFcfa(quote.base_price) }}</span>
        </div>

        <button
          type="button"
          class="w-full py-3 bg-[#286b41] text-white rounded-lg font-semibold hover:opacity-90 transition"
          @click="nextStep(2)"
        >
          Suivant <i class="fas fa-arrow-right ml-2" />
        </button>
      </div>

      <!-- Étape 2: Date & Heure -->
      <div v-show="step === 2">
        <div class="mb-4">
          <p class="text-xs text-gray-500 mb-1">ℹ️ {{ surchargeNotice }}</p>

          <label class="block text-gray-700 font-semibold mb-2">Date et heure <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <input
                id="pickup_date"
                v-model="form.pickupDate"
                type="date"
                :min="minDate"
                :class="[inputClass, borderFor('pickup_date')]"
              >
              <p v-if="errors.pickup_date" class="text-red-500 text-sm mt-1">{{ errors.pickup_date }}</p>
            </div>
            <div>
              <input
                id="pickup_time"
                v-model="form.pickupTime"
                type="time"
                :class="[inputClass, borderFor('pickup_time')]"
              >
              <p v-if="errors.pickup_time" class="text-red-500 text-sm mt-1">{{ errors.pickup_time }}</p>
            </div>
          </div>
        </div>

        <div v-show="!form.multiDay" class="my-4">
          <div class="flex items-center gap-3">
            <input
              id="simple_round_trip"
              v-model="form.simpleRoundTrip"
              type="checkbox"
              class="w-4 h-4 accent-[#286b41] cursor-pointer"
            >
            <label for="simple_round_trip" class="text-gray-700 font-semibold cursor-pointer">
              Aller-Retour
            </label>
          </div>

          <!-- Heure de retour -->
          <div v-show="form.simpleRoundTrip" class="mt-3">
            <label for="simple_return_time" class="block text-gray-700 font-semibold mb-2">
              Heure de retour <span class="text-red-500">*</span>
            </label>
            <input
              id="simple_return_time"
              v-model="form.simpleReturnTime"
              type="time"
              :class="[inputClass, borderFor('return_time')]"
            >
            <p v-if="errors.return_time" class="text-red-500 text-sm mt-1">{{ errors.return_time }}</p>
          </div>
        </div>

        <!-- Option multi-jours -->
        <div class="mb-4">
          <label class="inline-flex items-center text-gray-700 font-semibold">
            <input
              id="multi_day"
              v-model="form.multiDay"
              type="checkbox"
              class="w-4 h-4 accent-[#286b41] cursor-pointer mr-3"
            >
            <span>Réservation sur plusieurs jours</span>
          </label>

          <div v-show="form.multiDay" class="mt-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-2">
              <!-- Nombre de jours -->
              <div>
                <label for="days_input" class="block text-gray-700 font-semibold mb-2">
                  Nombre de jours <span class="text-red-500">*</span>
                </label>
                <input
                  id="days_input"
                  v-model="form.daysInput"
                  type="text"
                  inputmode="numeric"
                  :class="[inputClass, 'border-gray-300']"
                  placeholder="Entrez le nombre de jours"
                  @input="onDaysInput"
                  @blur="onDaysBlur"
                >
              </div>

              <!-- Jours de la semaine -->
              <div>
                <label for="week_days" class="block text-gray-700 font-semibold mb-2">
                  Jours de circulation <span class="text-red-500">*</span>
                </label>
                <select
                  id="week_days"
                  v-model="form.weekDays"
                  :class="[inputClass, 'border-gray-300 bg-white']"
                >
                  <option v-for="option in WEEK_DAYS_OPTIONS" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <!-- Aller-Retour -->
              <div class="flex items-center gap-3">
                <input
                  id="round_trip"
                  v-model="form.roundTrip"
                  type="checkbox"
                  class="w-4 h-4 accent-[#286b41] cursor-pointer"
                >
                <label for="round_trip" class="text-gray-700 font-semibold cursor-pointer">
                  Aller-Retour
                </label>
              </div>

              <!-- Heure de retour (visible uniquement si aller-retour coché) -->
              <div v-show="form.roundTrip" class="mt-3">
                <label for="return_time" class="block text-gray-700 font-semibold mb-2">
                  Heure de retour <span class="text-red-500">*</span>
                </label>
                <input
                  id="return_time"
                  v-model="form.returnTime"
                  type="time"
                  :class="[inputClass, borderFor('return_time')]"
                >
                <p v-if="errors.return_time" class="text-red-500 text-sm mt-1">{{ errors.return_time }}</p>
              </div>
            </div>
          </div>
          <p v-if="errors.days" class="text-red-500 text-sm mt-1">{{ errors.days }}</p>
          <p v-if="errors.week_days" class="text-red-500 text-sm mt-1">{{ errors.week_days }}</p>
        </div>

        <div class="flex space-x-4">
          <button
            type="button"
            class="flex-1 py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition"
            @click="prevStep(1)"
          >
            <i class="fas fa-arrow-left mr-2" /> Retour
          </button>
          <button
            type="button"
            class="flex-1 py-3 bg-[#286b41] text-white rounded-lg font-semibold hover:opacity-90 transition"
            @click="nextStep(3)"
          >
            Suivant <i class="fas fa-arrow-right ml-2" />
          </button>
        </div>
      </div>

      <!-- Étape 3: Détails et Confirmation -->
      <div v-show="step === 3">
        <div class="mb-4">
          <label for="phone" class="block text-gray-700 font-semibold mb-2">
            Numéro de téléphone <span class="text-red-500">*</span>
          </label>
          <input
            id="phone"
            v-model="form.phone"
            type="tel"
            placeholder="01 90 12 34 56"
            :class="[inputClass, borderFor('phone')]"
          >
          <p v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone }}</p>
        </div>

        <div class="mb-4">
          <label for="special_requests" class="block text-gray-700 font-semibold mb-2">
            Demandes spéciales, veuillez donner la localisation précise de l'adresse de départ et d'arrivée (optionnel)
          </label>
          <textarea
            id="special_requests"
            v-model="form.specialRequests"
            rows="3"
            :class="[inputClass, 'border-gray-300']"
            placeholder="Bagages volumineux, animaux, etc."
          />
          <p v-if="errors.special_requests" class="text-red-500 text-sm mt-1">{{ errors.special_requests }}</p>
        </div>

        <!-- RÉCAPITULATIF UNIFIÉ -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 space-y-3">
          <p class="text-sm font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-receipt text-[#286b41]" />
            Récapitulatif de la réservation
          </p>

          <!-- Trajet -->
          <div class="flex justify-between text-sm text-gray-600">
            <span class="text-gray-500">De</span>
            <span class="font-medium text-gray-700 text-right max-w-[60%] truncate">{{ form.fromLocation || '--' }}</span>
          </div>
          <div class="flex justify-between text-sm text-gray-600">
            <span class="text-gray-500">Vers</span>
            <span class="font-medium text-gray-700 text-right max-w-[60%] truncate">{{ form.toLocation || '--' }}</span>
          </div>

          <!-- Date & heure -->
          <div class="flex justify-between text-sm text-gray-600">
            <span class="text-gray-500">Date de départ</span>
            <span class="font-medium text-gray-700">{{ form.pickupDate || '--' }}</span>
          </div>
          <div class="flex justify-between text-sm text-gray-600">
            <span class="text-gray-500">Heure</span>
            <span class="font-medium text-gray-700">{{ form.pickupTime || '--' }}</span>
          </div>

          <div class="border-t border-gray-200" />

          <!-- Bloc course unique -->
          <div v-if="!form.multiDay">
            <div class="flex justify-between text-sm text-gray-600">
              <span class="text-gray-500">Type</span>
              <span class="font-medium text-gray-700">{{ isRoundTrip ? 'Trajet unique — Aller-Retour' : 'Trajet unique' }}</span>
            </div>

            <!-- Heure de retour (visible si aller-retour) -->
            <div v-if="isRoundTrip" class="flex justify-between text-sm text-gray-600 mt-2">
              <span class="text-gray-500">Heure de retour</span>
              <span class="font-medium text-gray-700">{{ returnTime || '--' }}</span>
            </div>

            <div class="flex justify-between text-sm text-gray-600 mt-2">
              <span class="text-gray-500">Prix du trajet</span>
              <span class="font-medium text-gray-700">{{ readyQuote ? formatFcfa(readyQuote.go_price) : '-- FCFA' }}</span>
            </div>

            <div v-if="isRoundTrip" class="flex justify-between text-sm text-gray-600 mt-2">
              <span class="text-gray-500">Retour</span>
              <span class="font-medium text-gray-700">{{ readyQuote?.return_price != null ? formatFcfa(readyQuote.return_price) : '-- FCFA' }}</span>
            </div>
          </div>

          <!-- Bloc abonnement -->
          <div v-else class="space-y-2">
            <div class="flex justify-between text-sm text-gray-600">
              <span class="text-gray-500">Type</span>
              <span class="inline-flex items-center gap-1 font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full text-xs">
                <i class="fas fa-rotate" /> Abonnement
              </span>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
              <span class="text-gray-500">Prix par trajet (Aller)</span>
              <span class="font-medium text-gray-700">{{ readyQuote ? formatFcfa(readyQuote.go_price) : '-- FCFA' }}</span>
            </div>
            <div v-if="isRoundTrip" class="flex justify-between text-sm text-gray-600">
              <span class="text-gray-500">Retour</span>
              <span class="font-medium text-gray-700">
                {{ readyQuote?.return_price != null ? formatFcfa(readyQuote.return_price) : '-- FCFA' }}
                {{ returnTime ? `— retour à ${returnTime}` : '' }}
              </span>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
              <span class="text-gray-500">Nombre de jrs</span>
              <span class="font-medium text-gray-700">{{ days }} jour(s)</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
              <span class="text-gray-500 text-nowrap">Jrs de circulat*</span>
              <span class="font-medium text-gray-700 text-nowrap">{{ weekDaysLabel }}</span>
            </div>
          </div>

          <!-- Total -->
          <div class="border-t border-gray-200 pt-3 flex justify-between font-bold text-base text-[#286b41]">
            <span>Total</span>
            <span>{{ totalLabel }}</span>
          </div>
        </div>

        <div class="flex space-x-4">
          <button
            type="button"
            class="flex-1 py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition"
            @click="prevStep(2)"
          >
            <i class="fas fa-arrow-left mr-2" /> Retour
          </button>
          <!-- Désactivé pendant l'envoi : l'original permettait un double clic, donc une double réservation. -->
          <button
            type="submit"
            :disabled="submitting"
            class="flex-1 py-3 gradient-bg text-white rounded-lg font-semibold hover:opacity-90 transition disabled:opacity-60 disabled:cursor-wait"
          >
            Confirmer <i class="fas ml-2" :class="submitting ? 'fa-spinner fa-spin' : 'fa-check'" />
          </button>
        </div>
      </div>

      <!-- Étape 4: Succès (invisible dans l'indicateur) -->
      <div v-if="step === 4">
        <div class="relative overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-emerald-50 p-8">
          <!-- Confettis CSS -->
          <div class="confetti">
            <span v-for="n in 18" :key="n" class="confetti-piece" />
          </div>

          <div class="text-center">
            <div class="mx-auto w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center shadow-lg success-pop">
              <i class="fas fa-check text-4xl text-emerald-600" />
            </div>

            <h4 class="mt-6 text-3xl font-extrabold text-gray-900 success-pop" style="animation-delay:.05s">
              Réservation envoyée !
            </h4>

            <p class="mt-2 text-gray-600 text-base success-pop" style="animation-delay:.1s">
              Merci 🙌 Votre demande a bien été enregistrée. Un chauffeur vous contactera très bientôt.
            </p>

            <div class="mt-8">
              <button
                type="button"
                class="inline-flex items-center justify-center px-7 py-3 rounded-xl bg-[#286b41] text-white font-semibold shadow-lg hover:opacity-95 active:scale-[.99] transition success-pop"
                style="animation-delay:.2s"
                @click="newBooking"
              >
                Nouvelle réservation <i class="fas fa-plus ml-2" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
