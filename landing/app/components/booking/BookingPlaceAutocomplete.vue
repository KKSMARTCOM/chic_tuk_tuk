<script setup lang="ts">
/**
 * Champ de lieu avec autocomplétion Nominatim (OpenStreetMap), restreinte au Bénin.
 * Transposition de setupAutocomplete() de resources/views/pages/index.blade.php.
 */
interface NominatimPlace {
  place_id: number
  display_name: string
  lat: string
  lon: string
}

defineProps<{
  inputId: string
  label: string
  placeholder: string
  error?: string
}>()

const text = defineModel<string>({ required: true })
const lat = defineModel<number | null>('lat', { required: true })
const lng = defineModel<number | null>('lng', { required: true })

type Panel = 'hidden' | 'loading' | 'results' | 'empty' | 'error'

const panel = ref<Panel>('hidden')
const results = ref<NominatimPlace[]>([])
const root = ref<HTMLElement | null>(null)

let debounce: ReturnType<typeof setTimeout> | undefined
// Ignore les réponses arrivées après une recherche plus récente.
let latestSearch = 0

async function search(query: string) {
  const searchId = ++latestSearch
  panel.value = 'loading'

  try {
    const places = await $fetch<NominatimPlace[]>('https://nominatim.openstreetmap.org/search', {
      query: { format: 'json', countrycodes: 'bj', q: query },
    })
    if (searchId !== latestSearch) return

    results.value = places
    panel.value = places.length ? 'results' : 'empty'
  }
  catch {
    if (searchId === latestSearch) panel.value = 'error'
  }
}

function onInput(event: Event) {
  // Le texte ne correspond plus à la ville choisie : ses coordonnées ne sont plus
  // valables. (L'original les conservait, et envoyait donc les coordonnées de
  // l'ancienne ville sous le nom de la nouvelle.)
  lat.value = null
  lng.value = null

  clearTimeout(debounce)
  // Lire la saisie sur l'événement, pas sur `text` : avec un v-model piloté par le
  // parent, defineModel ne reflète la nouvelle valeur qu'après le re-rendu du parent.
  // `text.value` renverrait ici la saisie précédente, avec une frappe de retard.
  const query = (event.target as HTMLInputElement).value.trim()

  if (query.length < 3) {
    panel.value = 'hidden'
    return
  }

  debounce = setTimeout(() => search(query), 400)
}

function select(place: NominatimPlace) {
  text.value = place.display_name
  lat.value = Number.parseFloat(place.lat)
  lng.value = Number.parseFloat(place.lon)
  panel.value = 'hidden'
}

function clear() {
  clearTimeout(debounce)
  text.value = ''
  lat.value = null
  lng.value = null
  panel.value = 'hidden'
}

// Ferme les suggestions au clic en dehors du champ.
function onDocumentClick(event: MouseEvent) {
  if (root.value && !root.value.contains(event.target as Node)) panel.value = 'hidden'
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick)
  clearTimeout(debounce)
})
</script>

<template>
  <div ref="root" class="mb-4">
    <label :for="inputId" class="block text-gray-700 font-semibold mb-2">
      {{ label }} <span class="text-red-500">*</span>
    </label>
    <div class="relative">
      <input
        :id="inputId"
        v-model="text"
        type="text"
        autocomplete="off"
        class="w-full px-4 py-3 border rounded-lg outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent"
        :class="{ 'border-red-500': error }"
        :placeholder="placeholder"
        @input="onInput"
      >

      <!-- Bouton clear -->
      <button
        v-show="text"
        type="button"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"
        aria-label="Effacer"
        @click="clear"
      >
        ✕
      </button>
    </div>
    <p v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</p>

    <div class="relative">
      <div
        v-if="panel !== 'hidden'"
        class="bg-white border rounded mt-1 max-h-[200px] w-full overflow-y-scroll absolute top-0 left-0 z-50"
      >
        <div v-if="panel === 'loading'" class="p-3 space-y-2">
          <div class="h-4 bg-gray-200 animate-pulse rounded" />
          <div class="h-4 bg-gray-200 animate-pulse rounded" />
          <div class="h-4 bg-gray-200 animate-pulse rounded" />
        </div>

        <template v-else-if="panel === 'results'">
          <div
            v-for="place in results"
            :key="place.place_id"
            class="p-2 hover:bg-gray-100 cursor-pointer text-sm"
            @click="select(place)"
          >
            {{ place.display_name }}
          </div>
        </template>

        <div v-else-if="panel === 'empty'" class="p-3 text-sm text-gray-500">
          Aucune ville ne correspond à votre recherche. Soyez plus précis (ex: Cotonou, Abomey-Calavi...).
        </div>

        <div v-else class="p-3 text-sm text-gray-500">
          Erreur lors de la recherche. Vérifiez votre connexion et réessayez.
        </div>
      </div>
    </div>
  </div>
</template>
