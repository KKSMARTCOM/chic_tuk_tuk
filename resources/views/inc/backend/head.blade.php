<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="ChicTukTuk - Plateforme de réservation de transport avec chauffeurs professionnels">
    <meta name="theme-color" content="#0369a1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ChicTukTuk">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/images/pwa-icons/icon-192x192.png">

    <title>Dashboard Admin - ChicTukTuk</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <!-- DATATABLES CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/datatables.min.css') }}" />

    <!-- PWA Script -->
    <script defer src="/js/pwa.js"></script>

    <script type="module">
        // Import the functions you need from the SDKs you need
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js";
        import {
            getAnalytics
        } from "https://www.gstatic.com/firebasejs/12.13.0/firebase-analytics.js";
        // TODO: Add SDKs for Firebase products that you want to use
        // https://firebase.google.com/docs/web/setup#available-libraries

        // Your web app's Firebase configuration
        // For Firebase JS SDK v7.20.0 and later, measurementId is optional
        const firebaseConfig = {
            apiKey: "AIzaSyBTZv_e9MowlNpQLI40NmxYVCwD_rnyQ60",
            authDomain: "chictuktuk.firebaseapp.com",
            projectId: "chictuktuk",
            storageBucket: "chictuktuk.firebasestorage.app",
            messagingSenderId: "886551961395",
            appId: "1:886551961395:web:9fc65050d8078c4b2aa846",
            measurementId: "G-G4RKJJ2R4P"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const analytics = getAnalytics(app);
    </script>
</head>
