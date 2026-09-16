// Service Worker pour ChicTukTuk PWA
// Gère le cache, les mises à jour et le mode hors ligne

const CACHE_NAME = "chicttuktuk-v1";
const RUNTIME_CACHE = "chicttuktuk-runtime";
const ASSETS_CACHE = "chicttuktuk-assets";
const IMAGES_CACHE = "chicttuktuk-images";

// Liste des fichiers à mettre en cache au démarrage
const PRECACHE_URLS = ["/", "/index.php", "/offline.html"];

// Event: Installation du Service Worker
self.addEventListener("install", (event) => {
    console.log("[Service Worker] Installation en cours...");

    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log("[Service Worker] Précaching des fichiers");
            return cache.addAll(PRECACHE_URLS).catch((err) => {
                console.warn(
                    "[Service Worker] Erreur lors du précaching:",
                    err,
                );
                // Continuer même si certains fichiers ne peuvent pas être mis en cache
                return cache.add("/");
            });
        }),
    );

    // Forcer l'activation immédiate
    self.skipWaiting();
});

// Event: Activation du Service Worker
self.addEventListener("activate", (event) => {
    console.log("[Service Worker] Activation en cours...");

    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    // Supprimer les anciens caches
                    if (
                        cacheName !== CACHE_NAME &&
                        cacheName !== RUNTIME_CACHE &&
                        cacheName !== ASSETS_CACHE &&
                        cacheName !== IMAGES_CACHE
                    ) {
                        console.log(
                            "[Service Worker] Suppression du cache:",
                            cacheName,
                        );
                        return caches.delete(cacheName);
                    }
                }),
            );
        }),
    );

    // Prendre le contrôle de tous les clients
    self.clients.claim();
});

// Event: Interception des requêtes (Fetch)
self.addEventListener("fetch", (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignorer les requêtes non-HTTP
    if (!url.protocol.startsWith("http")) {
        return;
    }

    // Stratégie pour les assets (CSS, JS, fonts)
    if (isAsset(url.pathname)) {
        event.respondWith(cacheFirstStrategy(request, ASSETS_CACHE));
        return;
    }

    // Stratégie pour les images
    if (isImage(url.pathname)) {
        event.respondWith(cacheFirstStrategy(request, IMAGES_CACHE));
        return;
    }

    // Stratégie pour les requêtes API (network first)
    if (url.pathname.startsWith("/api/")) {
        event.respondWith(networkFirstStrategy(request));
        return;
    }

    // Stratégie par défaut pour les pages HTML (network first)
    if (request.mode === "navigate") {
        event.respondWith(networkFirstStrategy(request, "/offline.html"));
        return;
    }

    // Fallback: stale-while-revalidate
    event.respondWith(staleWhileRevalidateStrategy(request));
});

// Stratégie: Cache First
async function cacheFirstStrategy(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);

        // Ne mettre en cache que les réponses réussies
        if (response && response.status === 200) {
            const clonedResponse = response.clone();
            cache.put(request, clonedResponse);
        }

        return response;
    } catch (error) {
        console.warn("[Service Worker] Erreur fetch:", error);
        return (
            caches.match("/offline.html") ||
            new Response("Contenu non disponible", { status: 503 })
        );
    }
}

// Stratégie: Network First
async function networkFirstStrategy(request, fallbackUrl = null) {
    try {
        const response = await fetch(request);

        if (response && response.status === 200) {
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        console.warn("[Service Worker] Erreur réseau:", error);

        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }

        if (fallbackUrl) {
            return (
                caches.match(fallbackUrl) ||
                new Response("Mode hors ligne", { status: 503 })
            );
        }

        return new Response("Impossible de charger", { status: 503 });
    }
}

// Stratégie: Stale While Revalidate
async function staleWhileRevalidateStrategy(request) {
    const cache = await caches.open(RUNTIME_CACHE);
    const cached = await cache.match(request);

    const fetchPromise = fetch(request)
        .then((response) => {
            if (response && response.status === 200) {
                cache.put(request, response.clone());
            }
            return response;
        })
        .catch((error) => {
            console.warn("[Service Worker] Erreur revalidation:", error);
            return cached || new Response("Non disponible", { status: 503 });
        });

    return cached || fetchPromise;
}

// Utilitaires
function isAsset(pathname) {
    return /\.(js|css|woff2?|ttf|eot|svg)(\?|$)/i.test(pathname);
}

function isImage(pathname) {
    return /\.(png|jpg|jpeg|gif|webp|svg)(\?|$)/i.test(pathname);
}

// Background Sync pour les actions hors ligne
self.addEventListener("sync", (event) => {
    if (event.tag === "sync-notifications") {
        event.waitUntil(syncNotifications());
    }
});

async function syncNotifications() {
    try {
        const response = await fetch("/notifications/unread-count");
        const data = await response.json();

        if (data.count > 0) {
            // Notifier l'utilisateur
            const notification = new Notification("Nouvelles notifications", {
                body: `Vous avez ${data.count} nouvelle(s) notification(s)`,
                icon: "/images/pwa-icons/icon-192x192.png",
                badge: "/images/pwa-icons/icon-192x192.png",
                tag: "notification-sync",
                requireInteraction: false,
            });
        }
    } catch (error) {
        console.warn("[Service Worker] Erreur sync:", error);
    }
}

// Push Notifications
self.addEventListener("push", (event) => {
    console.log("[Service Worker] Notification push reçue");

    let notificationData = {
        title: "ChicTukTuk",
        body: "Nouvelle notification",
        icon: "/images/pwa-icons/icon-192x192.png",
        badge: "/images/pwa-icons/icon-192x192.png",
        tag: "default-notification",
    };

    if (event.data) {
        try {
            notificationData = event.data.json();
        } catch (e) {
            notificationData.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(notificationData.title, {
            body: notificationData.body,
            icon: notificationData.icon,
            badge: notificationData.badge,
            tag: notificationData.tag,
            data: notificationData.data,
            requireInteraction: true,
        }),
    );
});

// Gestion des clics sur les notifications
self.addEventListener("notificationclick", (event) => {
    console.log("[Service Worker] Notification cliquée");
    event.notification.close();

    const urlToOpen = event.notification.data?.url || "/";

    event.waitUntil(
        clients
            .matchAll({ type: "window", includeUncontrolled: true })
            .then((clientList) => {
                // Chercher un client avec l'URL demandée
                for (let i = 0; i < clientList.length; i++) {
                    const client = clientList[i];
                    if (client.url === urlToOpen && "focus" in client) {
                        return client.focus();
                    }
                }
                // Ouvrir une nouvelle fenêtre
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            }),
    );
});

// Message Handler pour la communication avec le client
self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting();
    }
});

console.log("[Service Worker] Chargé avec succès");
