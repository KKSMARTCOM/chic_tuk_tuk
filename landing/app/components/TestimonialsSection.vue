<!-- Transposition de backend/resources/views/pages/components/testimonials.blade.php. -->
<script setup lang="ts">
/*
 * Témoignages statiques, comme dans la page d'origine (la boucle sur les témoignages
 * en base y est commentée). À brancher sur une API le jour où ils seront gérés en base.
 */
const testimonials = [
  { name: 'Armand Kouassi', rating: 5, quote: 'Excellent service ! Le chauffeur était très poli et ponctuel. J\'ai vraiment apprécié la propreté du tricycle. Je recommande vivement !' },
  { name: 'Marie Dossou', rating: 5, quote: 'Plateforme super facile à utiliser ! J\'ai réservé en moins de 2 minutes. Les prix sont vraiment compétitifs comparé à la concurrence.' },
  { name: 'Pierre Ogueni', rating: 4.5, quote: 'Transport écologique et confortable. Je suis impressionné par l\'engagement environnemental de cette entreprise. Bravo !' },
  { name: 'Yuki Tanaka', rating: 5, quote: 'En visite touristique, le circuit proposé était fantastique ! Guide très accueillant et informé. Une expérience inoubliable !' },
  { name: 'Sophie Adèle', rating: 5, quote: 'Client régulier depuis 3 mois. Service fiable et consistent. L\'application est intuitive et le support client très réactif.' },
  { name: 'Ibrahim Sow', rating: 5, quote: 'J\'utilise ce service pour mes déplacements quotidiens. Sûr, économique et écologique. Meilleur choix pour se déplacer en ville !' },
]

const avatarUrl = (name: string) => `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}`
</script>

<template>
  <section id="temoignages" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl font-bold text-gray-800 mb-4">Ce que nos clients disent</h2>
        <p class="text-xl text-gray-600">Des milliers de clients satisfaits</p>
      </div>

      <div class="grid md:grid-cols-3 gap-8">
        <div
          v-for="testimonial in testimonials"
          :key="testimonial.name"
          class="bg-gray-50 rounded-xl p-8 shadow-md hover:shadow-xl transition"
        >
          <div class="flex items-center mb-4">
            <img :src="avatarUrl(testimonial.name)" :alt="testimonial.name" class="w-16 h-16 rounded-full mr-4">
            <div>
              <h4 class="font-bold text-gray-800">{{ testimonial.name }}</h4>
              <div class="flex text-yellow-400">
                <i v-for="n in Math.floor(testimonial.rating)" :key="n" class="fas fa-star" />
                <i v-if="testimonial.rating % 1" class="fas fa-star-half-alt" />
              </div>
            </div>
          </div>
          <p class="text-gray-600 italic">"{{ testimonial.quote }}"</p>
        </div>
      </div>
    </div>
  </section>
</template>
