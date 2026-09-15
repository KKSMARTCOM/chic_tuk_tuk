<!-- Transposition de resources/views/pages/components/partners.blade.php (partenaires + investisseurs). -->
<script setup lang="ts">
const partners = [
  { name: 'Gozem', logo: '/assets/images/png/gozem.png' },
  { name: 'Yango', logo: '/assets/images/png/yango.png' },
  { name: 'Bajaj', logo: '/assets/images/png/bajaj.png' },
]
</script>

<template>
  <section id="partenaire" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl font-bold text-gray-800 mb-4">Ils nous soutiennent</h2>
        <p class="text-xl text-gray-600">Nos partenaires de confiance</p>
      </div>

      <div class="flex flex-wrap justify-center gap-6">
        <span
          v-for="partner in partners"
          :key="partner.name"
          class="group rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-lg transition p-6 flex items-center justify-center w-48"
        >
          <img
            :src="partner.logo"
            :alt="partner.name"
            class="h-12 w-auto object-cover grayscale contrast-200 opacity-70 group-hover:grayscale-0 group-hover:contrast-100 group-hover:opacity-100 transition duration-300"
            loading="lazy"
          >
        </span>
      </div>
    </div>
  </section>

  <!-- Devenir investisseur. L'original réutilisait l'id « partenaire » (id dupliqué, et
       qu'aucun lien ne cible) : il reçoit ici son propre id. -->
  <section id="investir" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl font-bold text-gray-800 mb-4">Devenir investisseur</h2>
        <p class="text-xl text-gray-600">
          Un modèle de microfinancement participatif pour équiper les chauffeurs de tricycles connectés et rentables.
        </p>
      </div>

      <div class="text-center">
        <a
          href="http://pitch.kksmartcom.com/chictuktuk"
          target="_blank"
          rel="noopener noreferrer"
          class="px-6 py-2 bg-[#286b41] text-white rounded-full hover:opacity-90 shadow-lg shadow-emerald-600/30 transition font-medium"
        >
          En savoir plus
        </a>
      </div>
    </div>
  </section>
</template>
