/**
 * Berlangganan channel private. `poll` SELALU dipanggil tiap `interval` ms (chat 4 dtk, inbox/daftar 10 dtk)
 * dan segera saat tab kembali aktif, realtime (Echo) hanya mempercepat, tidak menjadi satu-satunya jalur,
 * sehingga halaman tetap ter-update walau Reverb mati atau event broadcast gagal.
 */
export function subscribe(channel, handlers, poll, interval = 5000) {
    if (window.Echo) {
        try {
            const ch = window.Echo.private(channel);
            Object.entries(handlers).forEach(([event, fn]) => ch.listen('.' + event, fn));
        } catch (e) {
            console.warn('[Welcoming] gagal subscribe', channel, e);
        }
    }
    if (!poll) return;
    let busy = false;
    const tick = async () => {
        if (busy || document.hidden) return;
        busy = true;
        try { await poll(); } catch (e) { /* abaikan, coba lagi nanti */ } finally { busy = false; }
    };
    document.addEventListener('visibilitychange', () => { if (!document.hidden) tick(); });
    return setInterval(tick, interval);
}
