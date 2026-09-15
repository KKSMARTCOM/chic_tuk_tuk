// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2026-09-15',
  devtools: { enabled: false },

  // SSR conservé : le landing est la vitrine publique, son contenu doit être indexable.
  ssr: true,

  css: [
    // Même version que le CDN utilisé par la page Blade d'origine (6.4.0).
    '@fortawesome/fontawesome-free/css/all.min.css',
    '~/assets/css/main.css',
  ],

  // Tailwind v3 et non v4 : la page d'origine utilise le CDN v3. La v4 change des
  // valeurs par défaut (ombres, arrondis, couleur de bordure, ring) et altérerait
  // le rendu, alors que l'objectif est un design strictement identique.
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },

  app: {
    head: {
      htmlAttrs: { lang: 'fr' },
      title: 'ChicTukTuk - Réservation de Tricycles Chic',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1.0' },
        {
          name: 'description',
          content: 'Réservez votre tricycle ChicTukTuk en ligne : trajets simples, aller-retour et abonnements, avec des agents formés et ponctuels.',
        },
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
        },
      ],
    },
  },

  runtimeConfig: {
    public: {
      // Surchargés à l'exécution, sans rebuild, par NUXT_PUBLIC_API_BASE et NUXT_PUBLIC_APP_URL.
      apiBase: 'http://localhost:8000/api/v1',
      // Application authentifiée (app.chictuktuk.com) : cible du bouton « Connexion ».
      appUrl: 'http://localhost:8000',
    },
  },
})
