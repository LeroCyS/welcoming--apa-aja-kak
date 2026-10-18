/* Service worker Welcoming: hanya untuk notifikasi perangkat (Web Push). Tidak menyimpan halaman di cache. */
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));

self.addEventListener('push', (event) => {
    let data = {};
    try { data = event.data ? event.data.json() : {}; } catch (e) { data = { title: 'Welcoming', body: event.data?.text() }; }

    event.waitUntil((async () => {
        // Bila Welcoming sedang dibuka dan terlihat, aplikasi sudah menampilkan toast + suara sendiri.
        const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        const visible = windows.some((w) => w.visibilityState === 'visible' && w.focused);
        windows.forEach((w) => w.postMessage({ type: 'wl-push', data }));
        if (visible) return;

        await self.registration.showNotification(data.title || 'Welcoming', {
            body: data.body || '',
            icon: data.icon,
            badge: data.badge,
            tag: data.tag,
            renotify: !!data.renotify,
            requireInteraction: !!data.requireInteraction,
            data: data.data || {},
        });
    })());
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/';
    event.waitUntil((async () => {
        const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        const same = windows.find((w) => new URL(w.url).origin === self.location.origin);
        if (same) {
            await same.focus();
            return same.navigate(url).catch(() => null);
        }
        return self.clients.openWindow(url);
    })());
});
