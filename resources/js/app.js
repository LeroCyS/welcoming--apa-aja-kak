import axios from 'axios';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import { initEcho } from './echo';
import { subscribe } from './realtime';
import { wlPush } from './push';
import { shrinkImage, shrinkInput } from './image-resize';
import { wlConfirm, wlPrompt, bindConfirmForms } from './dialog';
import { initSelects } from './select';

const token = document.querySelector('meta[name="csrf-token"]')?.content;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
axios.defaults.headers.common['Accept'] = 'application/json';
// Halaman terkunci (PIN idle / Keluar Cepat): permintaan latar belakang membawa ke layar kunci, sekali saja,
// dan tidak pernah dari layar kunci itu sendiri (kalau tidak, halaman akan reload terus-menerus).
let redirectingToLock = false;
axios.interceptors.response.use(undefined, (error) => {
    const res = error.response;
    if (res?.status === 423 && res.data?.redirect && !redirectingToLock && !window.Welcoming?.locked
        && new URL(res.data.redirect, location.origin).pathname !== location.pathname) {
        redirectingToLock = true;
        window.location.replace(res.data.redirect);
    }
    return Promise.reject(error);
});
window.axios = axios;
window.wlSubscribe = subscribe;
window.wlPush = wlPush;
window.wlConfirm = wlConfirm;
window.wlPrompt = wlPrompt;
bindConfirmForms();
document.addEventListener('DOMContentLoaded', () => initSelects());
window.wlShrinkImage = shrinkImage;
// Semua <input type="file" data-shrink> (sampul artikel, foto guru, logo) otomatis diperkecil sebelum dikirim.
document.addEventListener('change', (e) => {
    if (e.target.matches?.('input[type="file"][data-shrink]')) shrinkInput(e.target);
});

initEcho();
if (!window.Welcoming?.locked) wlPush.sync();

// ---------- Suara notifikasi (dibuat dengan Web Audio, tanpa file) ----------
const sound = {
    get muted() { try { return localStorage.getItem('wl-sound') === 'off'; } catch (e) { return false; } },
    set muted(v) { try { localStorage.setItem('wl-sound', v ? 'off' : 'on'); } catch (e) { /* abaikan */ } },
    ctx: null,
    play(critical = false) {
        if (this.muted) return;
        try {
            this.ctx ||= new (window.AudioContext || window.webkitAudioContext)();
            const notes = critical ? [880, 660, 880] : [660, 880];
            notes.forEach((freq, i) => {
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                const at = this.ctx.currentTime + i * 0.14;
                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, at);
                gain.gain.exponentialRampToValueAtTime(0.18, at + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, at + 0.22);
                osc.connect(gain).connect(this.ctx.destination);
                osc.start(at);
                osc.stop(at + 0.24);
            });
        } catch (e) { /* audio diblokir sampai ada interaksi */ }
    },
};
window.wlSound = sound;

// ---------- Keluar Cepat & Keluar Akun ----------
function clearLocalTraces() {
    try {
        Object.keys(localStorage).filter((k) => k.startsWith('wl-draft')).forEach((k) => localStorage.removeItem(k));
        sessionStorage.clear();
    } catch (e) { /* storage bisa diblokir */ }
}
/**
 * Keluar Cepat: layar langsung diganti situs netral (tidak bisa di-Back) dan akun dikunci di server.
 * sessionStorage dikosongkan sehingga membuka Welcoming lagi selalu meminta PIN/password.
 */
function quickExit() {
    clearLocalTraces();
    document.documentElement.style.visibility = 'hidden';
    const fd = new FormData();
    fd.append('_token', token || '');
    let sent = false;
    try { sent = navigator.sendBeacon(window.Welcoming.quickExitBeacon, fd); } catch (e) { /* fallback di bawah */ }
    if (!sent) {
        try { fetch(window.Welcoming.quickExitBeacon, { method: 'POST', body: fd, keepalive: true, credentials: 'same-origin' }); } catch (e) { /* tetap lanjut */ }
    }
    window.location.replace(window.Welcoming.quickExitUrl || 'https://www.google.com');
}
// Keluar Akun biasa: konfirmasi, hapus draf, dan cabut notifikasi perangkat ini sebelum logout.
document.addEventListener('submit', async (e) => {
    const form = e.target.closest('[data-logout]');
    if (!form || form.dataset.confirmed) return;
    e.preventDefault();
    const ok = await wlConfirm({ title: 'Keluar dari akun?', text: 'Kamu perlu masuk lagi untuk membuka cerita di perangkat ini.', okLabel: 'Ya, keluar', tone: 'danger' });
    if (!ok) return;
    clearLocalTraces();
    const endpoint = await Promise.race([wlPush.endpoint(), new Promise((r) => setTimeout(() => r(null), 1500))]);
    if (endpoint) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'push_endpoint';
        input.value = endpoint;
        form.appendChild(input);
    }
    form.dataset.confirmed = '1';
    form.submit();
});
// Jika browser memulihkan halaman dari cache (Back), muat ulang agar server memeriksa sesi/kunci lagi.
window.addEventListener('pageshow', (e) => {
    if (e.persisted && window.Welcoming?.userId) window.location.replace(location.href);
});
window.quickExit = quickExit;
document.addEventListener('click', (e) => {
    if (e.target.closest('[data-quick-exit]')) {
        e.preventDefault();
        quickExit();
    }
});
if (document.querySelector('[data-quick-exit-shortcut]')) {
    let lastEsc = 0;
    document.addEventListener('keydown', (e) => {
        // Ctrl + Q (laptop/PC): langsung Keluar Cepat.
        if (e.ctrlKey && !e.altKey && !e.shiftKey && e.key.toLowerCase() === 'q') {
            e.preventDefault();
            quickExit();
            return;
        }
        if (e.key !== 'Escape') return;
        const now = Date.now();
        if (now - lastEsc < 1000) quickExit();
        lastEsc = now;
    });
}

// ---------- Komponen Alpine global ----------
document.addEventListener('alpine:init', () => {
    // Toast di pojok layar untuk notifikasi baru saat aplikasi terbuka.
    Alpine.store('toasts', {
        items: [],
        push(t) {
            const id = Math.random().toString(36).slice(2);
            this.items.push({ id, ...t });
            if (this.items.length > 3) this.items.shift();
            setTimeout(() => this.dismiss(id), t.critical ? 12000 : 6000);
        },
        dismiss(id) { this.items = this.items.filter((t) => t.id !== id); },
    });

    // Status online Guru BK untuk murid: langsung berubah lewat realtime (channel sekolah), dicek ulang tiap 10 dtk.
    Alpine.store('presence', {
        map: {},
        is(id, fallback) { return this.map[id] ?? fallback; },
        async load() {
            try {
                const { data } = await axios.get(window.Welcoming.counselorStatusUrl);
                this.map = Object.fromEntries(Object.entries(data).map(([id, s]) => [id, s.online]));
            } catch (e) { /* coba lagi nanti */ }
        },
        init() {
            if (!window.Welcoming?.counselorStatusUrl) return;
            this.load();
            if (window.Welcoming.schoolId) {
                subscribe('school.' + window.Welcoming.schoolId, {
                    PresenceChanged: (e) => { this.map = { ...this.map, [e.counselor_id]: !!e.online }; },
                });
            }
            setInterval(() => { if (!document.hidden) this.load(); }, 10000);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) this.load(); });
        },
    });

    // Daftar cerita murid (halaman Cerita & kolom kiri chat) ikut diperbarui otomatis.
    Alpine.data('storyList', (url) => ({
        init() {
            window.wlSubscribe('student.' + window.Welcoming.userId, {
                'Illuminate\\Notifications\\Events\\BroadcastNotificationCreated': () => this.refresh(),
            }, () => this.refresh(), 10000);
        },
        async refresh() {
            const { data } = await axios.get(url);
            this.$el.innerHTML = data.html;
        },
    }));

    /**
     * Lonceng notifikasi: badge, dropdown detail (ikon, jenis, potongan isi), toast + suara untuk notifikasi baru,
     * dan notifikasi browser bila tab sedang di belakang dan perangkat belum berlangganan Web Push.
     */
    Alpine.data('notificationBell', (feedUrl, readAllUrl) => ({
        open: false, items: [], unread: 0, seen: null, muted: sound.muted, pushState: wlPush.state(),
        async init() {
            await this.load(true);
            if (window.Welcoming.userId) {
                const prefix = window.Welcoming.role === 'counselor' ? 'counselor.' : 'student.';
                subscribe(prefix + window.Welcoming.userId, {
                    'Illuminate\\Notifications\\Events\\BroadcastNotificationCreated': () => this.load(),
                }, () => this.load(), 10000);
            }
            navigator.serviceWorker?.addEventListener('message', (e) => { if (e.data?.type === 'wl-push') this.load(); });
        },
        async load(first = false) {
            const { data } = await axios.get(feedUrl);
            const fresh = first || !this.seen ? [] : data.items.filter((i) => !i.read && !this.seen.has(i.id));
            this.items = data.items;
            this.unread = data.unread;
            this.seen = new Set(data.items.map((i) => i.id));
            this.announce(fresh);
        },
        announce(fresh) {
            // Notifikasi untuk percakapan yang sedang dibuka tidak perlu diumumkan lagi.
            const here = location.origin + location.pathname;
            const list = fresh.filter((i) => i.target !== here);
            if (!list.length || !window.Welcoming.notificationsEnabled) return;
            sound.play(list.some((i) => i.critical));
            list.slice(0, 3).forEach((i) => Alpine.store('toasts').push(i));
            if (document.hidden && !wlPush.hasActiveSubscription && wlPush.state() === 'granted') {
                list.slice(0, 3).forEach((i) => {
                    const neutral = window.Welcoming.role === 'student';
                    const n = new Notification(neutral ? 'Welcoming' : i.title, { body: neutral ? 'Ada kabar baru untukmu.' : (i.body || i.label), icon: '/images/welcoming-mark.png', tag: i.id });
                    n.onclick = () => { window.focus(); location.href = i.url; };
                });
            }
        },
        toggle() { this.open = !this.open; if (this.open) this.load(); },
        toggleSound() { this.muted = !this.muted; sound.muted = this.muted; if (!this.muted) sound.play(); },
        async enablePush() { this.pushState = await wlPush.enable(); },
        async readAll() { await axios.post(readAllUrl); this.unread = 0; this.items = this.items.map((i) => ({ ...i, read: true })); },
    }));

    /**
     * Status Online guru. Heartbeat berjalan di SEMUA halaman guru (termasuk editor artikel), bukan hanya
     * di halaman yang punya tombol Online. `enabled` = pilihan guru, `online` = status yang dilihat murid.
     * Bila heartbeat gagal, tombol jujur menampilkan "Terputus"; bila sesi habis, halaman dimuat ulang.
     */
    Alpine.store('myPresence', {
        online: false,
        enabled: false,
        connected: true,
        cfg: null,
        init() {
            this.cfg = window.Welcoming?.presence;
            if (!this.cfg) return;
            this.online = this.enabled = !!this.cfg.initial;
            this.beat();
            setInterval(() => this.beat(), this.cfg.interval * 1000);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) this.beat(); });
            window.addEventListener('online', () => this.beat());
        },
        apply(data) {
            this.online = !!data.online;
            this.enabled = !!data.enabled;
            this.connected = true;
        },
        fail(e) {
            if ([401, 419].includes(e.response?.status)) { window.location.reload(); return; }
            this.connected = false;
            this.online = false;
        },
        beat() {
            return axios.post(this.cfg.heartbeatUrl).then(({ data }) => this.apply(data)).catch((e) => this.fail(e));
        },
        async toggle() {
            try {
                const { data } = await axios.post(this.cfg.toggleUrl, { online: !this.enabled });
                this.apply(data);
            } catch (e) {
                this.fail(e);
            }
        },
    });

    // Walkthrough Beranda: sorotan (spotlight) berpindah antar fitur, kartu penjelasan menempel di sebelahnya.
    // Selama berjalan halaman terkunci; hanya Lanjut, Kembali, dan Lewati yang bisa dipakai.
    Alpine.data('tour', (steps, doneUrl, autoStart) => ({
        steps, list: [], i: 0, active: false, hole: null, box: { left: 16, top: 16, arrow: null, arrowX: 0 }, shake: false,
        init() {
            if (autoStart) { setTimeout(() => this.start(), 600); }
            const reflow = () => { if (this.active) { this.measure(); } };
            window.addEventListener('resize', reflow);
            window.addEventListener('scroll', reflow, { passive: true });
        },
        get step() { return this.list[this.i] ?? {}; },
        get last() { return this.i === this.list.length - 1; },
        el(target) {
            return target ? [...document.querySelectorAll(`[data-tour="${target}"]`)].find((el) => el.offsetParent !== null && !el.closest('.tour')) : null;
        },
        start() {
            if (this.active) { return; }
            // Hanya langkah yang elemennya terlihat di layar ini (sidebar di desktop, bottom nav di HP).
            this.list = this.steps.filter((s) => !s.target || this.el(s.target));
            this.i = 0;
            this.active = true;
            this.show();
        },
        show() {
            const el = this.el(this.step.target);
            if (el) {
                const r = el.getBoundingClientRect();
                if (r.top < 90 || r.bottom > window.innerHeight - 90) { el.scrollIntoView({ block: 'center', behavior: 'smooth' }); }
            }
            this.measure();
            this.$nextTick(() => { this.measure(); this.$refs.next?.focus({ preventScroll: true }); });
            setTimeout(() => this.measure(), 450);
        },
        measure() {
            const el = this.el(this.step.target);
            const card = this.$refs.card;
            const vw = window.innerWidth, vh = window.innerHeight, gap = 18, pad = 8, m = 12;
            const cw = card?.offsetWidth || 340, ch = card?.offsetHeight || 220;
            if (!el) {
                this.hole = null;
                this.box = { left: (vw - cw) / 2, top: Math.max(m, (vh - ch) / 2), arrow: null, arrowX: 0 };
                return;
            }
            const r = el.getBoundingClientRect();
            const radius = parseFloat(getComputedStyle(el).borderRadius) || 16;
            this.hole = { top: r.top - pad, left: r.left - pad, width: r.width + pad * 2, height: r.height + pad * 2, radius: `${radius + pad}px` };
            const h = this.hole;
            const clampX = (x) => Math.min(Math.max(m, x), vw - cw - m);
            const clampY = (y) => Math.min(Math.max(m, y), vh - ch - m);
            const centerX = clampX(r.left + r.width / 2 - cw / 2);
            const arrowX = Math.min(Math.max(22, r.left + r.width / 2 - centerX - 9), cw - 40);
            if (h.top + h.height + gap + ch <= vh - m) {
                this.box = { left: centerX, top: h.top + h.height + gap, arrow: 'top', arrowX };
            } else if (h.top - gap - ch >= m) {
                this.box = { left: centerX, top: h.top - gap - ch, arrow: 'bottom', arrowX };
            } else if (h.left + h.width + gap + cw <= vw - m) {
                this.box = { left: h.left + h.width + gap, top: clampY(r.top + r.height / 2 - ch / 2), arrow: null, arrowX: 0 };
            } else if (h.left - gap - cw >= m) {
                this.box = { left: h.left - gap - cw, top: clampY(r.top + r.height / 2 - ch / 2), arrow: null, arrowX: 0 };
            } else {
                // Elemen besar memenuhi layar: kartu ditaruh di bawah layar, di atas sorotan.
                this.box = { left: (vw - cw) / 2, top: vh - ch - m, arrow: null, arrowX: 0 };
            }
        },
        next() { if (this.last) { this.finish(); } else { this.i++; this.show(); } },
        prev() { if (this.i > 0) { this.i--; this.show(); } },
        nudge() {
            this.shake = false;
            requestAnimationFrame(() => { this.shake = true; setTimeout(() => (this.shake = false), 450); });
        },
        finish() {
            this.active = false;
            this.hole = null;
            axios.post(doneUrl).catch(() => {});
        },
    }));
});

window.Alpine = Alpine;
Alpine.plugin(focus);

// Modul halaman (dimuat sesuai kebutuhan)
const pageModules = import.meta.glob('./pages/*.js');
const page = document.body?.dataset.page;
const loaders = [];
document.querySelectorAll('[data-module]').forEach((el) => loaders.push(el.dataset.module));
if (page) loaders.push(page);
Promise.all([...new Set(loaders)].map((name) => pageModules[`./pages/${name}.js`]?.().then((m) => m.default?.(Alpine))))
    .finally(() => Alpine.start());
