// Firebase Messaging Service Worker
// File ini HARUS ada di root public/ agar FCM Web Push berfungsi

importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyCFHYyTBhCDHxEFy62AjgMDYqzEiwldHj8",
    authDomain: "pro2lms.firebaseapp.com",
    projectId: "pro2lms",
    storageBucket: "pro2lms.firebasestorage.app",
    messagingSenderId: "258632738708",
    appId: "1:258632738708:web:290758d81994b43821d181",
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

const CACHE_NAME = 'lms-pro-cache-v1';
const ASSETS_TO_CACHE = [
    '/',
    '/login',
    '/manifest.json',
    '/icons/logo192x192.png',
    '/icons/logo512x512.png',
];

// Service Worker Lifecycle: Install
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

// Service Worker Lifecycle: Activate (Clean up old caches)
self.addEventListener('activate', (event) => {
    self.clients.claim();
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Fetch events: Network first, fall back to cache
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    // Skip specific URLs to avoid interfering with external APIs like Firebase
    if (event.request.url.includes('googleapis.com') || event.request.url.includes('gstatic.com')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Jika request berhasil via network, langsung kembalikan
                return response;
            })
            .catch(async (error) => {
                // Terjadi error network (misal offline)
                console.log(`[SW] Network fetch gagal untuk: ${event.request.url}. Mencari di cache...`);
                
                const cachedResponse = await caches.match(event.request);
                if (cachedResponse) {
                    console.log(`[SW] Berhasil menemukan di cache: ${event.request.url}`);
                    return cachedResponse;
                }

                // Jika network gagal DAN tidak ada di cache, kita HARUS mengembalikan Response object
                // agar tidak muncul error "Failed to convert value to 'Response'"
                console.warn(`[SW] Network gagal & tidak ada di cache untuk: ${event.request.url}. Mengembalikan fallback.`);
                
                return new Response('Network error occurred and no cache available.', {
                    status: 408,
                    headers: { 'Content-Type': 'text/plain' }
                });
            })
    );
});

// Handler untuk push notification saat browser di background/tertutup
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw] Background message received:', payload);

    // Kita pakai payload.data karena server sekarang mengirim "Data-only" FCM
    const { title, body, id } = payload.data || {};

    const notificationTitle = title || 'Notifikasi Baru';
    const notificationOptions = {
        body: body || 'Anda menerima pesan baru.',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/badge-72x72.png',
        tag: id || 'lms-notif',
        renotify: true,
        data: payload.data
    };

    return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Klik notifikasi → buka URL yang sesuai
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const data = event.notification.data || {};
    let url = '/';

    if (data.type === 'diskusi_kelompok' && data.pertemuan_id && data.kelompok_id) {
        url = `/mahasiswa/classes/${data.kelas_id}/pertemuan/${data.pertemuan_id}/diskusi`;
    } else if (data.type === 'diskusi_dosen') {
        url = data.url || '/';
    } else if (data.type === 'pengumuman') {
        url = '/mahasiswa/classes';
    } else if (data.type === 'diskusi_kelas') {
        url = `/mahasiswa/classes/${data.kelas_id}/pertemuan/${data.pertemuan_id}/diskusi-kelas`;
    }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.navigate(url);
                    return client.focus();
                }
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});
