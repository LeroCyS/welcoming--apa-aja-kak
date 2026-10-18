import voiceRecorder from '../voice-recorder';

/**
 * Ruang chat (murid & guru). Realtime via Echo bila tersedia, dan SELALU polling tiap 3 detik sebagai cadangan.
 * Event broadcast hanya "ping"; isi pesan diambil lewat endpoint yang sudah diotorisasi.
 * Fitur: centang dua (terkirim/dibaca), "sedang mengetik…", tombol pesan baru, dan foto.
 */
let registered = false;

export default function (Alpine) {
    if (registered) return;
    registered = true;
    Alpine.data('voiceRecorder', voiceRecorder);

    Alpine.data('chatRoom', (cfg) => ({
        body: '',
        sending: false,
        error: '',
        lastId: cfg.lastId,
        version: cfg.version,
        statusLabel: null,
        // Lawan chat sedang mengetik (dari event realtime atau hasil polling).
        typing: false,
        typingTimer: null,
        lastTypingSent: 0,
        // Pesan baru yang masuk saat pengguna sedang membaca riwayat di atas.
        unseen: 0,
        baseTitle: document.title,
        // Foto
        photo: null,
        photoUrl: null,
        caption: '',
        mediaError: '',
        uploading: false,
        // Balasan cepat: /shortcut + Enter memasang template (Guru BK)
        slashIndex: 0,
        slashClosed: false,
        // Edit / hapus pesan
        msgMenu: null,
        editing: false,
        editText: '',
        menuBusy: false,
        menuError: '',

        init() {
            try { this.body = localStorage.getItem(cfg.draftKey) || ''; } catch (e) { /* storage diblokir */ }
            this.$nextTick(() => { this.scrollBottom(); this.autosize(); });
            window.addEventListener('load', () => this.scrollBottom(), { once: true });
            document.fonts?.ready.then(() => this.scrollBottom());
            window.wlSubscribe(cfg.channel, {
                MessageSent: () => this.fetchNew(),
                MessageRead: () => this.fetchNew(),
                MessageDelivered: () => this.fetchNew(),
                ConversationUpdated: () => this.fetchFull(),
                Typing: (e) => { if (e.role !== cfg.role) this.showTyping(); },
            }, () => this.fetchNew(), 3000);
            window.addEventListener('chat-append', (e) => this.apply(e.detail));
            window.addEventListener('chat-template', (e) => { this.body = e.detail; this.$nextTick(() => { this.autosize(); this.$refs.input?.focus(); }); });
            this.$refs.scroller?.addEventListener('scroll', () => { if (this.nearBottom()) this.clearUnseen(); }, { passive: true });
            window.addEventListener('scroll', () => { if (this.nearBottom()) this.clearUnseen(); }, { passive: true });
            document.addEventListener('visibilitychange', () => { if (!document.hidden && this.nearBottom()) this.clearUnseen(); });
            if (location.hash) setTimeout(() => document.querySelector(location.hash)?.scrollIntoView({ block: 'center' }), 300);
        },
        /** Di HP (murid) halaman yang menggulung, di layar lebar kotak pesan yang menggulung. */
        pageScrolls() {
            const s = this.$refs.scroller;
            return !s || s.scrollHeight <= s.clientHeight + 2;
        },
        nearBottom() {
            if (this.pageScrolls()) return window.innerHeight + window.scrollY >= document.body.scrollHeight - 200;
            const s = this.$refs.scroller;
            return s.scrollHeight - s.scrollTop - s.clientHeight < 160;
        },
        scrollBottom(smooth = false) {
            const s = this.$refs.scroller;
            if (s) s.scrollTo({ top: s.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
            if (this.pageScrolls() || window.innerWidth < 1024) window.scrollTo({ top: document.body.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
            this.clearUnseen();
        },
        clearUnseen() {
            this.unseen = 0;
            document.title = this.baseTitle;
        },
        autosize() {
            const el = this.$refs.input;
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 160) + 'px';
        },
        saveDraft() {
            try { this.body ? localStorage.setItem(cfg.draftKey, this.body) : localStorage.removeItem(cfg.draftKey); } catch (e) { /* abaikan */ }
        },
        /** Kabari lawan chat bahwa kita sedang mengetik (paling sering tiap 2,5 detik). */
        notifyTyping() {
            if (!cfg.typingUrl || !this.body.trim()) return;
            const now = Date.now();
            if (now - this.lastTypingSent < 2500) return;
            this.lastTypingSent = now;
            window.axios.post(cfg.typingUrl).catch(() => {});
        },
        showTyping() {
            const wasNear = this.nearBottom();
            this.typing = true;
            clearTimeout(this.typingTimer);
            this.typingTimer = setTimeout(() => { this.typing = false; }, 4500);
            if (wasNear) this.$nextTick(() => this.scrollBottom(true));
        },
        async fetchNew() {
            const { data } = await window.axios.get(cfg.messagesUrl, { params: { after: this.lastId } });
            this.apply(data);
        },
        async fetchFull() {
            const { data } = await window.axios.get(cfg.messagesUrl, { params: { full: 1 } });
            this.apply(data);
        },
        apply(data) {
            if (!data) return;
            const stick = this.nearBottom();
            const box = this.$refs.scroller;
            let incoming = 0;
            if (data.full) {
                box.innerHTML = data.messages.map((m) => m.html).join('');
            } else {
                data.messages.forEach((m) => {
                    if (box.querySelector(`[data-message-id="${m.id}"]`)) return;
                    box.insertAdjacentHTML('beforeend', m.html);
                    // Animasi masuk: pesan sendiri (punya centang) dari kanan, pesan lawan bicara dari kiri.
                    const el = box.lastElementChild;
                    const own = !!el?.querySelector('[data-receipt]');
                    el?.classList.add('msg-in', own ? 'msg-in-own' : 'msg-in-other');
                    if (!own) incoming++;
                });
            }
            if (data.last_id) this.lastId = Math.max(this.lastId, data.last_id);
            this.markReceipts(data.delivered_ids || [], 'delivered');
            this.markReceipts(data.read_ids || [], 'read');
            if (data.status) this.statusLabel = data.status;
            if (typeof data.typing === 'boolean') {
                if (data.typing) this.showTyping();
            }
            if (incoming) this.typing = false;
            if (data.version && data.version !== this.version) {
                this.version = data.version;
                if (!data.full) this.fetchFull();
            }
            if (!data.messages?.length) return;
            if (stick || data.mine) {
                this.$nextTick(() => this.scrollBottom(!data.mine));
            } else if (incoming) {
                this.unseen += incoming;
            }
            if (incoming && document.hidden) {
                this.unseen = Math.max(this.unseen, incoming);
                document.title = `(${this.unseen}) ${this.baseTitle}`;
            }
        },
        /** Centang pesan kita: satu → dua abu-abu (sampai) → dua hijau (dibaca). Status tidak pernah mundur. */
        markReceipts(ids, state) {
            const rank = { sent: 0, delivered: 1, read: 2 };
            const labels = { delivered: ['Sampai', 'Sampai'], read: ['Dibaca', 'Dibaca'] };
            ids.forEach((id) => {
                const el = this.$refs.scroller.querySelector(`[data-receipt="${id}"]`);
                if (!el || rank[el.dataset.state] >= rank[state]) return;
                el.dataset.state = state;
                el.setAttribute('aria-label', labels[state][0]);
                el.title = labels[state][1];
            });
        },
        /** Kata setelah "/" bila seluruh isi kolom berupa /shortcut, selain itu null. */
        get slashQuery() {
            const m = /^\/([\w-]*)$/.exec(this.body || '');
            return m ? m[1].toLowerCase() : null;
        },
        get slashMatches() {
            if (this.slashQuery === null || this.slashClosed || !cfg.quickReplies?.length) return [];
            const q = this.slashQuery;
            const seen = new Set();
            return cfg.quickReplies
                .filter((t) => t.shortcut && !seen.has(t.shortcut) && seen.add(t.shortcut))
                .filter((t) => t.shortcut.startsWith(q) || t.title.toLowerCase().includes(q))
                .sort((a, b) => (b.shortcut === q) - (a.shortcut === q) || (b.shortcut.startsWith(q)) - (a.shortcut.startsWith(q)))
                .slice(0, 6);
        },
        moveSlash(step) {
            if (!this.slashMatches.length) return false;
            this.slashIndex = (this.slashIndex + step + this.slashMatches.length) % this.slashMatches.length;
            return true;
        },
        /** Pasang template terpilih ke kolom ketik (tidak langsung terkirim). Mengembalikan true bila dipakai. */
        useQuickReply(template = null) {
            const t = template || this.slashMatches[this.slashIndex];
            if (!t) return false;
            this.body = t.body.replaceAll('{sapaan}', cfg.sapaan || '');
            this.slashIndex = 0;
            this.saveDraft();
            this.$nextTick(() => { this.autosize(); const el = this.$refs.input; el?.focus(); el?.setSelectionRange(el.value.length, el.value.length); });
            return true;
        },
        openMessageMenu(detail) {
            this.msgMenu = detail;
            this.editing = false;
            this.editText = detail.body || '';
            this.menuError = '';
        },
        closeMessageMenu() {
            this.msgMenu = null;
            this.editing = false;
            this.menuError = '';
        },
        startEdit() {
            this.editing = true;
            this.$nextTick(() => this.$refs.editInput?.focus());
        },
        async messageAction(request, fallback) {
            if (this.menuBusy) return;
            this.menuBusy = true;
            this.menuError = '';
            try {
                const { data } = await request();
                this.apply(data);
                this.closeMessageMenu();
            } catch (e) {
                this.menuError = this.handleError(e, fallback) || fallback;
                const errors = e.response?.data?.errors;
                if (errors) this.menuError = Object.values(errors)[0][0];
            } finally {
                this.menuBusy = false;
            }
        },
        saveEdit() {
            const text = this.editText.trim();
            if (!text || !this.msgMenu) return;
            this.messageAction(() => window.axios.patch(cfg.messageUrl.replace('__ID__', this.msgMenu.id), { body: text }), 'Pesan belum tersimpan. Coba lagi, ya.');
        },
        deleteMessage(scope) {
            if (!this.msgMenu) return;
            this.messageAction(() => window.axios.delete(cfg.messageUrl.replace('__ID__', this.msgMenu.id), { data: { scope } }), 'Pesan belum terhapus. Coba lagi, ya.');
        },
        handleError(e, fallback) {
            if (e.response?.status === 423) return '';
            if (e.response?.status === 429) return 'Pelan-pelan dulu ya, kamu mengirim terlalu banyak.';
            return e.response?.data?.message || fallback;
        },
        async send() {
            const text = this.body.trim();
            if (!text || this.sending) return;
            this.sending = true;
            this.error = '';
            try {
                const { data } = await window.axios.post(cfg.sendUrl, { body: text, after: this.lastId });
                this.body = '';
                this.saveDraft();
                this.$nextTick(() => this.autosize());
                this.apply({ ...data, mine: true });
            } catch (e) {
                this.error = this.handleError(e, 'Pesan belum terkirim. Coba lagi, ya.');
            } finally {
                this.sending = false;
            }
        },

        // ---------- Foto ----------
        pickPhoto() { this.$refs.photoInput?.click(); },
        async photoChosen(e) {
            let file = e.target.files?.[0];
            e.target.value = '';
            if (!file) return;
            if (!file.type.startsWith('image/')) { this.error = 'File harus berupa foto.'; return; }
            // Foto HP diperkecil dulu di browser agar tidak ditolak batas unggah server.
            file = await window.wlShrinkImage(file);
            if (file.size > cfg.imageMaxKb * 1024) { this.error = `Ukuran foto maksimal ${Math.round(cfg.imageMaxKb / 1024)} MB.`; return; }
            this.photo = file;
            this.photoUrl = URL.createObjectURL(file);
            this.caption = '';
            this.mediaError = '';
            this.$dispatch('open-modal', 'chat-photo');
        },
        cancelPhoto() {
            if (this.photoUrl) URL.revokeObjectURL(this.photoUrl);
            this.photo = null;
            this.photoUrl = null;
            this.$dispatch('close-modal', 'chat-photo');
        },
        async sendPhoto() {
            if (!this.photo || this.uploading) return;
            this.uploading = true;
            this.mediaError = '';
            const fd = new FormData();
            fd.append('image', this.photo);
            if (this.caption.trim()) fd.append('caption', this.caption.trim());
            fd.append('after', this.lastId);
            try {
                const { data } = await window.axios.post(cfg.imageUrl, fd);
                this.cancelPhoto();
                this.apply({ ...data, mine: true });
            } catch (e) {
                this.mediaError = this.handleError(e, 'Foto belum terkirim. Coba lagi, ya.');
            } finally {
                this.uploading = false;
            }
        },
    }));
}
