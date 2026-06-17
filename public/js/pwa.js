// public/js/pwa.js

console.log("[PWA] Initialisation");

// ============================================================
// Clés sessionStorage / localStorage
// "dismissed" → sessionStorage : oublié à la fermeture du navigateur
// "installed"  → localStorage  : permanent
// ============================================================
const STORAGE_KEYS = {
    dismissed: "pwa_banner_dismissed", // sessionStorage
    installed: "pwa_installed", // localStorage
};

let deferredPrompt = null;

// ============================================================
// 1. Prompt d'installation natif (Android/Chrome)
// ============================================================
window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    deferredPrompt = e;

    // Ne pas afficher si :
    // - déjà installé (permanent)
    // - déjà refusé dans cette session (sessionStorage)
    const alreadyInstalled = localStorage.getItem(STORAGE_KEYS.installed);
    const dismissedThisSession = sessionStorage.getItem(STORAGE_KEYS.dismissed);

    console.log(alreadyInstalled);
    console.log(dismissedThisSession);

    if (!alreadyInstalled && !dismissedThisSession) {
        setTimeout(showInstallBanner, 3000); // petit délai pour ne pas agresser
    }
});

// Détection installation réussie
window.addEventListener("appinstalled", () => {
    console.log("[PWA] App installée");
    localStorage.setItem(STORAGE_KEYS.installed, "1");
    sessionStorage.removeItem(STORAGE_KEYS.dismissed);
    hideInstallBanner();
    showToast("success", "🎉 ChicTukTuk installé avec succès !");
    deferredPrompt = null;
});

// ============================================================
// 2. Bannière d'installation
// ============================================================
function showInstallBanner() {
    const container = document.getElementById("pwa-install-container");
    if (!container) return;

    container.classList.remove("hidden");

    // Brancher le bouton Installer (une seule fois)
    const btn = document.getElementById("pwa-install-btn");
    btn?.addEventListener("click", triggerInstall, { once: true });
}

function hideInstallBanner() {
    document.getElementById("pwa-install-container")?.classList.add("hidden");
}

async function triggerInstall() {
    if (!deferredPrompt) return;

    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;

    if (outcome === "accepted") {
        // appinstalled event s'occupera du reste
        console.log("[PWA] Installation acceptée");
    } else {
        // Refus → même comportement que "Plus tard"
        dismissBanner();
    }

    deferredPrompt = null;
}

function dismissBanner() {
    // Stocker dans sessionStorage → oublié à la fermeture du navigateur
    sessionStorage.setItem(STORAGE_KEYS.dismissed, "1");
    hideInstallBanner();
    console.log("[PWA] Bannière masquée pour cette session");
}

// ============================================================
// 3. Enregistrement du Service Worker
// ============================================================
function registerSW() {
    if (!("serviceWorker" in navigator)) {
        console.warn("[PWA] Service Worker non supporté");
        return;
    }

    window.addEventListener("load", async () => {
        try {
            const reg = await navigator.serviceWorker.register("/sw.js", {
                scope: "/",
            });
            console.log("[PWA] SW enregistré:", reg.scope);

            // Écouter les mises à jour
            reg.addEventListener("updatefound", () => {
                const newWorker = reg.installing;
                newWorker?.addEventListener("statechange", () => {
                    if (
                        newWorker.state === "installed" &&
                        navigator.serviceWorker.controller
                    ) {
                        showUpdateBanner(reg);
                    }
                });
            });

            // Vérifier une mise à jour toutes les heures
            setInterval(() => reg.update(), 60 * 60 * 1000);
        } catch (err) {
            console.error("[PWA] Erreur SW:", err);
        }
    });

    // Recharger après activation d'un nouveau SW
    let refreshing = false;
    navigator.serviceWorker.addEventListener("controllerchange", () => {
        if (!refreshing) {
            refreshing = true;
            window.location.reload();
        }
    });
}

// ============================================================
// 4. Bannière de mise à jour
// ============================================================
function showUpdateBanner(registration) {
    let banner = document.getElementById("pwa-update-banner");
    if (banner) return; // déjà affichée

    banner = document.createElement("div");
    banner.id = "pwa-update-banner";
    banner.className =
        "fixed bottom-4 left-1/2 -translate-x-1/2 z-50 bg-white border border-blue-300 rounded-xl shadow-xl px-5 py-4 flex items-center gap-4 max-w-sm w-full";
    banner.innerHTML = `
        <i class="fas fa-rotate text-blue-600 text-xl flex-shrink-0"></i>
        <div class="flex-1">
            <p class="text-sm font-semibold text-gray-800">Mise à jour disponible</p>
            <p class="text-xs text-gray-500">Une nouvelle version est prête.</p>
        </div>
        <button id="pwa-update-btn" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex-shrink-0">
            Mettre à jour
        </button>
        <button onclick="document.getElementById('pwa-update-banner').remove()" class="text-gray-400 hover:text-gray-600 text-lg leading-none flex-shrink-0">&times;</button>
    `;
    document.body.appendChild(banner);

    document.getElementById("pwa-update-btn")?.addEventListener("click", () => {
        registration.waiting?.postMessage({ type: "SKIP_WAITING" });
        banner.remove();
    });
}

// ============================================================
// 5. État réseau (online / offline)
// ============================================================
function initNetworkStatus() {
    const container = document.getElementById("connection-status");
    if (!container) return;

    const showOffline = () => {
        container.innerHTML = `
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 flex items-center gap-2 shadow-lg">
                <i class="fas fa-wifi text-yellow-500"></i>
                <span class="text-sm text-yellow-800 font-semibold">Mode hors ligne activé</span>
            </div>`;
        container.classList.remove("hidden");
    };

    const showOnline = () => {
        container.innerHTML = `
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 flex items-center gap-2 shadow-lg">
                <i class="fas fa-wifi text-emerald-500"></i>
                <span class="text-sm text-emerald-800 font-semibold">Connexion rétablie</span>
            </div>`;
        container.classList.remove("hidden");
        setTimeout(() => container.classList.add("hidden"), 3000);
    };

    if (!navigator.onLine) showOffline();
    window.addEventListener("offline", showOffline);
    window.addEventListener("online", showOnline);
}

// ============================================================
// 6. Toast générique (succès/erreur)
// ============================================================
function showToast(type, message) {
    const colors = {
        success: "bg-emerald-600",
        error: "bg-red-600",
        info: "bg-blue-600",
    };
    const el = document.createElement("div");
    el.className = `fixed bottom-4 right-4 ${colors[type] ?? colors.info} text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-2 z-50 text-sm font-semibold`;
    el.innerHTML = `<span>${message}</span>`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 4000);
}

// ============================================================
// 7. iOS — Instruction manuelle (Safari uniquement)
// ============================================================
function initIosPrompt() {
    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    const isStandalone = window.navigator.standalone === true;
    const dismissed = sessionStorage.getItem(STORAGE_KEYS.dismissed);
    const installed = localStorage.getItem(STORAGE_KEYS.installed);

    if (!isIos || !isSafari || isStandalone || dismissed || installed) return;

    setTimeout(() => {
        const tip = document.createElement("div");
        tip.id = "ios-install-tip";
        tip.className =
            "fixed bottom-4 inset-x-4 z-50 bg-white border border-gray-200 rounded-xl shadow-xl p-4 flex items-start gap-3 max-w-sm mx-auto";
        tip.innerHTML = `
            <i class="fas fa-mobile-alt text-blue-600 text-xl flex-shrink-0 mt-0.5"></i>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 mb-1">Installer ChicTukTuk</p>
                <p class="text-xs text-gray-600">
                    Appuyez sur <strong>Partager</strong> <i class="fas fa-arrow-up-from-bracket mx-1"></i>
                    puis <strong>"Sur l'écran d'accueil"</strong>
                </p>
            </div>
            <button id="ios-tip-close" class="text-gray-400 hover:text-gray-600 text-lg leading-none flex-shrink-0">&times;</button>
        `;
        document.body.appendChild(tip);

        document
            .getElementById("ios-tip-close")
            ?.addEventListener("click", () => {
                sessionStorage.setItem(STORAGE_KEYS.dismissed, "1");
                tip.remove();
            });
    }, 4000);
}

// ============================================================
// 8. Init
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
    registerSW();
    initNetworkStatus();
    initIosPrompt();
});

// ============================================================
// API publique (utilisée dans le Blade : onclick="PWA.dismiss()")
// ============================================================
window.PWA = {
    dismiss: dismissBanner,
    install: triggerInstall,
    status() {
        console.group("[PWA] État");
        console.log("SW supporté:", "serviceWorker" in navigator);
        console.log(
            "Installé (localStorage):",
            !!localStorage.getItem(STORAGE_KEYS.installed),
        );
        console.log(
            "Refusé cette session:",
            !!sessionStorage.getItem(STORAGE_KEYS.dismissed),
        );
        console.log("Online:", navigator.onLine);
        console.groupEnd();
    },
};

console.log(
    "[PWA] Prêt — appelez PWA.status() dans la console pour l'état complet",
);
