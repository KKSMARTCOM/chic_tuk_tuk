# 🆘 PWA Troubleshooting Guide

## 🔴 Problèmes Courants

### 1. L'application n'apparaît pas pour l'installation

#### Symptômes

- Pas d'icône d'installation (⬇️) dans la barre d'adresse
- Pas de banneau "Installer l'application"
- Pas d'option "Sur l'écran d'accueil" sur iOS

#### Causes Possibles

```
1. ❌ HTTPS non activé (obligatoire sauf localhost)
2. ❌ manifest.json non accessible
3. ❌ Service Worker non enregistré
4. ❌ Icônes manquantes
5. ❌ Attendre le timing (3-5 secondes minimum)
```

#### Solutions

**A. Vérifier HTTPS**

```bash
# Vérifier le protocole
curl -I https://votredomaine.com
# Doit retourner: HTTP/1.1 200 OK (pas 301 redirect)

# En local avec HTTPS
php artisan serve --ssl --port=8443
# Puis naviguer vers: https://127.0.0.1:8443
```

**B. Vérifier le Manifest**

```bash
# Vérifier l'accessibilité
curl -I https://votredomaine.com/manifest.json
# Doit retourner: HTTP 200

# Vérifier le contenu
curl https://votredomaine.com/manifest.json | jq '.'

# Dans Chrome DevTools:
# F12 → Application → Manifest
# Vérifier:
# ✅ "name" présent
# ✅ "short_name" présent
# ✅ "display": "standalone"
# ✅ Icons chargées (192, 384, 512)
# ✅ Couleurs visibles
```

**C. Vérifier le Service Worker**

```javascript
// Dans la console du navigateur
navigator.serviceWorker.getRegistrations().then((registrations) => {
    console.log("Service Workers enregistrés:", registrations.length);
    registrations.forEach((reg) => {
        console.log("Scope:", reg.scope);
        console.log("Active:", reg.active);
    });
});

// Doit afficher au moins 1 enregistrement avec scope: "/"
```

**D. Vérifier les Icônes**

```bash
# S'assurer que les fichiers existent
ls -la public/images/pwa-icons/

# Doit afficher:
# icon-192x192.png
# icon-192x192-maskable.png
# icon-384x384.png
# icon-384x384-maskable.png
# icon-512x512.png
# icon-512x512-maskable.png

# Vérifier l'accessibilité
curl -I https://votredomaine.com/images/pwa-icons/icon-192x192.png
# Doit retourner: HTTP 200

# Dans DevTools:
# F12 → Application → Manifest
# Vérifier que les icônes se chargent (images visibles)
```

**E. Attendre le Timing**

```
Desktop Chrome/Edge:
  → 1ère visite: Pas d'installation visible
  → 2ème visite (3-5 sec après): Icône d'installation apparaît

Android Chrome:
  → Banneau "Installer l'application" après quelques secondes

iOS Safari:
  → Jamais d'installation automatique (toujours via partage)
```

**F. Forcer un Rechargement**

```bash
# Hard refresh (vider le cache)
# Windows/Linux: Ctrl + Shift + R
# Mac: Cmd + Shift + R

# Ou en JavaScript
caches.keys().then(names => {
  Promise.all(names.map(name => caches.delete(name)))
    .then(() => location.reload());
});

# Dans DevTools:
# Application → Storage → Clear site data
```

---

### 2. Mode Hors Ligne ne Fonctionne Pas

#### Symptômes

- Erreur lors du mode offline
- Page offline ne s'affiche pas
- Cache vide

#### Solutions

**A. Vérifier que le Service Worker est "Running"**

```
DevTools → Application → Service Workers
Vérifier: Status = "Running" (vert)
```

**B. Vérifier le Cache**

```javascript
// Console du navigateur
caches.keys().then((names) => {
    console.log("Caches disponibles:");
    names.forEach((name) => {
        caches.open(name).then((cache) => {
            cache.keys().then((requests) => {
                console.log(`${name}: ${requests.length} fichiers`);
            });
        });
    });
});

// Doit afficher:
// precache-v1: X fichiers
// pages-cache-v1: Y fichiers
// assets-cache-v1: Z fichiers
```

**C. Tester le Mode Offline**

```
DevTools → Application → Service Workers
Cliquer sur "Offline" checkbox
Rafraîchir la page

Doit afficher:
✅ Si déjà visité: Page depuis le cache
❌ Si jamais visité: offline.html
```

**D. Vérifier offline.html**

```bash
# S'assurer que le fichier existe
ls -la public/offline.html

# Vérifier l'accessibilité
curl -I https://votredomaine.com/offline.html
# Doit retourner: HTTP 200
```

**E. Nettoyer le Cache Manuellement**

```javascript
// Console du navigateur
// ⚠️ Cela supprimera TOUT le cache

caches.keys().then((names) => {
    Promise.all(names.map((name) => caches.delete(name))).then(() => {
        console.log("Cache nettoyé");
        location.reload();
    });
});

// Ou via DevTools:
// Application → Storage → Cliquer "Clear site data"
```

---

### 3. Service Worker ne s'enregistre pas

#### Symptômes

- DevTools → Application → Service Workers: Vide ou erreur
- Console: "Failed to register a ServiceWorker: ..."

#### Solutions

**A. Vérifier que /sw.js est Accessible**

```bash
curl -I https://votredomaine.com/sw.js
# Doit retourner: HTTP 200 OK
# Content-Type: application/javascript

# Vérifier le contenu
curl https://votredomaine.com/sw.js | head -10
# Doit commencer par: const CACHE_VERSION = ...
```

**B. Vérifier que HTTPS est Activé**

```bash
# Service Workers ne fonctionnent qu'en HTTPS
# Exception: localhost et 127.0.0.1

# En production, assurer HTTPS:
https://votredomaine.com ✅
http://votredomaine.com ❌

# En local avec PHP:
php artisan serve --ssl --port=8443
# Naviguer vers: https://127.0.0.1:8443
```

**C. Vérifier la Console pour les Erreurs**

```
DevTools → Console
Chercher les messages:
❌ "Failed to register service worker"
❌ "HTTP error status code"
❌ "Parse error"

Cliquer sur le message pour voir les détails
```

**D. Vérifier les Headers**

```bash
# S'assurer que les headers sont corrects
curl -I https://votredomaine.com/sw.js

# Vérifier:
Content-Type: application/javascript ✅
Cache-Control: no-cache ✅
X-Content-Type-Options: nosniff ✅
```

**E. Forcer un Rechargement du Service Worker**

```javascript
// Console du navigateur
navigator.serviceWorker.getRegistrations().then((registrations) => {
    registrations.forEach((reg) => {
        reg.unregister().then(() => {
            console.log("Service Worker désenregistré");
        });
    });
});

// Puis rafraîchir la page (Ctrl+R)
// Le Service Worker se réenregistrera automatiquement
```

---

### 4. Performance Lente / Cache ne Fonctionne Pas

#### Symptômes

- Pages lentes même après la visite
- Cache-Control headers ignorés
- Assets ne se cachent pas

#### Solutions

**A. Vérifier les Cache-Control Headers**

```bash
curl -I https://votredomaine.com/
# Chercher: Cache-Control: public, max-age=3600

curl -I https://votredomaine.com/manifest.json
# Doit avoir: Cache-Control: public, no-cache ou max-age
```

**B. Vérifier la Stratégie de Cache**

```
Dans sw.js:

Assets (CSS, JS, Images): Cache First
  → Cherche d'abord en cache
  → Si pas trouvé → Requête réseau
  → Mise à jour du cache

Pages HTML: Network First
  → Cherche d'abord en réseau
  → Si erreur → Cache

API: Network First
  → Cherche d'abord en réseau
  → Si erreur → Cache
```

**C. Vérifier la Taille du Cache**

```javascript
// Console du navigateur
caches.keys().then((names) => {
    names.forEach(async (name) => {
        const cache = await caches.open(name);
        const keys = await cache.keys();
        let size = 0;
        for (const req of keys) {
            const resp = await cache.match(req);
            size += resp.blob().then((b) => b.size);
        }
        console.log(`${name}: ~${size} bytes`);
    });
});
```

**D. Vérifier les Network Timings**

```
DevTools → Network
Cliquer sur une ressource
Voir:
- Size: "from ServiceWorker" ← Cache utilisé ✅
- Size: "from disk cache" ← Cache utilisé ✅
- Size: "3.2 KB" ← Requête réseau résente
- Time: < 100ms ← Cache utilisé
- Time: > 200ms ← Requête réseau
```

---

### 5. Notification ne Fonctionne Pas

#### Symptômes

- Pas d'affichage de notification
- Erreur "NotificationPermission" denied
- Push notifications pas reçues

#### Solutions

**A. Vérifier la Permission**

```javascript
// Console du navigateur
Notification.permission;
// Doit afficher: "granted", "default", ou "denied"

// Demander la permission
Notification.requestPermission().then((permission) => {
    console.log("Permission:", permission);
});
```

**B. Tester une Notification Simple**

```javascript
// Console du navigateur
new Notification("Test PWA", {
    icon: "/images/pwa-icons/icon-192x192.png",
    body: "Ceci est une notification de test",
});
```

**C. Vérifier le Manifest Notification Settings**

```json
{
  "categories": ["booking", "notification"],
  "screenshots": [...]
}
```

---

### 6. Installation sur iOS ne Fonctionne Pas

#### Symptômes

- Pas d'option "Sur l'écran d'accueil"
- Icône manquante après installation
- App ne démarre pas correctement

#### Solutions

**A. Ajouter les Meta Tags iOS**

```html
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta
    name="apple-mobile-web-app-status-bar-style"
    content="black-translucent"
/>
<meta name="apple-mobile-web-app-title" content="ChicTukTuk" />
<link rel="apple-touch-icon" href="/images/pwa-icons/icon-192x192.png" />
```

**B. Vérifier l'Icône**

```bash
# L'icône doit être 192x192 minimum
ls -la public/images/pwa-icons/icon-192x192.png

# Vérifier la dimension
file public/images/pwa-icons/icon-192x192.png
# Doit afficher: 192 x 192
```

**C. Procédure iOS Correcte**

```
1. Ouvrir Safari (pas Chrome)
2. Naviguer vers https://votredomaine.com
3. Cliquer bouton Partager (carré + flèche)
4. Scroller et chercher "Sur l'écran d'accueil"
5. Donner un nom (laisser par défaut OK)
6. Ajouter
7. L'app apparaît sur l'écran d'accueil
```

---

### 7. Erreurs dans la Console

#### Erreur: "Failed to fetch resource with type: 'image'"

**Cause**: Image non trouvée ou pas en cache

**Solution**:

```bash
# Vérifier que l'image existe
ls -la public/images/pwa-icons/

# Ajouter l'image au cache dans sw.js
const PRECACHE_URLS = [
  '/',
  '/images/pwa-icons/icon-192x192.png',
  // ...
];
```

#### Erreur: "Fetch event fallback"

**Cause**: Ressource non trouvée et pas de fallback

**Solution**:

```javascript
// Dans sw.js, ajouter un fallback
.catch(() => {
  // Retourner une page/image par défaut
  return caches.match('/offline.html');
});
```

#### Erreur: "Mixed Content: The page was loaded over HTTPS, but requested an insecure resource"

**Cause**: Mélange HTTP/HTTPS

**Solution**:

```html
<!-- Dans head.blade.php, s'assurer tout est HTTPS -->
<script src="https://cdn.jsdelivr.net/..."></script>
✅
<script src="http://cdn.example.com/..."></script>
❌
```

---

## 🔧 Debugging Avancé

### 1. Voir tous les Logs PWA

```javascript
// Console du navigateur
// Tous les logs commencent par [PWA] ou [Service Worker]

// Filtrer dans DevTools:
// Console → Chercher: [PWA]
// Affiche uniquement les logs PWA

// Logs clés:
"[PWA] Service Worker enregistré";
"[Service Worker] Installation...";
"[Service Worker] Activation...";
"[Service Worker] Fetch:";
```

### 2. Debugger le Service Worker

```
DevTools → Application → Service Workers
Cliquer sur le Service Worker
Voir les logs en bas de la console
```

### 3. Inspecter les Caches

```javascript
// Console du navigateur
async function inspectCaches() {
    const names = await caches.keys();
    for (const name of names) {
        const cache = await caches.open(name);
        const keys = await cache.keys();
        console.log(`\n${name}:`);
        for (const req of keys) {
            console.log(`  - ${req.url}`);
        }
    }
}
inspectCaches();
```

### 4. Forcer une Mise à Jour du Service Worker

```bash
# Changer la version dans sw.js
const CACHE_VERSION = 'v2'; // Était v1

# Le Service Worker détectera la modification et se mettra à jour automatiquement
```

### 5. Tester les Stratégies de Cache

```javascript
// Console du navigateur
// Tester Cache First
fetch("/css/style.css")
    .then((r) => console.log("Réseau:", r))
    .catch(() => console.log("Cache utilisé"));

// Vérifier DevTools → Network
// Size doit afficher: "from ServiceWorker"
```

---

## 📊 Checklist de Debugging

- [ ] HTTPS activé? (curl -I https://...)
- [ ] manifest.json accessible? (DevTools → Manifest)
- [ ] Service Worker enregistré? (DevTools → Service Workers)
- [ ] Service Worker "Running"? (Status vert)
- [ ] Aucune erreur dans la console? (DevTools → Console)
- [ ] Cache vide? (Storage → Cache)
- [ ] offline.html existe? (curl /offline.html)
- [ ] Icônes présentes? (public/images/pwa-icons/)
- [ ] Permissions correctes? (chmod 644 public/manifest.json)
- [ ] Headers corrects? (curl -I /sw.js)

---

## 📞 Contacter le Support

Si le problème persiste:

1. **Collecter les infos**:
    - URL du site
    - Navigateur (Chrome, Edge, Firefox, Safari)
    - Système (Windows, macOS, iOS, Android)
    - Erreurs de la console (F12 → Console)

2. **Fournir les logs**:

    ```bash
    # Exporter les logs
    DevTools → Console → Clic droit → Save as...

    # Screenshots de DevTools
    # Application → Service Workers
    # Application → Manifest
    # Console → Erreurs
    ```

3. **Vérifier la démo**:
    - https://web.dev/progressive-web-apps/
    - https://whatpwacando.today/

---

**Version**: 1.0.0  
**Dernière mise à jour**: 15 mai 2026  
**Status**: ✅ Production Ready
