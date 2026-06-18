importScripts(
    "https://www.gstatic.com/firebasejs/12.13.0/firebase-app-compat.js",
);
importScripts(
    "https://www.gstatic.com/firebasejs/12.13.0/firebase-messaging-compat.js",
);

firebase.initializeApp({
    apiKey: "AIzaSyBTZv_e9MowlNpQLI40NmxYVCwD_rnyQ60",
    authDomain: "chictuktuk.firebaseapp.com",
    projectId: "chictuktuk",
    storageBucket: "chictuktuk.firebasestorage.app",
    messagingSenderId: "886551961395",
    appId: "1:886551961395:web:9fc65050d8078c4b2aa846",
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log("[FCM SW] Message en arrière-plan:", payload);

    const title = payload.notification?.title ?? "ChicTukTuk";
    const options = {
        body: payload.notification?.body,
        icon: "/images/pwa-icons/icon-192x192.png",
        badge: "/images/pwa-icons/icon-192x192.png",
        data: payload.data,
    };

    self.registration.showNotification(title, options);
});
