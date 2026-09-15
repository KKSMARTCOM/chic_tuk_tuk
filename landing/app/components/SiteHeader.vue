<script setup lang="ts">
// Transposition de resources/views/inc/frontend/header.blade.php.
const { public: { appUrl } } = useRuntimeConfig()
const loginUrl = `${appUrl}/login`

const { scrollToSection } = useSmoothScroll()
const mobileMenuOpen = ref(false)

const links = [
  { id: 'reservation', label: 'Réserver' },
  { id: 'comment-ca-marche', label: 'Comment ça marche' },
  { id: 'avantages', label: 'Avantages' },
]

function goTo(id: string) {
  mobileMenuOpen.value = false
  scrollToSection(id)
}

// Comme l'original : le menu mobile se referme quand la fenêtre repasse en largeur desktop.
function closeOnDesktop() {
  if (window.innerWidth >= 768) mobileMenuOpen.value = false
}

onMounted(() => window.addEventListener('resize', closeOnDesktop))
onBeforeUnmount(() => window.removeEventListener('resize', closeOnDesktop))
</script>

<template>
  <nav class="bg-white shadow-lg fixed w-full top-0 left-0 right-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-20">
        <!-- Logo -->
        <div class="flex items-center flex-shrink-0">
          <a href="/" class="h-20 w-28 overflow-hidden">
            <img src="/assets/images/png/chic_tuk_tuk_logo_transparent.png" class="h-full w-full object-cover" alt="Logo">
          </a>
        </div>

        <!-- Menu Desktop -->
        <div class="hidden md:flex items-center space-x-8">
          <a
            v-for="link in links"
            :key="link.id"
            :href="`#${link.id}`"
            class="text-gray-700 hover:text-[#286b41] transition font-medium"
            @click.prevent="goTo(link.id)"
          >{{ link.label }}</a>
          <a
            :href="loginUrl"
            class="px-6 py-2 bg-[#286b41] text-white rounded-full hover:opacity-90 shadow-lg shadow-emerald-600/30 transition font-medium"
          >Connexion</a>
        </div>

        <!-- Bouton Hamburger Mobile -->
        <div class="md:hidden flex items-center gap-4">
          <a
            href="https://wa.me/22956141438?text=Bonjour%0AJ'aimerais%20avoir%20plus%20d'informations"
            target="_blank"
            rel="noopener noreferrer"
            class="px-3 py-2 flex items-center gap-2 rounded-md bg-[#286b41] text-white text-sm font-semibold"
          >
            <i class="fa-brands fa-whatsapp font-bold text-lg block" /> Contactez
          </a>

          <button
            type="button"
            class="text-gray-700 hover:text-[#286b41] transition p-2"
            aria-label="Menu"
            :aria-expanded="mobileMenuOpen"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Menu Mobile -->
    <div class="md:hidden bg-white border-t border-gray-200" :class="{ hidden: !mobileMenuOpen }">
      <div class="px-4 pt-2 pb-4 space-y-2">
        <a
          v-for="link in links"
          :key="link.id"
          :href="`#${link.id}`"
          class="block px-4 py-2 text-gray-700 hover:bg-emerald-50 hover:text-[#286b41] transition rounded-lg font-medium"
          @click.prevent="goTo(link.id)"
        >{{ link.label }}</a>
        <a
          :href="loginUrl"
          class="block px-4 py-2 mt-4 bg-[#286b41] text-white rounded-lg hover:opacity-90 shadow-lg shadow-emerald-600/30 transition font-medium text-center"
        >Connexion</a>
      </div>
    </div>
  </nav>
</template>
