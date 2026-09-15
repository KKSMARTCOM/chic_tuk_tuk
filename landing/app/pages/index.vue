<!-- Transposition de backend/resources/views/pages/index.blade.php. -->
<template>
  <div>
    <!-- Hero Section avec Formulaire de Réservation -->
    <section
      id="reservation"
      style="background: url('/assets/images/png/tricycle_bg.png') no-repeat center center/cover"
      class="h-[750px] relative"
    >
      <div class="w-full h-full absolute inset-0 bg-black/80" />
      <div class="w-full md:max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 absolute inset-0 flex items-center">
        <div class="w-full">
          <div class="block md:grid grid-cols-1 md:grid-cols-2 gap-12 items-center w-full">
            <!-- Texte Hero -->
            <div class="text-white hidden md:block">
              <h2 class="text-5xl font-bold mb-6">Voyagez avec Style et Confort</h2>
              <p class="text-xl mb-8 text-purple-100">
                Découvrez une nouvelle façon de vous déplacer avec nos chic tuk tuk. Unique, moderne et confortable.
              </p>
              <div class="flex space-x-6 mb-12">
                <div class="text-center">
                  <div class="text-4xl font-bold">500+</div>
                  <div class="text-purple-200">Courses réalisées</div>
                </div>
                <div class="text-center">
                  <div class="text-4xl font-bold">4.9/5</div>
                  <div class="text-purple-200">Note moyenne</div>
                </div>
                <div class="text-center">
                  <div class="text-4xl font-bold">50+</div>
                  <div class="text-purple-200">Agents</div>
                </div>
              </div>
              <div class="flex justify-start">
                <a
                  href="https://wa.me/2290144429017?text=Bonjour%0AJ'aimerais%20avoir%20plus%20d'informations"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="px-6 py-3 rounded-md bg-[#FFB230] font-semibold text-black flex items-center gap-2"
                >
                  <i class="fa-brands fa-whatsapp font-bold text-lg block" /> Contactez-nous
                </a>
              </div>
            </div>

            <!-- Formulaire de Réservation Multi-étapes -->
            <BookingForm />
          </div>
        </div>
      </div>
    </section>

    <!-- Comment ça marche -->
    <HowItWorksSection />

    <!-- Ce que nous offrons -->
    <AdvantagesSection />

    <!-- Ils nous soutiennent -->
    <PartnersSection />

    <!-- Témoignages -->
    <TestimonialsSection />
  </div>
</template>
