/**
 * Notifikasi perangkat (Web Push). Service worker di /sw.js menampilkan notifikasi walau Welcoming tidak dibuka.
 * Teks untuk murid selalu netral (diatur server). Saat Keluar Akun, langganan di perangkat ini dicabut.
 */
const supported = () => 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window && !!window.Welcoming?.pushKey;

function urlBase64ToUint8Array(base64) {
    const padding = '='.repeat((4 - (base64.length % 4)) % 4);
    const raw = atob((base64 + padding).replace(/-/g, '+').replace(/_/g, '/'));
    return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
}

async function registration() {
    return navigator.serviceWorker.register('/sw.js', { scope: '/' });
}

async function subscribe() {
    const reg = await registration();
    let sub = await reg.pushManager.getSubscription();
    if (!sub) {
        sub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: urlBase64ToUint8Array(window.Welcoming.pushKey) });
    }
    const json = sub.toJSON();
    const encoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
    await window.axios.post(window.Welcoming.pushSubscribeUrl, { endpoint: json.endpoint, keys: json.keys, contentEncoding: encoding });
    return sub;
}

export const wlPush = {
    /** 'unsupported' | 'default' | 'granted' | 'denied' */
    state() {
        return supported() ? Notification.permission : 'unsupported';
    },
    /** Minta izin (harus dari klik pengguna) lalu daftarkan perangkat ini. */
    async enable() {
        if (!supported()) return 'unsupported';
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            try { await subscribe(); } catch (e) { console.warn('[Welcoming] push gagal didaftarkan', e); }
        }
        return permission;
    },
    /** Daftarkan ulang diam-diam bila izin sudah ada (mis. setelah login di perangkat yang sama). */
    async sync() {
        if (!supported() || Notification.permission !== 'granted' || !window.Welcoming.userId || !window.Welcoming.notificationsEnabled) return;
        try { await subscribe(); } catch (e) { /* abaikan */ }
    },
    /** Endpoint langganan perangkat ini (untuk dicabut saat Keluar Akun). */
    async endpoint() {
        if (!supported()) return null;
        try {
            const reg = await navigator.serviceWorker.getRegistration('/');
            const sub = await reg?.pushManager.getSubscription();
            if (sub) {
                const endpoint = sub.endpoint;
                await sub.unsubscribe();
                return endpoint;
            }
        } catch (e) { /* abaikan */ }
        return null;
    },
    hasActiveSubscription: false,
};

if (supported()) {
    navigator.serviceWorker.getRegistration('/').then((reg) => reg?.pushManager.getSubscription()).then((sub) => { wlPush.hasActiveSubscription = !!sub; }).catch(() => {});
}
