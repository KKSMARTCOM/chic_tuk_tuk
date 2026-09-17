# Feuille de route — après l'auth API v1

Date : 2026-09-16

Ce document dit **où en est la migration** vers les fronts Nuxt, **ce qui bloque quoi**,
et **ce qui reste à décider**. Il ne remplace pas les specs : chaque phase ci-dessous
repart d'un cycle conception → spec → plan, comme l'auth API v1
(`2026-09-16-auth-api-v1-design.md` et `2026-09-16-auth-api-v1.md`).

---

## 1. Acquis

| Brique | État | Vérifié comment |
| --- | --- | --- |
| `landing` sur `staging.chictuktuk.com` | en ligne | devis réel à 42 km, réservation avec widget Turnstile |
| Backend sur `api-staging.chictuktuk.com` | en ligne | `/api/v1/health` à 200, CORS limité aux origines des fronts |
| Espaces Blade sur `app-staging.chictuktuk.com` | en ligne | `/login` à 200 |
| Kill-switch du service worker | servi à la racine du landing | `/sw.js` renvoie bien le script de désinscription |
| Turnstile | actif | POST sans jeton refusé sur `cf_turnstile_token` |
| API v1 publique | en ligne | `pricing/quote`, `public/bookings` |
| Auth API v1 | **commitée, pas encore déployée** | 63 tests, 230 assertions |
| Redirection des anciens chemins Blade depuis le landing | en ligne | `staging.chictuktuk.com/login` en 302 vers `app-staging`, qui répond 200 |

## 2. Phase 0 — finir de livrer l'auth (prérequis de tout le reste)

Rien de ce qui suit n'a de sens avant que l'auth réponde sur staging.

1. **Fusionner la branche courante dans `staging`, puis déployer.** Le déploiement
   exécutera la migration qui recrée `password_reset_tokens` avec `user_id` en clé
   primaire. Sans risque — la table est vide, l'image précédente n'y écrit jamais —
   mais c'est un changement de schéma, pas un ajout de code.
2. **Renseigner un SMTP réellement opérationnel** sur la ressource staging. Le `.env`
   local pointe sur un bac à sable Mailtrap. Sans expéditeur valide, la
   réinitialisation échoue silencieusement : le contrôleur journalise et renvoie quand
   même sa réponse indifférenciée, par nécessité de sécurité.
3. **Vérifier `FRONT_APP_URL`** : elle a désormais un second rôle au-delà du CORS, celui
   de base des liens de réinitialisation.
4. **Éprouver les endpoints contre staging** : connexion par profil, le `409` sur
   `arsogn991@gmail.com` (compte admin **et** propriétaire, cas réel en base), le verrou
   après cinq échecs, `/me` et ses permissions.
5. **Décider du sort d'`APP_DEBUG`.** `/api/v1/health` renvoie `"env":"development"` sur
   staging. Acceptable là, à exclure en production : les traces d'exception seraient
   publiques.

## 3. Phase 1 — le front `client` : squelette et tunnel d'authentification

Premier consommateur réel de ce qui vient d'être écrit, et seul moyen de fermer la
dernière boucle ouverte : **la page `/reset-password` n'existe pas**, donc le parcours
complet depuis l'email n'est pas encore vérifiable de bout en bout.

À faire : créer le dépôt `chic_tuk_tuk_client` avec ses branches `staging` et `prod`,
une ressource Coolify par branche, et le domaine `app-staging.chictuktuk.com` — qui
sert aujourd'hui le Blade et devra donc lui être retiré, exactement comme
`staging.chictuktuk.com` l'a été.

Points déjà tranchés, à ne pas rouvrir :

- **Jeton Bearer géré par le front**, pas de cookie posé par l'API. Conséquence directe :
  les pages authentifiées se rendent côté navigateur (`ssr: false`), puisque le serveur
  Nuxt n'a pas accès au jeton. Le SEO n'y perd rien, ces écrans ne sont pas indexables.
- **Le login ne demande pas le profil.** Il envoie email et mot de passe ; sur `409
  PROFIL_AMBIGUOUS` il affiche le choix parmi les `profils` renvoyés et rappelle
  l'endpoint. Ne pas réintroduire le sélecteur à trois cartes du formulaire Blade.
- **La navigation se construit sur les `permissions`** de `/me` (66 permissions, 5 rôles
  dont `lecteur`, un admin en lecture seule), jamais sur le seul `profil`.
- **`dashboard_path`** est renvoyé par l'API (`/driver/dashboard`, etc.) : le front n'a
  pas à recalculer où envoyer l'utilisateur après connexion.
- **Design identique**, comme pour le landing : transposition du markup Tailwind
  existant, pas une refonte.

Écrans du périmètre : connexion, choix de profil sur ambiguïté, mot de passe oublié,
`/reset-password?token=…`, changement de mot de passe, et une coquille authentifiée
(barre latérale, garde de route, purge du jeton sur `401`).

## 4. Phase 2 — première tranche verticale : l'espace propriétaire

Les espaces Blade restants ne sont pas de taille comparable :

| Espace | Routes | Vues | Utilisateurs réels |
| --- | --- | --- | --- |
| propriétaire | 4 | 3 (sous `pages/client/owner/`) | 9 |
| agent | 14 | 6 | 10 |
| client | 7 | 4 | **0** |
| admin | 57 | 40 | 3 |

Une des quatre routes est orpheline : `owner.vehicles.show` rend
`pages.client.owner.vehicles.show`, qui n'existe pas, et aucun écran n'y renvoie — le
tableau de bord ne lie que `leaves.show` et `payments.show`. À trancher au moment de la
spec : reconstruire la fiche véhicule côté Nuxt, ou supprimer la route.

**Commencer par le propriétaire**, malgré ses 9 utilisateurs : c'est la plus petite
tranche, essentiellement de la lecture (véhicules, paiements, pauses, congés), et elle
suffit à éprouver le motif complet — endpoints API, classes `Data`, écrans Nuxt,
permissions — avant de l'appliquer à des domaines où une erreur coûte cher. Attaquer
l'agent ou l'admin d'emblée, c'est apprendre le motif sur le code le plus risqué.

Rappel de la règle du projet : **le contrat se livre côté backend d'abord**, et de façon
rétrocompatible, puisque le Blade continue d'appeler l'API pendant le déploiement du
front.

## 5. Phase 3 — l'espace agent

Le plus utilisé au quotidien (10 agents, PWA installée, notifications FCM), et celui qui
porte la logique la plus délicate du projet : visibilité des courses, abonnements
parents/enfants, courses retour cachées, révocation. Le `CLAUDE.md` en détaille les
règles — elles devront être transposées **à l'identique** et couvertes par des tests
avant tout écran, parce qu'une erreur y est immédiatement visible par les agents.

Deux sujets propres à cette phase :

- **Les notifications push.** Le jeton FCM est aujourd'hui enregistré par `/fcm/token`
  sur le chemin Blade. Le front Nuxt devra l'enregistrer contre l'API, et la PWA
  installée depuis `app-staging` puis `app.chictuktuk.com` prend le relais de celle
  installée sur le domaine racine.
- **La réinstallation de la PWA.** Les agents ont installé l'application depuis
  `chictuktuk.com`. Quand ce domaine passera au landing, leur application installée
  affichera la vitrine. Il faut une communication et un accompagnement, pas seulement un
  kill-switch technique.

## 6. Phase 4 — l'espace admin

57 routes, 40 vues, 16 contrôleurs, 62 permissions : à découper en sous-lots (courses,
véhicules et contrats, agents et congés, paiements et commissions, utilisateurs et
rôles, contenus). Chaque sous-lot mérite sa propre spec. Ne pas tenter d'un bloc.

## 7. L'espace client : une décision produit, pas technique

**Aucun compte `client` n'existe en base**, et les réservations publiques sont anonymes.
Avant d'écrire une ligne, il faut trancher : veut-on des comptes clients, avec quelle
inscription, et pour quel bénéfice — historique des courses, abonnements en libre-service,
moyens de paiement enregistrés ? Tant que la réponse n'est pas donnée, cet espace n'a pas
d'utilisateur à servir et passe en dernier.

## 8. Chantiers transverses

**Génération des types TypeScript depuis les classes `Data`.** Les types du landing sont
écrits à la main (`app/types/api.ts`), donc toute évolution de contrat doit être
répercutée des deux côtés sans filet. Avec un second front, le risque double. À traiter
avant que le front `client` ne grossisse.

**Bascule des domaines en production.** Le mouvement répété sur staging reste à faire en
prod : le backend prend `api.chictuktuk.com` et `app.chictuktuk.com`, le landing prend
`chictuktuk.com`. Attention, la branche `prod` a onze commits de retard sur `staging` —
tout ce qui concerne l'API v1 n'y est pas encore.

**Duplication de la vitrine.** `api-staging` et `app-staging` servent toujours la page
d'accueil Blade complète. Sans conséquence sur staging ; en production,
`api.chictuktuk.com/` offrirait une copie indexable du landing. À régler par une
redirection de `/` sur le domaine d'API lors de la bascule.

**Liste et révocation des appareils.** Écartée du périmètre de l'auth. Avec le
multi-appareils désormais autorisé, un utilisateur ne peut pas couper l'accès d'un
téléphone perdu sans changer son mot de passe. Endpoints prévus mais non écrits :
`GET /auth/sessions`, `DELETE /auth/sessions/{id}`, `POST /auth/logout-all`.

**Retrait du chemin Blade.** Terminus de la migration. Il permettra de réunifier ce qui
est aujourd'hui volontairement dupliqué : la logique de verrou de compte existe en double
(`AuthService` pour le Blade, `AuthenticateUser` pour l'API), parce que refactorer
`AuthService` imposait de toucher au chemin en service.

## 9. Pièges déjà payés, à ne pas repayer

Consignés ici parce qu'ils ont chacun coûté du temps :

- **Le verrou de `package-lock.json` doit être régénéré sous Node 22**, jamais sous
  Node 20 : la résolution dépend de la version active et `npm ci` échoue au build.
- **Ne jamais lancer `vendor/bin/pint` sans arguments** sur le backend, ni sur un dossier
  entier, ni sur un fichier préexistant que la livraison ne fait que compléter. Le dépôt
  n'a jamais été formaté : 130+ fichiers en écart, et Pint a réécrit 49 lignes de
  `bootstrap/app.php` pour 15 lignes ajoutées.
- **Carbon 3 renvoie des différences signées.** `now()->diffInMinutes($passé)` vaut `-61`.
- **Sanctum écrit `last_used_at` pendant l'authentification**, pas après : tout contrôle
  de fraîcheur doit passer avant `auth:sanctum` et résoudre le jeton lui-même.
- **La liste de priorité des middlewares contient l'interface**
  `Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests`, pas la classe
  `Authenticate`. Viser la classe relègue silencieusement le middleware en fin de liste.
- **Le garde Laravel mémorise l'utilisateur résolu entre deux requêtes d'un même test** :
  `$this->app['auth']->forgetGuards()` est nécessaire pour vérifier qu'un jeton révoqué
  ne passe plus.
- **Un service worker survit au remplacement de l'application** derrière son domaine.
  Tout changement de domaine en exige un de désinscription.

## 10. Erreur de documentation corrigée

Le `CLAUDE.md` annonçait `pages/owner/*` pour les vues propriétaire. Elles vivent en
réalité sous `resources/views/pages/client/owner/`, et le dossier `pages/owner` n'existe
pas. Corrigé, avec mention de la route orpheline relevée ci-dessus.
