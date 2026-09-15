<!-- Transposition de resources/views/pages/components/how_it_work.blade.php. -->
<script setup lang="ts">
const steps = [
  {
    icon: 'fa-mobile-alt',
    title: 'Réservez en ligne',
    text: 'Remplissez le formulaire avec vos informations de trajet. C\'est simple et rapide !',
  },
  {
    icon: 'fa-user-check',
    title: 'Confirmation instantanée',
    text: 'Un Agent accepte votre réservation et vous recevez une confirmation immédiate par whatsapp ou par appel téléphonique.',
  },
  {
    icon: 'fa-route',
    title: 'Profitez de votre trajet',
    text: 'Votre Agent vous attend à l\'heure et au lieu convenus. Bon voyage !',
  },
]
</script>

<template>
  <section id="comment-ca-marche" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl font-bold text-gray-800 mb-4">Comment ça marche ?</h2>
        <p class="text-xl text-gray-600">Réservez votre tricycle en 3 étapes simples</p>
      </div>

      <div class="grid md:grid-cols-3 gap-12">
        <div v-for="step in steps" :key="step.title" class="text-center">
          <div class="w-20 h-20 bg-[#FFE7C1] rounded-full flex items-center justify-center text-black text-3xl font-bold mx-auto mb-6">
            <i class="fas text-3xl text-gray-800" :class="step.icon" />
          </div>

          <h3 class="text-2xl font-bold text-gray-800 mb-4">{{ step.title }}</h3>
          <p class="text-gray-600">{{ step.text }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
