# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

ChicTukTuk — plateforme de réservation de courses (tuk-tuk) avec gestion de chauffeurs, véhicules,
contrats et propriétaires.

## Commandes

```bash
# Setup
composer install && npm install
cp .env.example .env && php artisan key:generate

# Développement (serveur + queue + logs + vite en parallèle)
composer run dev
# ou séparément :
php artisan serve
npm run dev            # Vite (JS)
npm run watch:css      # Tailwind CSS en watch (build classique, en plus du CDN)

# Build frontend
npm run build           # Vite
npm run build:css       # Tailwind CSS minifié
npm run build:all       # les deux

# Tests (PHPUnit — pas de Pest)
php artisan test
php artisan test --filter=NomDuTest
vendor/bin/phpunit tests/Feature/CheminDuTest.php

# Lint / format PHP (Laravel Pint, config par défaut — pas de pint.json)
vendor/bin/pint
vendor/bin/pint --test   # dry-run

# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Commandes métier (cf. section "Commandes artisan" plus bas)
php artisan app:expire-bookings
php artisan app:process-recurring-bookings
php artisan app:generate-daily
php artisan app:activate-leave-pauses
```

Remarque : `tests/` ne contient actuellement que les tests d'exemple par défaut de Laravel
(`ExampleTest.php` en Unit et Feature) — pas encore de suite de tests métier.

## Stack technique

- Laravel 11 (bootstrap/app.php, pas de Kernel.php)
- PostgreSQL
- Tailwind CSS (via CDN) + Font Awesome 6
- Laravel Sanctum (session web, guard unique `web`)
- Spatie Permission (rôles + permissions, guard `web`)
- Firebase FCM (notifications push)
- PWA (service worker, manifest)
- DataTables + Alpine.js (sidebar accordion)

## Architecture auth

- Un seul modèle `User` avec colonne `profil` : `admin`, `client`, `driver`, `owner`
- Sanctum via cookie httpOnly `ctt_{profil}_token`
- Session web + Sanctum combinés (`Auth::login()` + `createToken()`)
- Middleware `InjectSanctumTokenFromCookie` injecte le token Bearer avant auth
- 3 middlewares distincts (aliasés dans bootstrap/app.php) :
  - `profil:xxx` → `CheckProfil`, vérifie `$user->profil` (utilisé sur tous les groupes de routes : `admin`, `driver`, `client`, `owner`)
  - `permission:xxx` → `CheckPermission`, vérifie `$user->hasAnyPermission()` (Spatie, utilisé route par route pour les actions CRUD)
  - `role:xxx` → `CheckRole`, vérifie `$user->hasAnyRole()` (alias déclaré mais non utilisé actuellement dans les routes)
- Verrou compte : 5 tentatives → locked_until en base (5 min)

## Modèles principaux

### User

- profil : admin | client | driver | owner
- Rôles Spatie : admin, driver, client, proprietaire
- Relations : driver(), vehicles(), vehicleContracts(), fcmTokens(), pushSubscriptions()

### Booking

- Colonnes clés : is_recurring, parent_booking_id, subscription_driver_id,
  trip_type (go/return), round_trip, return_time, week_days,
  remaining_days, next_recurring_date, subscription_end_date,
  is_revoked, revoked_at, expired_at, status (pending/confirmed/in_progress/completed/cancelled/expired/suspended)
- Accessors : is_subscription_parent, is_subscription_child, is_simple_return,
  subscription_label, subscription_index, commission_preview, driver_earning_preview
- Méthode : isVisibleToDriver(driverId)

### Driver

- Relations : driverContracts(), activeDriverContract(), currentVehicle()
- Colonnes : failed_login_attempts, locked_until, last_failed_login (sur User)

### Vehicle

- Relations : owner(), vehicleContracts(), activeVehicleContract(),
  driverContracts(), activeDriverContract(), pauses(), activePause()
- Méthode : isOnPause(), total_pause_days (accessor)

### VehicleContract

- Colonnes : total_amount, monthly_payment, contract_months,
  unlimited_internet, spotify_premium, manager_remuneration,
  status (active/completed/cancelled)
- Accessors : total_paid, remaining_amount, surplus, progress_percentage
- Constantes : App\Consts\VehicleContractConsts::TOTAL_AMOUNTS, MONTHLY_PAYMENTS,
  DEFAULT_UNLIMITED_INTERNET, DEFAULT_SPOTIFY_PREMIUM, DEFAULT_MANAGER_REMUNERATION

### DriverContract

- Relations : driver(), vehicle(), vehicleContract(), leaveRequests(), payments(), vehiclePauses()
- Accessors : months_elapsed, accrued_leave_days, used_leave_days, available_leave_days, total_paid
- Méthode : isActive()

### VehiclePause

- reason_type : agent_leave | agent_change | technical | accident | legal | other
- is_auto : true si créée automatiquement par congé agent

### Payment

- Colonnes : driver_id, vehicle_contract_id, driver_contract_id,
  payment_month, payment_type (contract/manual), net_amount, amount, status
- Paiements journaliers auto via commande `app:generate-daily` (lun-ven uniquement)

### LeaveRequest

- Colonnes : driver_id, driver_contract_id, dates (array), status, rejection_reason

## Logique booking — points critiques

### Courses simples

- Course unique sans AR : status=pending, visible par tous
- Course unique avec AR : course aller créée + course retour cachée (subscription_driver_id=null)
- Quand agent accepte l'aller → subscription_driver_id=A sur la retour → visible par A seulement
- Course retour : is_simple_return = parent_booking_id non null + parentBooking.is_recurring=false

### Abonnements

- Parent : is_recurring=true, whereNull(parent_booking_id)
- Enfants : is_recurring=false, whereNotNull(parent_booking_id), parentBooking.is_recurring=true
- Cron à 1h du matin (lun-dim selon week_days) : crée J+1 depuis le parent
- next_recurring_date = veille du jour J+1 à 1h
- subscription_driver_id propagé depuis le parent sur tous les enfants
- Révocation : is_revoked=true, subscription_driver_id=null sur la course révoquée uniquement
- Suspension : status=suspended (abonnement commencé puis résilié)

### take() — Acceptation

- Vérifie isVisibleToDriver()
- Abonnement parent sans titulaire → subscription_driver_id=A + retour abonnement liée
- Course aller simple AR → subscription_driver_id=A sur la retour simple

### cancel() — 3 cas

1. Enfant abonnement → cancelled + copie liée au titulaire (agent doit révoquer depuis disponibles)
2. Parent abonnement → cancelled + enfants pending annulés + recréation parent (+ retour cachée si AR)
3. Course unique → cancelled + recréation (+ gestion course retour si AR)

### getAvailableBookings(driverId)

- Course unique aller (avec ou sans AR) → tout le monde
- Abonnement parent sans titulaire → tout le monde
- Abonnement parent avec titulaire → titulaire seul
- Enfant abonnement lié → titulaire seul
- Enfant abonnement révoqué → tout le monde
- Retour simple liée → agent seul (qui a accepté l'aller)
- Retour révoquée → tout le monde

### getByDriverId(driverId)

- driver_id=driverId (courses assignées)
- OU retour simple pending liée (subscription_driver_id=driverId, status!=pending exclu des "mes courses")

## Services principaux

Tous dans `app/Services`, injectés dans les contrôleurs (pas de logique métier dans les contrôleurs).

- BookingService : create, createFromAdmin, update (\_partial pour status/driver), take,
  cancel, complete, start, revokeFromSubscription, getAvailableBookings,
  getByDriverId, createRecurringBookings, markExpiredBookings,
  calculateEndDate, getNextAllowedDay
- PricingService : getDistance (OpenRouteService, clé dans config('services.openrouteservice.key')), getPrice
- PaymentService : create, generateDailyContractPayments, generateDailyPaymentForContract
- VehicleService : create, update, toggleStatus, pauseVehicle, endPause, createAutoAgentPause
- VehicleContractService : create, getStats
- DriverContractService : create, end, getStats
- UserService : create, update, updatePassword, toggleStatus, delete, generatePassword
- AuthService : login, logout, checkRateLimit, isAccountLocked, getLockRemainingTime,
  incrementLoginAttempts, resetLoginAttempts
- CommissionService, DriverService, OwnerService, TestimonialService, ZoneService : CRUD/stats dédiés à leur modèle
- FcmNotificationService : envoi de notifications push (kreait/laravel-firebase)

## Contrôleurs & routes

- `app/Http/Controllers/Admin/*` → espace admin (`routes/admin.php`, préfixe `admin.`)
- `app/Http/Controllers/Client/*` → espace client ET espace propriétaire (`routes/client.php`, préfixe `client.`
  et préfixe `owner.` **dans le même fichier** — il n'y a pas de `routes/owner.php` séparé)
- `app/Http/Controllers/Web/*` → auth, dashboard, bookings driver, settings, FCM (partagé entre profils)
- `app/Http/Controllers/Api/PricingController` → endpoint public de calcul de prix
- `routes/web.php` charge admin.php, client.php et driver.php via `require`

Gating par groupe de routes (voir "Architecture auth") :

- `/admin/*` → `auth:sanctum` + `profil:admin`, puis `permission:xxx` par action
- `/driver/*` → `auth:sanctum` + `profil:driver`
- `/client/*` → `auth:sanctum` + `profil:client`
- `/owner/*` (défini dans routes/client.php) → `auth:sanctum` + `profil:owner`
- /fcm/token → POST (enregistrement token FCM)
- /install → page installation PWA
- /admin/owners/{owner}/vehicles → AJAX véhicules d'un propriétaire

## Commandes artisan

- `app:expire-bookings` → marque expired les courses pending +24h dépassées
- `app:process-recurring-bookings` → crée les courses J+1 depuis les abonnements (cron à 1h)
- `app:generate-daily {--date=}` → génère les paiements journaliers (lun-ven uniquement)
- `app:activate-leave-pauses` → active les pauses véhicule automatiques liées aux congés agent

## Scheduler (bootstrap/app.php → withSchedule)

Laravel 11 sans Kernel.php : le scheduler est déclaré directement dans `bootstrap/app.php`, pas dans routes/console.php.

⚠️ Dans le code actuel, les 4 commandes (`app:expire-bookings`, `app:process-recurring-bookings`,
`app:generate-daily`, `app:activate-leave-pauses`) sont enregistrées **sans fréquence explicite**
(pas de `->everyFifteenMinutes()`, `->dailyAt()`, etc. — seulement `->appendOutputTo(storage_path('logs/commands.log'))`),
ce qui les fait tourner à chaque minute par défaut. Si le comportement voulu est "toutes les 15 min"
pour expire-bookings, "1h du matin" pour process-recurring-bookings et "lun-ven 6h" pour generate-daily
(cohérent avec la logique métier décrite plus haut), vérifier si une régression a supprimé ces appels
avant de modifier ce fichier.

## PWA & Firebase

- Service worker : /sw.js
- Firebase messaging SW : /firebase-messaging-sw.js
- Clés VAPID dans meta[name="firebase-vapid-key"]
- Token FCM sauvegardé dans table fcm_tokens
- Notifications envoyées via FcmNotificationService (kreait/laravel-firebase)
- Page d'installation : /install

## Vues importantes

- layouts/app.blade.php → layout admin/driver/owner avec sidebar
- layouts/auth.blade.php → layout login/install
- pages/admin/bookings/index → DataTables, colonne 0 cachée (timestamp tri)
- pages/admin/contracts/index → onglets Contrats agents / Contrats propriétaires
- pages/driver/bookings/available → courses disponibles avec logique visibilité
- pages/driver/bookings/accepting → courses actives avec label dynamique
- pages/owner/\* → espace propriétaire (véhicules, paiements, pauses)

## Points d'attention

- UUID partout (HasUuid trait), keyType=string, incrementing=false
- PostgreSQL : pas de CONCAT pour dates → (pickup_date::date + pickup_time::time)
- Spatie : tous les rôles sous guard 'web', model_id en uuid dans model_has_roles
- DataTables : colonne 0 cachée avec timestamp pour tri, type:'num' dans columnDefs
- Sanctum + session : Auth::login() obligatoire en plus de createToken()
- Paiements : commission/driver_earning calculés à la completion, pas à la création
- Congés : pas de restriction de dépassement, surplus affiché en rouge
