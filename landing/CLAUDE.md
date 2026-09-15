# CLAUDE.md — landing

Vitrine publique de ChicTukTuk et tunnel de réservation, servie sur `chictuktuk.com`.
Projet Nuxt autonome du monorepo : il ne partage aucun code avec `client/` (app.chictuktuk.com)
et ne parle au backend Laravel que par son API (`api.chictuktuk.com`).

## Commandes

```bash
nvm use            # Node 22 (.nvmrc) — Nuxt 4.5 refuse Node 20, en fin de vie depuis avril 2026
npm install
npm run dev        # http://localhost:3000
npm run build      # build SSR → .output/
node .output/server/index.mjs
```

Variables d'environnement (lues à l'exécution, sans rebuild) : voir `.env.example`.
En local, le backend doit autoriser l'origine du landing : `FRONT_LANDING_URL=http://localhost:3000`
dans le `.env` Laravel.

## Règle principale : design identique

Ce projet est une **transposition** de `resources/views/pages/index.blade.php` et de ses
composants Blade, pas une refonte. Le markup et les classes Tailwind sont repris tels quels.
Chaque composant indique en tête le fichier Blade dont il provient.

- **Tailwind v3, volontairement** (`tailwindcss ~3.4`) : la page d'origine utilise le CDN v3.
  La v4 change des valeurs par défaut (ombres, arrondis, bordures, ring) et altérerait le rendu.
  Thème par défaut, sans extension.
- Font Awesome **6.4.0**, même version que le CDN d'origine.
- Police Poppins (Google Fonts), comme l'original.
- Le carrousel « Ce que nous offrons » utilise embla à la place de slick/jQuery, avec la même
  configuration (5 s, boucle, pause au survol, 3/2/1 cartes à 992/520 px).

## API

- `GET  /public/pricing/quote` — devis détaillé. **Aucun prix n'est calculé dans le front** :
  le récapitulatif affiche le devis de l'API. L'original recalculait la majoration horaire en
  JavaScript et avait divergé du serveur.
- `POST /public/bookings` — création de la réservation (201).

Types dans `app/types/api.ts`, écrits à la main d'après les classes Data du backend en attendant
leur génération automatique : toute modification d'un contrat doit être répercutée des deux côtés.

## Écarts assumés avec la page Blade

Corrections de comportement, sans effet sur le design :

- l'étape 1 exige de choisir une ville dans les suggestions (l'API refuse sinon) ;
- modifier le texte d'un lieu invalide ses coordonnées (l'original gardait celles de l'ancienne ville) ;
- l'heure de retour doit suivre l'heure de départ dans tous les cas, comme l'exige l'API ;
- le téléphone suit la règle de l'API (10 à 20 caractères, espaces acceptés — l'original refusait
  le format de son propre placeholder « 01 90 12 34 56 ») ;
- le bouton « Confirmer » est désactivé pendant l'envoi (l'original permettait une double réservation) ;
- le texte de majoration horaire vient de l'API au lieu d'être codé en dur.
