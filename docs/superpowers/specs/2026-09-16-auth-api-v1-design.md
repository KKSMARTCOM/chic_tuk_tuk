# Auth API v1 — conception

Date : 2026-09-16
Périmètre : authentification de l'API v1 (`api.chictuktuk.com`) pour le front `client`
(`app.chictuktuk.com`), plus la gestion du mot de passe.

## 1. Pourquoi

Le front Nuxt `client` doit remplacer les espaces authentifiés de l'application Blade
(admin, agent, client, propriétaire). Il ne peut afficher quoi que ce soit avant que
l'API sache authentifier un utilisateur et déclarer ses droits. L'API v1 ne comporte
aujourd'hui que trois routes publiques (`health`, `pricing/quote`, `public/bookings`) et
aucune authentification.

Le chemin Blade reste en service pendant toute la transition et **ne doit subir aucun
changement de comportement**. Cette contrainte a dicté plusieurs choix ci-dessous.

## 2. État constaté

Faits vérifiés sur le dépôt et la base, à l'origine des décisions :

- **L'unicité des comptes porte sur `(email, profil)`**, pas sur l'email seul. La
  migration `2026_06_03_120825_drop_email_licence_unique_rule` l'a explicitement voulu
  (« Email unique par profil »). Le cas est réel en base : deux adresses portent chacune
  deux comptes (`admin` + `driver`, `admin` + `owner`).
- **`users.email` est nullable** depuis `2026_02_17_113207_modify_users_table_nullability`.
  Un compte sans email ne peut ni se connecter ni réinitialiser son mot de passe, ce qui
  est cohérent et assumé.
- **`password_reset_tokens` a `email` comme clé primaire**, donc une seule ligne par
  adresse : le broker Laravel par défaut ne peut pas distinguer deux comptes partageant
  un email. Aucune route ne sert les vues `forgot-password.blade.php` et
  `reset-password.blade.php` : la réinitialisation **n'existe pas** aujourd'hui et rien
  n'écrit dans cette table.
- **`AuthService::login()` supprime les jetons dont le nom vaut le profil** à chaque
  connexion Blade (`$user->tokens()->where('name', $user->profil)->delete()`).
- **`config/sanctum.php` a `'expiration' => null`** : les jetons n'expirent jamais. La
  table `personal_access_tokens` possède déjà une colonne `expires_at`, honorée
  nativement par Sanctum par jeton.
- Les jetons Blade existants ont `last_used_at` à `null` : sur une requête stateful,
  Sanctum résout le garde de session avant le jeton, qui n'est donc jamais marqué.
- Spatie compte **5 rôles** (`admin` 62 permissions, `lecteur` 41, `driver` 7,
  `client` 6, `proprietaire` 5) et 66 permissions. Le rôle `lecteur` n'est pas
  documenté dans `CLAUDE.md`.
- Répartition des comptes : 10 agents, 9 propriétaires, 3 admins, **aucun client**.
  L'espace client n'a donc pas d'utilisateur réel à ce jour.

## 3. Décisions

| # | Décision | Motif |
|---|---|---|
| 1 | Le profil est **résolu par le mot de passe**, pas demandé | Le sélecteur actuel produit « Identifiants incorrects » sur un mauvais choix de carte alors que le mot de passe est bon |
| 2 | Jeton **Bearer géré par le front**, pas de cookie posé par l'API | Choix explicite du propriétaire du projet |
| 3 | **Inactivité glissante de 14 jours**, plafond absolu de 90 jours, **multi-appareils** | Un agent quotidien n'est jamais déconnecté, un jeton abandonné meurt seul, et le téléphone ne déconnecte plus le poste |
| 4 | Périmètre : login, logout, me, changement et réinitialisation du mot de passe | Décision du propriétaire du projet, réinitialisation comprise bien qu'elle soit du périmètre neuf |
| 5 | Jetons de réinitialisation **liés au compte** (`user_id`), un lien par compte dans un seul email | Seul moyen cohérent avec l'unicité `(email, profil)` sans redemander son profil à l'utilisateur |

## 4. Découpage

```
app/Domains/Identity/
  Application/Data/
    LoginData.php              email, password, profil?
    UserData.php               sortie de /me et de /login
    ChangePasswordData.php     current_password, password
    ForgotPasswordData.php     email
    ResetPasswordData.php      token, password
  Application/Actions/
    AuthenticateUser.php       résolution du profil, verrou, émission du jeton
    ChangePassword.php         vérifie l'actuel, remplace, révoque les autres jetons
    SendPasswordResetLinks.php un email, un lien par compte
    ResetPassword.php          consomme le jeton, révoque tous les jetons du compte
  Domain/PasswordReset/
    UserKeyedTokenRepository.php
  Presentation/Api/V1/
    AuthController.php         login, logout, me
    PasswordController.php     change, forgot, reset
app/Shared/Http/Middleware/
  EnforceTokenFreshness.php
routes/api/v1/auth.php          à requérir depuis routes/api.php, qui
                               prévoit déjà ce découpage par espace
database/migrations/
  xxxx_rekey_password_reset_tokens_by_user.php
```

`App\Services\AuthService` **n'est pas modifié**. Il renvoie des `RedirectResponse` et
porte la logique de redirection Blade ; le réutiliser imposerait de le refactorer, donc
de toucher au chemin en service. La logique de verrou et de comptage des tentatives est
réimplémentée dans `AuthenticateUser`. C'est une duplication assumée d'une trentaine de
lignes, à réunifier lors du retrait du Blade.

Conventions du projet à respecter : classes `Data` (spatie/laravel-data) pour la
validation **et** la sérialisation, pas de `FormRequest` ni de `JsonResource` ; `BaseData`
comme classe de base pour le mapping snake_case ; enums plutôt que `app/Consts`.

## 5. Contrats

Toutes les routes sont préfixées `/api/v1`. Les erreurs suivent
`App\Shared\Http\ApiExceptionRenderer` : `{message, code, errors?}`.

### POST /auth/login

Entrée : `email` (requis, email), `password` (requis), `profil` (optionnel, une valeur de
l'enum `Profil`).

Algorithme :

1. Throttle par IP (voir § 7). Si dépassé → `429`.
2. Charger tous les utilisateurs portant cet email. Si `profil` est fourni, filtrer
   dessus. Ensemble vide → `422` générique, sans autre traitement.
3. Écarter les comptes verrouillés (`locked_until` dans le futur). **Si tous le sont**
   → `423`, avec le `retry_after` du verrou qui expire le plus tôt. S'il en reste au
   moins un, poursuivre avec ceux-là seulement.
4. Vérifier le mot de passe contre chacun des comptes restants (au plus 4 vérifications
   bcrypt). Conserver ceux qui correspondent.
5. Aucune correspondance → incrémenter le compteur d'échec de **tous** les comptes
   portant cet email, puis `422` générique.
6. Plus d'une correspondance → `409 PROFIL_AMBIGUOUS` avec la liste des profils. Aucun
   jeton n'est émis, aucun compteur n'est incrémenté : le mot de passe était bon.
7. Une seule correspondance, `is_active` faux → `403 ACCOUNT_DISABLED`.
8. Une seule correspondance, active → réinitialiser les compteurs, journaliser, émettre
   le jeton, `200`.

L'étape 3 écarte les comptes verrouillés au lieu de refuser l'ensemble, et l'étape 5
incrémente tous les comptes de l'email. Ces deux règles vont de pair et méritent d'être
justifiées ensemble. Refuser dès qu'**un** compte est verrouillé permettrait de bloquer
un compte en s'acharnant sur un autre — or l'attaquant ne connaît que l'email, jamais le
profil visé. Incrémenter tous les comptes de l'email est en revanche cohérent : l'unicité
`(email, profil)` fait que ces comptes appartiennent à la même personne, et ils se
verrouillent donc ensemble. La conséquence est que le cas « un seul compte verrouillé sur
deux » ne survient qu'après un verrouillage manuel ou un déverrouillage partiel, mais le
code doit le traiter proprement.

```
200 { "token": "12|abc…",
      "user": { "id": "…", "name": "…", "email": "…", "profil": "driver",
                "dashboard_path": "/driver/dashboard",
                "roles": ["driver"], "permissions": ["bookings.view", …] } }

409 { "message": "Plusieurs comptes utilisent ces identifiants. Précisez le profil.",
      "code": "PROFIL_AMBIGUOUS", "profils": ["admin", "owner"] }

423 { "message": "Compte temporairement verrouillé. Réessayez dans 4 minutes.",
      "code": "ACCOUNT_LOCKED", "retry_after": 240 }

403 { "message": "Votre compte a été désactivé. Contactez l'administrateur.",
      "code": "ACCOUNT_DISABLED" }

422 { "message": "Identifiants incorrects.", "code": "VALIDATION_FAILED",
      "errors": { "email": ["Identifiants incorrects."] } }
```

Le message de `422` ne distingue **jamais** un email inconnu d'un mot de passe faux, et
ne révèle pas le nombre de tentatives restantes — contrairement au chemin Blade, qui
l'affiche. Une API anonyme n'a pas les mêmes contraintes qu'un formulaire : le décompte
renseignerait un attaquant sur l'existence du compte.

### POST /auth/logout

Authentifié. Supprime le jeton courant. → `204`.

### GET /auth/me

Authentifié. → `200` avec le même objet `user` que `login`. Les permissions sont celles
effectives de l'utilisateur (`getAllPermissions()`), pas celles de ses rôles seuls.

### POST /auth/password

Authentifié. Entrée : `current_password`, `password`, `password_confirmation`.
`current_password` faux → `422` sur ce champ. Succès : remplace le hash, **révoque tous
les autres jetons du compte** et conserve le courant. → `204`.

### POST /auth/password/forgot

Public, throttlé. Entrée : `email`. Réponse **toujours identique**, que l'adresse existe
ou non :

```
200 { "message": "Si un compte existe pour cette adresse, un email vient d'être envoyé." }
```

Si des comptes existent, un email unique est envoyé, contenant un lien par compte,
étiqueté par `Profil::label()` :

```
Deux comptes utilisent cette adresse.
  → Réinitialiser mon accès Administrateur
  → Réinitialiser mon accès Propriétaire
```

Chaque lien vaut `{FRONT_APP_URL}/reset-password?token=…`. Un jeton par compte,
indépendants, valables 60 minutes.

**Forme du jeton : `{user_id}.{aléa}`.** Les jetons sont hachés en base et la table est
indexée par `user_id` : sans préfixe, retrouver le compte à partir du seul jeton
imposerait de parcourir toutes les lignes en comparant les hachages. Le préfixe rend le
jeton auto-descriptif, et le front n'a rien à en savoir — il transmet une chaîne opaque.
L'uuid ne contient pas de point, la découpe sur le premier point est donc sûre.

### POST /auth/password/reset

Public, throttlé. Entrée : `token`, `password`, `password_confirmation`. Jeton inconnu,
expiré ou déjà consommé → `422` sur `token`. Succès : remplace le hash, supprime le jeton
de réinitialisation et **révoque tous les jetons d'accès du compte** — un mot de passe
réinitialisé signifie un accès possiblement compromis. → `204`.

## 6. Cycle de vie des jetons, et isolation du Blade

Trois mesures, toutes strictement côté API :

**Nom du jeton : `api`.** Pas le profil. `AuthService::login()` supprime les jetons dont
le nom vaut le profil ; si l'API nommait ainsi les siens, un agent se connectant à l'app
Blade tuerait silencieusement sa session sur le front Nuxt. Les abilities restent
`[$user->profil]`, par parité avec l'existant.

**Plafond absolu : `expires_at = now()+90 jours`** posé à la création du jeton, honoré
nativement par Sanctum. `config/sanctum.php` **reste à `'expiration' => null`** : un
réglage global s'appliquerait aussi aux jetons Blade et changerait leur comportement.

**Inactivité glissante : middleware `EnforceTokenFreshness`**, appliqué au seul groupe de
routes API authentifiées. Il lit `last_used_at ?? created_at` du jeton ; au-delà de
14 jours, il supprime le jeton et renvoie `401 TOKEN_EXPIRED`. Le repli sur `created_at`
est nécessaire : les jetons émis par le chemin Blade ont `last_used_at` à `null`.

**Le middleware s'exécute AVANT `auth:sanctum` et résout le jeton lui-même**, via
`PersonalAccessToken::findToken($request->bearerToken())`, au lieu de passer par
`$request->user()->currentAccessToken()`. Le garde de Sanctum écrit `last_used_at` à
`now()` *pendant* qu'il authentifie : lu après lui, ce champ vaut toujours « à
l'instant » et la fenêtre d'inactivité n'expirerait jamais personne. Le coût est une
recherche indexée de plus, la même que celle que le garde fait juste après.

Déclarer le middleware avant `auth:sanctum` sur la route **ne suffit pas** : Laravel trie
la pile selon sa liste de priorité, où l'authentification figure et se retrouve donc
hissée devant tout middleware absent de cette liste. Il faut l'y insérer avec
`$middleware->prependToPriorityList()` dans `bootstrap/app.php`, en visant
`Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests` — **l'interface**, car
c'est elle qui figure dans la liste, et non la classe concrète `Authenticate`. Viser la
classe ne correspond à rien et le middleware est silencieusement relégué en fin de liste,
donc après l'authentification.

Cette insertion modifie une liste **globale**. Elle ne change toutefois l'ordre que des
piles où `EnforceTokenFreshness` est présent, c'est-à-dire les seules routes d'API qui le
déclarent : le chemin Blade n'est pas affecté.

Valeurs à exposer dans un **nouveau `config/identity.php`** plutôt qu'en dur : fenêtre
d'inactivité (14 jours), plafond absolu (90 jours), durée du jeton de réinitialisation
(60 minutes), seuil (5) et durée (5 minutes) du verrou de compte. Un fichier dédié plutôt
que `config/auth.php`, qui est un fichier du framework dont les clés ont un sens imposé.

## 7. Limitation de débit

Le **verrou de compte** est la défense principale : 5 tentatives échouées → 5 minutes,
stocké sur la ligne utilisateur (`failed_login_attempts`, `locked_until`,
`last_failed_login`), comme aujourd'hui.

Le **throttle par IP reste volontairement large** (30 tentatives/minute sur `login`,
10/heure sur `forgot`). Même raisonnement que pour Turnstile sur les réservations
publiques : les opérateurs mobiles béninois partagent une IP entre de nombreux abonnés
(CGNAT), donc une limite serrée par IP punit des utilisateurs innocents sans gêner un
attaquant distribué.

## 8. Migration

`password_reset_tokens` est recréée avec `user_id` (uuid) en clé primaire, `token` et
`created_at`. Aucune donnée à reprendre : la table est inutilisée, faute de routes.

La contrainte de déploiement du projet (`migrate --force` à chaque démarrage de
conteneur, donc compatibilité avec l'image précédente) est satisfaite sans précaution
particulière : l'image actuelle n'écrit jamais dans cette table. Le `down()` restaure la
structure d'origine à clé primaire `email`.

`UserKeyedTokenRepository` implémente
`Illuminate\Auth\Passwords\TokenRepositoryInterface` et est enregistré à la place du
dépôt par défaut. Son interface reçoit l'objet utilisateur, et non seulement une adresse,
donc le broker Laravel reste utilisable sans autre adaptation.

## 9. Prérequis d'exploitation

- **SMTP réellement opérationnel.** Le `.env` local pointe sur un bac à sable Mailtrap ;
  staging et production doivent avoir un expéditeur valide, sans quoi la
  réinitialisation échoue silencieusement.
- **`FRONT_APP_URL` renseignée** dans chaque environnement : elle construit les liens de
  réinitialisation.
- **Limite assumée** : le front `client` n'existant pas, la page `/reset-password` n'a pas
  encore de destination. Les endpoints sont testables un par un ; le parcours complet
  depuis l'email ne le sera qu'après la création de cette page.

## 10. Tests

`tests/` ne contient que les exemples Laravel par défaut. Cette livraison ouvre la suite
de tests métier, écrite **avant** le code :

1. Connexion nominale, un cas par profil, vérifiant `dashboard_path`, `roles` et
   `permissions`.
2. Email portant deux comptes et un même mot de passe → `409` avec les deux profils,
   aucun jeton émis, aucun compteur d'échec incrémenté.
3. Email portant deux comptes aux mots de passe différents → connexion directe, sans
   ambiguïté.
4. Second appel avec `profil` → lève l'ambiguïté et renvoie un jeton.
5. Cinq échecs → `423` avec `retry_after`, et le sixième essai reste refusé même avec le
   bon mot de passe.
6. Email à deux comptes dont **un seul** est verrouillé → connexion réussie sur
   l'autre, sans `423`.
7. Compte `is_active` faux → `403`, avant toute émission de jeton.
8. Mot de passe faux et email inconnu → réponses `422` **strictement identiques**.
9. Jeton dont `last_used_at` a plus de 14 jours → `401 TOKEN_EXPIRED`, et le jeton est
   supprimé en base.
10. Jeton sans `last_used_at` dont `created_at` a plus de 14 jours → même résultat.
11. **Isolation** : un jeton d'API nommé `api` survit à une connexion sur le chemin
    Blade du même utilisateur.
12. `forgot` sur un email à deux comptes → un seul email, deux liens, deux jetons
    distincts en base.
13. `forgot` sur un email inconnu → réponse identique au cas connu, aucun email envoyé.
14. `reset` → mot de passe changé, jeton de réinitialisation supprimé, tous les jetons
    d'accès du compte révoqués.
15. Changement de mot de passe authentifié → les autres jetons sont révoqués, le courant
    survit.

## 11. Hors périmètre

- Inscription autonome d'un client : aucun compte `client` n'existe, et les réservations
  publiques sont anonymes. C'est une question produit, pas technique.
- Renouvellement de jeton (`refresh`) : sans objet avec une fenêtre glissante.
- Liste et révocation des appareils : utile avec le multi-appareils, mais écarté de cette
  livraison.
- Fusion des comptes partageant un email en un utilisateur multi-profils : chantier de
  migration de données distinct.
- Documentation du rôle `lecteur` dans `CLAUDE.md` : à traiter à part, hors de cette
  livraison.
