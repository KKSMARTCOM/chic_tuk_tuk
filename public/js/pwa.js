// Script d'enregistrement du Service Worker et gestion PWA
console.log("[PWA] Initialisation du PWA");

// Variables globales
let deferredPrompt;
let isAppInstalled = false;

// Vérifier si l'app est déjà installée
window.addEventListener("beforeinstallprompt", (e) => {
    console.log("[PWA] beforeinstallprompt déclenché");
    e.preventDefault();
    deferredPrompt = e;
    showInstallPrompt();
});

window.addEventListener("appinstalled", () => {
    console.log("[PWA] App installée avec succès");
    isAppInstalled = true;
    deferredPrompt = null;
    hideInstallPrompt();
    showSuccessMessage();
});

// Enregistrer le Service Worker
if ("serviceWorker" in navigator) {
    window.addEventListener("load", async () => {
        try {
            const registration = await navigator.serviceWorker.register(
                "/sw.js",
                {
                    scope: "/",
                },
            );
            console.log(
                "[PWA] Service Worker enregistré avec succès:",
                registration,
            );
            handleServiceWorkerRegistration(registration);
        } catch (error) {
            console.error(
                "[PWA] Erreur lors de l'enregistrement du Service Worker:",
                error,
            );
        }
    });
}

// Gestion de l'enregistrement du Service Worker
function handleServiceWorkerRegistration(registration) {
    // Écouter les mises à jour
    registration.addEventListener("updatefound", () => {
        const newWorker = registration.installing;

        newWorker.addEventListener("statechange", () => {
            if (newWorker.state === "activated") {
                showUpdateNotification();
            }
        });
    });

    // Vérifier les mises à jour toutes les heures
    setInterval(
        () => {
            registration.update();
        },
        60 * 60 * 1000,
    );
}

// Afficher la notification d'installation
function showInstallPrompt() {
    const installContainer = document.getElementById("pwa-install-container");
    if (installContainer) {
        installContainer.classList.remove("hidden");
        document
            .getElementById("pwa-install-btn")
            .addEventListener("click", installApp);
        document
            .getElementById("pwa-dismiss-btn")
            .addEventListener("click", dismissInstallPrompt);
    }
}

// Masquer la notification d'installation
function hideInstallPrompt() {
    const installContainer = document.getElementById("pwa-install-container");
    if (installContainer) {
        installContainer.classList.add("hidden");
    }
}

// Rejeter l'installation
function dismissInstallPrompt() {
    hideInstallPrompt();
    deferredPrompt = null;
}

// Installer l'app
async function installApp() {
    if (!deferredPrompt) {
        return;
    }

    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    console.log(`[PWA] Choix utilisateur: ${outcome}`);

    if (outcome === "accepted") {
        console.log("[PWA] App acceptée pour installation");
    } else {
        console.log("[PWA] Installation rejetée");
    }

    deferredPrompt = null;
}

// Montrer le message de succès
function showSuccessMessage() {
    const successMsg = document.createElement("div");
    successMsg.className =
        "fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 animate-pulse z-50";
    successMsg.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>ChicTukTuk installé avec succès!</span>
    `;
    document.body.appendChild(successMsg);

    setTimeout(() => {
        successMsg.remove();
    }, 4000);
}

// Afficher la notification de mise à jour
function showUpdateNotification() {
    const updateContainer = document.createElement("div");
    updateContainer.className =
        "fixed bottom-4 right-4 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3 z-50 max-w-sm";
    updateContainer.innerHTML = `
        <i class="fas fa-cloud-download-alt"></i>
        <div class="flex-1">
            <p class="font-semibold text-sm">Mise à jour disponible</p>
            <p class="text-xs">Une nouvelle version est disponible</p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.body.appendChild(updateContainer);

    setTimeout(() => {
        updateContainer.remove();
    }, 6000);
}

// Demander les permissions de notification
async function requestNotificationPermission() {
    if (!("Notification" in window)) {
        console.log("[PWA] Les notifications ne sont pas supportées");
        return false;
    }

    if (Notification.permission === "granted") {
        console.log("[PWA] Les notifications sont déjà autorisées");
        return true;
    }

    if (Notification.permission !== "denied") {
        try {
            const permission = await Notification.requestPermission();
            if (permission === "granted") {
                console.log("[PWA] Notifications autorisées");
                return true;
            }
        } catch (error) {
            console.error(
                "[PWA] Erreur lors de la demande de notification:",
                error,
            );
        }
    }

    return false;
}

// S'abonner aux notifications push
async function subscribeToPushNotifications() {
    if (!("serviceWorker" in navigator) || !("PushManager" in window)) {
        console.log("[PWA] Les notifications push ne sont pas supportées");
        return false;
    }

    try {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (subscription) {
            console.log("[PWA] Déjà abonné aux notifications push");
            return true;
        }

        // Demander l'autorisation
        const permission = await requestNotificationPermission();
        if (!permission) {
            console.log("[PWA] Autorisation de notification refusée");
            return false;
        }

        // S'abonner (note: nécessite un serveur avec support push)
        console.log("[PWA] Prêt à s'abonner aux notifications push");
        return true;
    } catch (error) {
        console.error(
            "[PWA] Erreur lors de l'abonnement aux notifications:",
            error,
        );
        return false;
    }
}

// Afficher l'état PWA
function logPWAStatus() {
    console.group("[PWA] État");
    console.log("Service Worker supporté:", "serviceWorker" in navigator);
    console.log("Cache API supportée:", "caches" in window);
    console.log("Notifications supportées:", "Notification" in window);
    console.log("Push notifications supportées:", "PushManager" in window);
    console.log("Connexion Online:", navigator.onLine);
    console.log("App installée:", isAppInstalled);
    console.groupEnd();
}

// Initialiser au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
    console.log("[PWA] DOM chargé");
    logPWAStatus();

    // Configurer le bouton de notification si disponible
    const notificationBtn = document.getElementById("enable-notifications-btn");
    if (notificationBtn) {
        notificationBtn.addEventListener("click", subscribeToPushNotifications);
    }
});

// Écouter les changements de connexion
window.addEventListener("online", () => {
    console.log("[PWA] Connexion rétablie");
    showConnectionStatus(true);
});

window.addEventListener("offline", () => {
    console.log("[PWA] Connexion perdue - Mode hors ligne activé");
    showConnectionStatus(false);
});

// Afficher le statut de connexion
function showConnectionStatus(isOnline) {
    const statusContainer = document.getElementById("connection-status");
    if (!statusContainer) return;

    if (isOnline) {
        statusContainer.classList.add("hidden");
    } else {
        statusContainer.innerHTML = `
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 flex items-center space-x-2">
                <i class="fas fa-wifi-slash text-yellow-600"></i>
                <span class="text-sm text-yellow-800">Mode hors ligne - Certaines fonctionnalités sont limitées</span>
            </div>
        `;
        statusContainer.classList.remove("hidden");
    }
}

// Exporter les fonctions pour utilisation
window.PWA = {
    install: installApp,
    dismiss: dismissInstallPrompt,
    requestNotifications: requestNotificationPermission,
    subscribeToPush: subscribeToPushNotifications,
    status: logPWAStatus,
};

console.log("[PWA] Initialisé avec succès");
