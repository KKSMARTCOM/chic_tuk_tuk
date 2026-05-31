# 📱 PWA - Progressive Web App Setup

## Vue d'ensemble

ChicTukTuk est maintenant une **Progressive Web App (PWA)** entièrement fonctionnelle. Cela signifie que les utilisateurs peuvent:

- 📲 **Installer l'app** directement depuis le navigateur
- 🚀 **Lancer l'app** comme une application native
- 📶 **Fonctionner hors ligne** grâce au Service Worker
- 🔔 **Recevoir des notifications** push
- ⚡ **Accès rapide** et **performance optimisée**

---

## 🚀 Pour les Utilisateurs

### Installation sur Mobile (Android/iOS)

#### Android:

1. Ouvrez https://votredomaine.com dans **Chrome**
2. Appuyez sur le menu (⋮) → "Installer l'application" ou cliquez sur la bannière d'installation
3. ChicTukTuk s'installera sur votre écran d'accueil

#### iOS (iPad/iPhone):

1. Ouvrez https://votredomaine.com dans **Safari**
2. Appuyez sur le bouton **Partager** (carré avec flèche)
3. Sélectionnez "Sur l'écran d'accueil"
4. Confirmez

### Installation sur Desktop

- **Chrome/Edge**: Cliquez sur l'icône d'installation (⬇️) dans la barre d'adresse
- **Firefox**: Consultez les paramètres pour les PWA

### Fonctionnalités Disponibles

✅ **Mode Hors Ligne**: Accédez aux pages précédemment chargées sans connexion  
✅ **Notifications**: Recevez des mises à jour en temps réel  
✅ **Raccourcis**: Accès rapide au profil, réservations, notifications  
✅ **Performance**: Chargement instantané grâce au cache

---

## 🔧 Pour les Développeurs

### Architecture PWA

```
public/
├── manifest.json              # Manifeste PWA
├── sw.js                      # Service Worker
├── offline.html               # Page hors ligne
├── js/
│   └── pwa.js                 # Enregistrement et gestion PWA
└── images/
    ├── pwa-icons/             # Icônes (192x192, 384x384, 512x512)
    └── pwa-screenshots/       # Screenshots (540x720, 1280x720)

resources/views/
├── inc/backend/
│   ├── head.blade.php         # Meta tags PWA
│   ├── pwa-components.blade.php # UI components
│   └── ...
└── layouts/
    └── app.blade.php          # Layout principal

generate-pwa-icons.php          # Script pour générer les icônes
```

### Fichiers Clés

#### 1. **manifest.json**

Définit les métadonnées de l'application:

- Nom et description
- Icônes et screenshots
- Couleurs de thème
- Shortcuts
- Catégories

#### 2. **sw.js** - Service Worker

Gère:

- **Caching** (précache, cache-first, network-first)
- **Offline support** avec fallback pages
- **Background sync** pour synchroniser les données
- **Push notifications** handling
- **Update checking** automatique

Stratégies de cache:

- **Cache First**: Assets (CSS, JS, images)
- **Network First**: Pages HTML et données
- **Stale While Revalidate**: Contenu dynamique

#### 3. **pwa.js** - Enregistrement et Gestion

Gère:

- Enregistrement du Service Worker
- Prompt d'installation
- Notifications push
- État de connexion
- Updates

#### 4. **pwa-components.blade.php**

Composants UI Blade:

- Bannière d'installation
- Statut de connexion
- Notifications

#### 5. **offline.html**

Page affichée en mode hors ligne avec:

- Message clair
- Options de retry
- Conseils d'aide

### Métadonnées PWA (head.blade.php)

```html
<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, viewport-fit=cover"
/>
<meta name="theme-color" content="#0369a1" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<link rel="manifest" href="/manifest.json" />
<link rel="apple-touch-icon" href="/images/pwa-icons/icon-192x192.png" />
<script defer src="/js/pwa.js"></script>
```

---

## 📦 Fichiers Créés/Modifiés

### Fichiers Créés:

- ✅ `public/manifest.json`
- ✅ `public/sw.js`
- ✅ `public/offline.html`
- ✅ `public/js/pwa.js`
- ✅ `resources/views/inc/backend/pwa-components.blade.php`
- ✅ `generate-pwa-icons.php`
- ✅ `public/images/pwa-icons/*` (6 fichiers)
- ✅ `public/images/pwa-screenshots/*` (2 fichiers)

### Fichiers Modifiés:

- ✅ `resources/views/inc/backend/head.blade.php` (meta tags PWA)
- ✅ `resources/views/layouts/app.blade.php` (ajout composants PWA)

---

## 🛠️ Configuration et Déploiement

### Prérequis

1. **HTTPS obligatoire** (sauf en localhost)

    ```bash
    # Vérifier
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
        || $_SERVER['SERVER_PORT'] == 443) {
        // OK - HTTPS activé
    }
    ```

2. **Service Workers supportés dans**:
    - Chrome 40+
    - Firefox 44+
    - Edge 17+
    - Safari 11.1+
    - Opera 27+

3. **Manifest.json accessible**:
    ```bash
    curl https://votredomaine.com/manifest.json
    ```

### Déploiement

1. **Icônes et images**: Les icônes sont générées automatiquement

    ```bash
    php generate-pwa-icons.php
    ```

2. **Service Worker**: Automatiquement enregistré par `pwa.js`

3. **Test local**:

    ```bash
    # Avec HTTPS local
    php artisan serve --port=8443 --ssl
    ```

4. **En production**:
    - Assurer HTTPS activé ✅
    - Vérifier que `/manifest.json` est accessible ✅
    - Vérifier que `/sw.js` est accessible ✅
    - Tester sur les navigateurs cibles ✅

---

## 🔍 Test et Validation

### Checklist de Validation PWA

```bash
# 1. Manifest
curl https://votredomaine.com/manifest.json

# 2. Service Worker
curl https://votredomaine.com/sw.js

# 3. Icônes
ls public/images/pwa-icons/
ls public/images/pwa-screenshots/

# 4. Page offline
curl https://votredomaine.com/offline.html

# 5. HTTPS
echo $SERVER['HTTPS']

# 6. Scripts
curl https://votredomaine.com/js/pwa.js
```

### Chrome DevTools

1. Ouvrir DevTools (F12)
2. Aller à **Application** → **Manifest**
    - Vérifier le manifest.json
    - Vérifier les icônes
3. Aller à **Service Workers**
    - Vérifier l'enregistrement
    - Tester "Offline"
4. Aller à **Storage** → **Cache**
    - Voir les ressources en cache

### Lighthouse PWA Audit

Dans Chrome:

1. DevTools → **Lighthouse**
2. Sélectionner **PWA**
3. Analyser
4. Objectif: Score ≥ 90

---

## 🎯 Stratégies de Cache

### Cache First (Assets)

```
CSS, JS, Fonts, Images
↓
Recherche d'abord en cache
Si pas trouvé → Requête réseau
Mise à jour du cache
```

### Network First (Pages)

```
Pages HTML, API
↓
Recherche d'abord en réseau
Si erreur → Cache
Si pas de cache → Offline page
```

### Stale While Revalidate

```
Contenu dynamique
↓
Retourner le cache immédiatement
En arrière-plan: Revalider et mettre à jour
```

---

## 🔔 Notifications Push (Optionnel)

Pour activer les notifications push server-side:

```php
// Dans votre contrôleur
use Illuminate\Support\Facades\Http;

public function sendPushNotification($user, $title, $body)
{
    // Récupérer la subscription de l'utilisateur
    $subscription = $user->push_subscription;

    if ($subscription) {
        Http::withHeaders([
            'Authorization' => 'vapid t=' . config('pwa.vapid.public'),
        ])->post($subscription->endpoint, [
            'title' => $title,
            'body' => $body,
            'icon' => '/images/pwa-icons/icon-192x192.png',
        ]);
    }
}
```

---

## 🚨 Troubleshooting

### L'app n'apparaît pas pour l'installation?

**Checklist**:

- ✅ Site en HTTPS (obligatoire sauf localhost)
- ✅ manifest.json valide
- ✅ Service Worker enregistré
- ✅ Icônes présentes (min 192x192)
- ✅ display: "standalone" dans manifest
- ✅ Attendre 30 secondes après la visite

**Solution**:

```bash
# Vérifier le manifest
curl -i https://votredomaine.com/manifest.json

# Vérifier le Service Worker
curl -i https://votredomaine.com/sw.js

# Vérifier dans DevTools
# F12 → Application → Manifest → Vérifier les icônes
```

### Certaines pages se chargent pas en mode offline?

**Cause**: Les pages ne sont pas précachées automatiquement

**Solution**:

1. Les pages se cachent après la première visite
2. Ajouter des URLs au `PRECACHE_URLS` dans `sw.js`

```javascript
const PRECACHE_URLS = ["/", "/profile", "/settings", "/offline.html"];
```

### Le cache n'a pas la version à jour?

**Solution - Hard Refresh**:

- Windows/Linux: Ctrl + Shift + R
- Mac: Cmd + Shift + R

Ou nettoyer manuellement:

```javascript
// Dans la console du navigateur
caches.keys().then((names) => {
    names.forEach((name) => caches.delete(name));
});
```

### Mode hors ligne ne fonctionne pas?

**Checklist**:

- ✅ Service Worker enregistré (DevTools → Application)
- ✅ offline.html existe
- ✅ Cache n'est pas vide

**Debug**:

```javascript
// Console
navigator.serviceWorker.getRegistrations().then((regs) => {
    regs.forEach((reg) => console.log(reg));
});

// Voir les erreurs
caches.keys().then(console.log);
```

---

## 📊 Monitoring

### Vérifier l'adoption PWA

```php
// Enregistrer les installations
public function recordPWAInstall()
{
    \DB::table('pwa_installs')->insert([
        'user_id' => auth()->id(),
        'installed_at' => now(),
        'user_agent' => request()->userAgent(),
    ]);
}
```

### Logs Service Worker

Tous les logs commencent par `[PWA]` ou `[Service Worker]`:

```javascript
// Console du navigateur
// Voir tous les PWA logs
console.log("%c[PWA]", "color: blue; font-weight: bold;");
```

---

## 🎨 Customisation

### Changer les Couleurs

Dans `manifest.json`:

```json
{
    "theme_color": "#0369a1",
    "background_color": "#ffffff"
}
```

Dans `head.blade.php`:

```html
<meta name="theme-color" content="#0369a1" />
```

### Changer le Nom de l'App

```json
{
    "name": "ChicTukTuk - Réservation",
    "short_name": "ChicTukTuk"
}
```

### Ajouter des Shortcuts

```json
{
    "shortcuts": [
        {
            "name": "Nouvelle Réservation",
            "url": "/bookings",
            "icons": [{ "src": "..." }]
        }
    ]
}
```

---

## 📚 Ressources

- [MDN Web Docs - PWA](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
- [Google Developers - PWA](https://developers.google.com/web/progressive-web-apps)
- [Web.dev - PWA Checklist](https://web.dev/pwa-checklist/)
- [Manifest Spec](https://www.w3.org/TR/appmanifest/)

---

## ✅ Statut PWA

```
✅ Manifest.json         - Complet
✅ Service Worker       - Fonctionnel
✅ Offline Support      - Actif
✅ Installation Prompt  - Actif
✅ Icônes              - Générées
✅ HTTPS               - Requis
✅ Responsive Design   - Oui
✅ Screenshots         - Présents
✅ Notifications       - Prêtes
```

**Audit Lighthouse PWA**: Viser un score ≥ 90

---

**Dernière mise à jour**: 15 mai 2026  
**Version PWA**: 1.0.0  
**Status**: ✅ Production Ready
