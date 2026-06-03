# ✅ PWA Testing Checklist

## 🔐 HTTPS & Sécurité

- [ ] Application accessible en HTTPS
- [ ] Certificat SSL valide
- [ ] Pas d'avertissements de sécurité
- [ ] Service Worker enregistré (DevTools → Application)

## 📦 Fichiers Essentiels

- [x] `public/manifest.json` ✅
- [x] `public/sw.js` ✅
- [x] `public/offline.html` ✅
- [x] `public/js/pwa.js` ✅
- [x] `public/images/pwa-icons/*` (6 fichiers) ✅
- [x] `public/images/pwa-screenshots/*` (2 fichiers) ✅

## 🎯 Installation PWA

### Desktop (Chrome/Edge/Brave)

- [ ] Naviguer vers https://votredomaine.com
- [ ] Attendre 3-5 secondes
- [ ] Voir l'icône d'installation (⬇️) dans la barre d'adresse
- [ ] Cliquer dessus
- [ ] Confirmer "Installer"
- [ ] L'app s'ouvre dans une nouvelle fenêtre
- [ ] L'app apparaît dans le menu des applications

### Mobile Chrome (Android)

- [ ] Naviguer vers https://votredomaine.com
- [ ] Attendre la bannière "Installer l'application"
- [ ] Cliquer sur "Installer"
- [ ] Confirmer
- [ ] L'app s'ajoute à l'écran d'accueil
- [ ] Lancer depuis l'écran d'accueil

### Mobile Safari (iOS)

- [ ] Naviguer vers https://votredomaine.com avec Safari
- [ ] Cliquer sur le bouton **Partager** (carré avec flèche)
- [ ] Scroller et cliquer "Sur l'écran d'accueil"
- [ ] L'app s'ajoute à l'écran d'accueil
- [ ] Lancer depuis l'écran d'accueil

## 🔍 Vérification Manifest & Service Worker

### Dans Chrome DevTools (F12)

1. **Application tab → Manifest**
    - [ ] Manifest chargé
    - [ ] "name" correct
    - [ ] "short_name" visible
    - [ ] "start_url" = "/"
    - [ ] "display" = "standalone"
    - [ ] Icons chargées (192, 384, 512)
    - [ ] Couleurs affichées

2. **Service Workers**
    - [ ] Status: "Running"
    - [ ] Scope: "/"
    - [ ] Pas d'erreurs dans la console

3. **Storage → Cache**
    - [ ] `precache-v1` contient les fichiers
    - [ ] `pages-cache-v1` contient les pages visitées
    - [ ] `assets-cache-v1` contient CSS/JS/Images

## 🚀 Fonctionnalités

### Mode Hors Ligne

- [ ] Ouvrir DevTools → Network → Offline
- [ ] Rafraîchir la page
- [ ] La page se charge (depuis cache)
- [ ] Voir offline.html pour pages non cachées
- [ ] Désactiver Offline
- [ ] La page se recharge normalement

### Performances

- [ ] Temps de chargement < 2 secondes
- [ ] Chrome DevTools → Performance → Lighthouse
- [ ] PWA Audit score ≥ 90
- [ ] Performance score ≥ 90

### Notifications (si implémenté)

- [ ] Cliquer "Autoriser les notifications" (si demandé)
- [ ] Recevoir une notification test
- [ ] Cliquer sur la notification
- [ ] Navigation correcte

## 🎨 Interface Utilisateur

### Installation Banner

- [ ] Visible après 3-5 secondes (desktop)
- [ ] Banneau bleu dégradé
- [ ] Icône de l'app visible
- [ ] Bouton "Installer" fonctionnel
- [ ] Bouton "Plus tard" ferme la banneau
- [ ] Banneau ne réapparaît pas si fermé

### Connexion Status

- [ ] Déconnecter internet (DevTools ou physiquement)
- [ ] Voir la banneau jaune "Hors Ligne"
- [ ] Message "Tentative de reconnexion..."
- [ ] Reconnecter internet
- [ ] La banneau disparaît automatiquement

## 📊 Lighthouse Audit

```bash
# Dans Chrome:
# F12 → Lighthouse
# Cliquer "Analyze page load"
# Category: PWA

Vérifier:
- [ ] Installable: 100
- [ ] PWA Optimized: 100
- [ ] Performance: ≥ 90
- [ ] Accessibility: ≥ 90
- [ ] Best Practices: ≥ 90
- [ ] SEO: ≥ 90
```

## 📱 Test Multi-Dispositifs

### Android

- [ ] Chrome
- [ ] Edge
- [ ] Brave
- [ ] Firefox

### iOS

- [ ] Safari

### Desktop

- [ ] Chrome/Chromium
- [ ] Edge
- [ ] Brave
- [ ] Firefox (support PWA limité)

## 🔧 Console Logs

Ouvrir la console (F12) et vérifier:

```javascript
// Doit afficher:
"[PWA] Service Worker enregistré";
"[PWA] Manifest chargé";
"[PWA] Cache créé: precache-v1";

// Et pour les actions:
"[Service Worker] Installation...";
"[Service Worker] Activation...";
"[Service Worker] Événement fetch:";
```

## 🛡️ Sécurité

- [ ] HTTPS obligatoire (sauf localhost)
- [ ] Certificat SSL valide
- [ ] CSP (Content Security Policy) configurée
- [ ] Pas de mixed content (HTTP + HTTPS)
- [ ] Service Worker servi en HTTPS

## 📊 Vérification des Fichiers

```bash
# S'assurer que ces URLs sont accessibles:
curl -I https://votredomaine.com/manifest.json
# HTTP 200 OK

curl -I https://votredomaine.com/sw.js
# HTTP 200 OK

curl -I https://votredomaine.com/offline.html
# HTTP 200 OK

curl -I https://votredomaine.com/js/pwa.js
# HTTP 200 OK

curl -I https://votredomaine.com/images/pwa-icons/icon-192x192.png
# HTTP 200 OK
```

## 🐛 Debugging

### Service Worker ne s'enregistre pas

1. Vérifier que HTTPS est activé
2. Vérifier que `/sw.js` existe et est accessible
3. Vérifier la console pour les erreurs
4. Vérifier DevTools → Application → Service Workers

### Installation ne s'affiche pas

1. Attendre 30 secondes après la première visite
2. Rafraîchir la page (Ctrl+Shift+R)
3. Vérifier le manifest.json dans DevTools
4. Vérifier que "display" = "standalone"
5. Vérifier que les icônes sont présentes

### Offline ne fonctionne pas

1. Vérifier que Service Worker est "Running"
2. Vérifier que les fichiers sont en cache (Storage → Cache)
3. Vérifier que offline.html existe
4. Nettoyer le cache: `caches.keys().then(names => names.forEach(name => caches.delete(name)))`

### Performance lente

1. Vérifier DevTools → Network
2. Vérifier que les assets sont en cache
3. Vérifier que le réseau n'est pas surchargé
4. Vérifier Cache-Control headers

## ✅ Validation Finale

- [ ] Toutes les URLs accessibles (200 OK)
- [ ] Service Worker enregistré et "Running"
- [ ] Manifest valide et complet
- [ ] Installation possible sur tous les navigateurs
- [ ] Mode offline fonctionnel
- [ ] Lighthouse score ≥ 90
- [ ] Performance score ≥ 90
- [ ] Pas d'erreurs dans la console
- [ ] Pas d'avertissements de sécurité

## 📝 Notes

- **Date de test**: ****\_\_\_****
- **Navigateur**: ****\_\_\_****
- **Appareil**: ****\_\_\_****
- **Issues trouvées**: ****\_\_\_****
- **Status**: ****\_\_\_****

---

**Généré**: 15 mai 2026  
**Version**: 1.0.0  
**Status**: ✅ Ready for Testing
