# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

ChicTukTuk — plateforme de réservation de courses (tuk-tuk). Monorepo de projets
indépendants, chacun avec ses dépendances, son `CLAUDE.md`, son `.gitignore` et son
`Dockerfile`. Lancer les commandes **depuis le dossier du projet concerné**.

| Dossier    | Projet                                                       | Domaine (prod)      | Doc                  |
| ---------- | ------------------------------------------------------------ | ------------------- | -------------------- |
| `backend/` | Laravel 11 + PostgreSQL : API v1 **et** interface Blade historique | api.chictuktuk.com  | `backend/CLAUDE.md`  |
| `landing/` | Nuxt 4 (SSR) : site vitrine + tunnel de réservation public    | chictuktuk.com      | `landing/CLAUDE.md`  |
| `client/`  | Nuxt : espaces client, propriétaire, agent, admin — **à venir** | app.chictuktuk.com  | —                    |

## Migration en cours

Les interfaces Blade de `backend/` sont transposées une à une dans les projets Nuxt, **à
design identique** (même markup Tailwind). Tant qu'une interface n'a pas sa version Nuxt
en production, sa version Blade reste en service et ne doit pas régresser : le site est
en production et utilisé.

Le contrat entre projets est l'API `backend/routes/api/v1` (erreurs `{message, code, errors?}`,
auth Bearer Sanctum). Les fronts ne recalculent jamais un prix : ils affichent celui de l'API.

## Déploiement

Coolify, une ressource par projet et par environnement, alimentée par les branches
`staging` puis `prod` — **toujours valider sur staging avant prod**.

- Build pack Dockerfile, *Base Directory* = dossier du projet (`/backend`, `/landing`),
  *Dockerfile Location* = `/Dockerfile`.
- *Watch Paths* (`backend/**`, `landing/**`) : un commit sur un projet ne redéploie pas les autres.
- CORS : le backend n'autorise que `FRONT_LANDING_URL` et `FRONT_APP_URL` ; le landing
  cible l'API via `NUXT_PUBLIC_API_BASE`.
