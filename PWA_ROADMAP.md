# 🚀 PWA Phase 3+ Roadmap

## 📋 Vue d'Ensemble

Ce document décrit les améliorations optionnelles pour ChicTukTuk PWA après la v1.0.0 complète.

---

## 🎯 Phase 3: Push Notifications Server-Side (Priorité Haute)

### Objectif

Permettre aux administrateurs d'envoyer des notifications push aux utilisateurs.

### Fichiers à Créer

#### 1. Migration de la Base de Données

```php
// database/migrations/XXXX_XX_XX_create_push_subscriptions_table.php
Schema::create('push_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->text('endpoint');
    $table->text('public_key');
    $table->text('auth_token');
    $table->timestamps();
    $table->unique(['user_id', 'endpoint']);
});

// Stocke les subscriptions Web Push pour chaque utilisateur
```

#### 2. Model PushSubscription

```php
// app/Models/PushSubscription.php
class PushSubscription extends Model
{
    protected $fillable = ['user_id', 'endpoint', 'public_key', 'auth_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

#### 3. Générer VAPID Keys

```php
// app/Console/Commands/GenerateVapidKeys.php
use Minishlink\WebPush\WebPush;

class GenerateVapidKeys extends Command
{
    public function handle()
    {
        $keys = WebPush::generateVapidKeys();

        $this->info('Add to .env:');
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
    }
}

// Exécuter:
// php artisan generate:vapid-keys
```

#### 4. Config PWA

```php
// config/pwa.php
return [
    'vapid' => [
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT', 'mailto:contact@chicktuktuk.com'),
    ],

    'push' => [
        'ttl' => 24 * 3600, // 24 heures
        'urgency' => 'normal', // high, normal, low
        'topic' => 'chicktuktuk-notifications',
    ],
];
```

#### 5. Controller pour les Subscriptions

```php
// app/Http/Controllers/Api/PushSubscriptionController.php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
            'publicKey' => 'required|string',
            'authToken' => 'required|string',
        ]);

        auth()->user()->pushSubscriptions()->updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'public_key' => $validated['publicKey'],
                'auth_token' => $validated['authToken'],
            ]
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        auth()->user()->pushSubscriptions()
            ->where('endpoint', $request->endpoint)
            ->delete();

        return response()->json(['success' => true]);
    }
}
```

#### 6. Service de Push Notifications

```php
// app/Services/PushNotificationService.php
namespace App\Services;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('pwa.vapid.subject'),
                'publicKey' => config('pwa.vapid.public_key'),
                'privateKey' => config('pwa.vapid.private_key'),
            ]
        ]);
    }

    public function send($user, $title, $body, $options = [])
    {
        $payload = [
            'title' => $title,
            'body' => $body,
            'icon' => '/images/pwa-icons/icon-192x192.png',
            'badge' => '/images/pwa-icons/icon-192x192.png',
            'tag' => $options['tag'] ?? 'notification',
            'requireInteraction' => $options['requireInteraction'] ?? false,
        ];

        foreach ($user->pushSubscriptions as $subscription) {
            $webPushSubscription = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->public_key,
                'authToken' => $subscription->auth_token,
            ]);

            $this->webPush->send(
                json_encode($payload),
                $webPushSubscription
            );
        }

        $this->webPush->flush();
    }

    public function sendToAll($title, $body, $options = [])
    {
        User::chunk(100, function ($users) use ($title, $body, $options) {
            foreach ($users as $user) {
                $this->send($user, $title, $body, $options);
            }
        });
    }
}
```

#### 7. Routes API

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store']);
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy']);
});
```

#### 8. Mise à Jour pwa.js

```javascript
// public/js/pwa.js - Ajouter à la fin

async function subscribeToPushNotifications() {
  try {
    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(
        '{{ config('pwa.vapid.public_key') }}'
      ),
    });

    // Sauvegarder la subscription
    await fetch('/api/push/subscribe', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        endpoint: subscription.endpoint,
        publicKey: subscription.getKey('p256dh'),
        authToken: subscription.getKey('auth'),
      }),
    });

    console.log('[PWA] Push notifications activated');
  } catch (error) {
    console.error('[PWA] Push notification error:', error);
  }
}

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

window.PWA.subscribeToPush = subscribeToPushNotifications;
```

### Utilisation

```php
// Envoyer une notification à un utilisateur
$service = app(PushNotificationService::class);
$service->send(auth()->user(), 'Nouvelle réservation', 'Vous avez une nouvelle réservation!');

// Envoyer à tous les utilisateurs
$service->sendToAll('Maintenance', 'L\'app sera en maintenance demain');
```

---

## 🎯 Phase 4: Admin Panel Notifications

### Objectif

Interface admin pour gérer les notifications push.

### Fichiers à Créer

#### 1. Model Notification

```php
// app/Models/Notification.php
class Notification extends Model
{
    protected $fillable = [
        'title', 'body', 'target_type', 'target_id',
        'scheduled_at', 'sent_at', 'created_by'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    // target_type: 'all', 'role', 'user'
}
```

#### 2. Admin Controller

```php
// app/Http/Controllers/Admin/NotificationController.php
class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::latest()->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'target_type' => 'required|in:all,role,user',
            'target_id' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
        ]);

        $notification = Notification::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        if ($request->scheduled_at) {
            SendPushNotificationJob::dispatch($notification)
                ->delay($request->scheduled_at);
        } else {
            SendPushNotificationJob::dispatch($notification);
        }

        return redirect()->route('admin.notifications')
            ->with('success', 'Notification envoyée');
    }
}
```

#### 3. Admin Views

```blade
{{-- resources/views/admin/notifications/create.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Nouvelle Notification</h1>

    <form method="POST" action="{{ route('admin.notifications.store') }}" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">Titre</label>
            <input type="text" name="title" required class="w-full border px-3 py-2">
            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Message</label>
            <textarea name="body" required rows="4" class="w-full border px-3 py-2"></textarea>
            @error('body') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Destinataires</label>
            <select name="target_type" required class="w-full border px-3 py-2">
                <option value="all">Tous les utilisateurs</option>
                <option value="role">Par rôle</option>
                <option value="user">Utilisateur spécifique</option>
            </select>
        </div>

        <div id="target-input" style="display: none;">
            <input type="text" name="target_id" placeholder="Rôle ou ID utilisateur"
                   class="w-full border px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Planifier (optionnel)</label>
            <input type="datetime-local" name="scheduled_at" class="w-full border px-3 py-2">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
            Envoyer Notification
        </button>
    </form>
</div>

<script>
document.querySelector('select[name="target_type"]').addEventListener('change', (e) => {
    document.getElementById('target-input').style.display =
        e.target.value === 'all' ? 'none' : 'block';
});
</script>
@endsection
```

---

## 🎯 Phase 5: Background Sync

### Objectif

Synchroniser les données hors ligne (formulaires, actions) quand la connexion revient.

### Service Worker Update

```javascript
// public/sw.js - Ajouter:

self.addEventListener("sync", (event) => {
    if (event.tag === "sync-forms") {
        event.waitUntil(syncForms());
    }
});

async function syncForms() {
    const db = await openIndexedDB();
    const forms = await db.getAllFromObjectStore("pending-forms");

    for (const form of forms) {
        try {
            const response = await fetch(form.action, {
                method: form.method,
                headers: form.headers,
                body: form.body,
            });

            if (response.ok) {
                await db.deleteFromObjectStore("pending-forms", form.id);
            }
        } catch (error) {
            console.log("[Service Worker] Form sync failed, retry later");
        }
    }
}
```

---

## 🎯 Phase 6: Advanced Performance

### 1. Code Splitting

```javascript
// vite.config.js
export default {
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ["lodash", "date-fns"],
                    maps: ["leaflet"],
                },
            },
        },
    },
};
```

### 2. Image Optimization

```bash
# Utiliser ImageMagick ou Sharp
npm install sharp

# Optimiser les screenshots
node scripts/optimize-images.js
```

### 3. Bundle Analysis

```bash
npm install webpack-bundle-analyzer
npm run build:analyze
```

---

## 🎯 Phase 7: Analytics & Monitoring

### Service de Monitoring

```php
// app/Services/PWAAnalyticsService.php
class PWAAnalyticsService
{
    public function recordInstall(User $user)
    {
        PWAInstall::create([
            'user_id' => $user->id,
            'installed_at' => now(),
            'user_agent' => request()->userAgent(),
            'device_type' => $this->getDeviceType(),
        ]);
    }

    public function recordOfflineUsage(User $user)
    {
        PWAUsage::create([
            'user_id' => $user->id,
            'mode' => 'offline',
            'timestamp' => now(),
        ]);
    }
}
```

### Dashboard

```blade
{{-- resources/views/admin/pwa-analytics.blade.php --}}
<div class="grid grid-cols-4 gap-6 mb-6">
    <div class="bg-white p-6 rounded shadow">
        <h3 class="text-gray-500 text-sm">Total Installations</h3>
        <p class="text-3xl font-bold">{{ $totalInstalls }}</p>
    </div>
    <!-- Autres cards... -->
</div>

<div class="bg-white p-6 rounded shadow">
    <h3 class="text-xl font-bold mb-4">Installations par Jour</h3>
    <!-- Chart -->
</div>
```

---

## 📊 Timeline Recommandée

```
Phase 1 (COMPLETE ✅):
- Profile & Settings Pages
- PWA Core Setup
- Offline Support

Phase 2 (NEXT - 1-2 semaines):
- Push Notifications Server
- VAPID Keys
- Subscription Management

Phase 3 (Suivant - 1 semaine):
- Admin Panel
- Notification Scheduling
- UI for Management

Phase 4 (Optional - 2 semaines):
- Background Sync
- Advanced Caching
- Performance Optimization

Phase 5 (Optional - 1 semaine):
- Analytics
- Monitoring
- Dashboard

Total: 4-6 semaines pour tout
```

---

## 💡 Tips & Best Practices

### Pour les Notifications Push

- ✅ Garder le message court (< 100 caractères)
- ✅ Tester sur plusieurs appareils
- ✅ Vérifier les permissions
- ✅ Monitorer l'engagement
- ❌ Ne pas surcharger d'notifications

### Pour le Background Sync

- ✅ Stocker les formulaires en cache
- ✅ Retenter en cas d'erreur
- ✅ Notifier l'utilisateur du succès
- ✅ Limiter le nombre d'essais
- ❌ Ne pas supprimer les données immédiatement

### Pour la Performance

- ✅ Minimiser la taille du Service Worker
- ✅ Utiliser les images optimisées
- ✅ Code splitting approprié
- ✅ Monitoring continu
- ❌ Charger trop de ressources

---

## 🔗 Ressources Utiles

### Web Push

- [Web.dev - Push Notifications](https://web.dev/notifications/)
- [Minishlink WebPush Library](https://github.com/Minishlink/web-push-php)
- [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging)

### Background Sync

- [MDN - Background Sync](https://developer.mozilla.org/en-US/docs/Web/API/Background_Sync_API)
- [Service Worker Cookbook](https://serviceworke.rs/)

### Analytics

- [Google Analytics PWA](https://support.google.com/analytics/answer/9333790)
- [Sentry for Error Tracking](https://sentry.io/)

---

## ✅ Checklist d'Implémentation

### Phase 3 (Push Notifications)

- [ ] Migration créée
- [ ] VAPID keys générées
- [ ] Model PushSubscription créé
- [ ] Service créée
- [ ] Controller API créé
- [ ] Routes créées
- [ ] pwa.js mis à jour
- [ ] Tests écrits
- [ ] Déployé en staging

### Phase 4 (Admin Panel)

- [ ] Model Notification créé
- [ ] Admin Controller créé
- [ ] Admin Views créées
- [ ] Routes créées
- [ ] Job créé pour envoi
- [ ] Tests écrits
- [ ] UI/UX testée

---

## 📞 Support & Questions

Pour des questions sur les phases suivantes:

1. Consulter la documentation existante
2. Vérifier les ressources externes
3. Tester en local d'abord
4. Vérifier les logs d'erreur

---

**Version**: 1.0.0 Roadmap  
**Created**: 15 mai 2026  
**Status**: 📋 Planned Features  
**Next Phase**: 🔔 Push Notifications Server (Phase 3)
