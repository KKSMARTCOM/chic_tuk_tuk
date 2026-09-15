/**
 * Thème Tailwind par défaut, sans extension : la page Blade d'origine utilise le CDN
 * Tailwind v3 sans configuration. Toute personnalisation ici ferait diverger le rendu.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
  content: ['./app/**/*.{vue,js,ts}'],
  theme: {
    extend: {},
  },
  plugins: [],
}
