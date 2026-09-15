<!-- Transposition de backend/resources/views/pages/components/advantages.blade.php. -->
<script setup lang="ts">
import emblaCarouselVue from 'embla-carousel-vue'
import Autoplay from 'embla-carousel-autoplay'

const advantages = [
  {
    image: '/assets/images/jpg/wifi.jpg',
    title: 'Tricycle connecté',
    text: 'Profitez d’un tricycle équipé d’une connexion Wi-Fi fluide et stable, idéale pour rester connecté, travailler ou se divertir pendant vos trajets.',
  },
  {
    image: '/assets/images/jpg/child1.jpeg',
    title: 'Transport scolaire en tricycle',
    text: 'Réservez un tricycle pour assurer les trajets aller et retour de vos enfants vers l’école en toute sécurité. Une solution pratique, fiable et adaptée pour accompagner les élèves chaque jour.',
  },
  {
    image: '/assets/images/png/pub.png',
    title: 'On fait votre pub',
    text: 'Faites connaître votre activité en affichant votre publicité sur nos tricycles. Votre message circule dans toute la ville et touche un large public de façon visible, dynamique et efficace.',
  },
  {
    image: '/assets/images/jpg/women2.jpeg',
    title: 'Sécurité garantie',
    text: 'Tous nos Agents sont formés et vérifiés pour votre sécurité.',
  },
  {
    image: '/assets/images/jpg/women1.jpeg',
    title: 'Ponctualité',
    text: 'Arrivée à l\'heure garantie pour tous vos trajets.',
  },
]

/*
 * Reproduit la configuration slick d'origine (public/assets/js/custom2.js) :
 * une carte à la fois toutes les 5 s, en boucle, pause au survol, sans flèches ni
 * points. Le nombre de cartes visibles suit les mêmes paliers que slick, en CSS :
 * 1 sous 520 px, 2 sous 992 px, 3 au-delà. Les 20 px autour de chaque carte
 * correspondent à la règle `.slick-slide { margin: 20px }` de custom.css.
 */
const [emblaRef] = emblaCarouselVue(
  { loop: true, align: 'start', slidesToScroll: 1 },
  [Autoplay({ delay: 5000, stopOnMouseEnter: true, stopOnInteraction: false })],
)
</script>

<template>
  <section id="avantages" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl font-bold text-gray-800 mb-4">Ce que nous offrons</h2>
        <p class="text-xl text-gray-600">Des avantages qui font la différence</p>
      </div>

      <div ref="emblaRef" class="overflow-hidden">
        <div class="flex">
          <div
            v-for="advantage in advantages"
            :key="advantage.title"
            class="shrink-0 grow-0 basis-full min-[520px]:basis-1/2 min-[992px]:basis-1/3 p-5"
          >
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden group transition duration-300 hover:-translate-y-2">
              <div class="h-60 overflow-hidden">
                <img
                  :src="advantage.image"
                  :alt="advantage.title"
                  class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                >
              </div>
              <div class="p-6 h-52">
                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ advantage.title }}</h3>
                <p class="text-gray-600 text-sm">{{ advantage.text }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
