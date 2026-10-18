import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Echo hanya diinisialisasi bila Reverb dikonfigurasi. Jika koneksi gagal,
 * halaman otomatis memakai polling (lihat realtime.js) supaya demo tetap jalan.
 */
export function initEcho() {
    if (!window.Welcoming?.realtime || !import.meta.env.VITE_REVERB_APP_KEY || !window.Welcoming?.userId) {
        return null;
    }
    try {
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
        return window.Echo;
    } catch (e) {
        console.warn('[Welcoming] Realtime tidak tersedia, memakai polling.', e);
        return null;
    }
}

export function echoConnected() {
    return window.Echo?.connector?.pusher?.connection?.state === 'connected';
}
