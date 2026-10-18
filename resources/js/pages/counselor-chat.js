import chat from './chat';
import inbox from './inbox';

export default function (Alpine) {
    chat(Alpine);
    inbox(Alpine);

    Alpine.data('templatePicker', (cfg) => ({
        all: false,
        selected: null,
        text: '',
        get groups() {
            const list = cfg.templates.filter((t) => this.all || t.topic_id === null || cfg.topics.includes(t.topic_id));
            const map = {};
            list.forEach((t) => { (map[t.topic] ||= []).push(t); });
            return Object.entries(map).map(([name, items]) => ({ name, items }));
        },
        pick(t) { this.selected = t; this.text = t.body.replaceAll('{sapaan}', cfg.sapaan); },
        use() {
            window.dispatchEvent(new CustomEvent('chat-template', { detail: this.text }));
            this.$dispatch('close-modal', 'template');
        },
    }));

    // Tata letak ruang chat guru: daftar kiri & panel detail bisa dibuka/tutup, diingat per perangkat.
    Alpine.data('convLayout', () => {
        const read = (key, fallback) => { try { const v = localStorage.getItem(key); return v === null ? fallback : v === '1'; } catch (e) { return fallback; } };
        const save = (key, val) => { try { localStorage.setItem(key, val ? '1' : '0'); } catch (e) { /* abaikan */ } };
        const wide = window.innerWidth >= 1280;
        return {
            list: read('wl-conv-list', window.innerWidth >= 1536),
            // Di layar kecil panel berupa laci, jadi selalu mulai tertutup.
            panel: wide ? read('wl-conv-panel', true) : false,
            toggleList() { this.list = !this.list; save('wl-conv-list', this.list); },
            togglePanel() { this.panel = !this.panel; if (window.innerWidth >= 1280) save('wl-conv-panel', this.panel); },
        };
    });

    // Penilaian urgensi oleh guru (header, banner "Belum dinilai", dan panel Kasus berbagi status lewat event).
    Alpine.data('urgencyPicker', (cfg) => ({
        value: cfg.value,
        menu: false,
        busy: false,
        error: '',
        styles: cfg.styles,
        get key() { return this.value || 'unassessed'; },
        get label() { return this.styles[this.key][0]; },
        init() {
            window.addEventListener('urgency-changed', (e) => { this.value = e.detail.urgency; });
        },
        async set(urgency) {
            if (!urgency || this.busy) return;
            this.busy = true;
            this.error = '';
            try {
                const { data } = await window.axios.patch(cfg.url, { urgency });
                window.dispatchEvent(new CustomEvent('urgency-changed', { detail: data }));
            } catch (e) {
                this.error = e.response?.data?.message || 'Urgensi belum tersimpan. Coba lagi, ya.';
            } finally {
                this.busy = false;
            }
        },
    }));

    Alpine.data('articlePicker', (cfg) => ({
        q: '', note: '', sent: [], busy: false,
        get filtered() {
            const q = this.q.toLowerCase();
            return cfg.articles.filter((a) => !q || a.title.toLowerCase().includes(q) || (a.category || '').toLowerCase().includes(q));
        },
        async send(a) {
            this.busy = true;
            try {
                const { data } = await window.axios.post(cfg.url, { article_id: a.id, note: this.note });
                this.sent.push(a.id);
                window.dispatchEvent(new CustomEvent('chat-append', { detail: { ...data, mine: true } }));
            } finally { this.busy = false; }
        },
    }));

    Alpine.data('summaryPoller', () => ({
        init() {
            const tick = async () => {
                const el = this.$el.querySelector('[data-summary-pending]');
                if (!el) return;
                const { data } = await window.axios.get(el.dataset.summaryPending);
                if (data.status !== 'pending') this.$el.innerHTML = data.html;
            };
            setInterval(tick, 3000);
            window.wlSubscribe('counselor.' + window.Welcoming.userId, { SummaryReady: tick });
        },
    }));
}
