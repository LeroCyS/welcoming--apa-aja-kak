export default function (Alpine) {
    Alpine.data('moodCheckin', (cfg) => ({
        mood: cfg.mood,
        note: cfg.note || '',
        songUrl: cfg.songUrl || '',
        noteError: '',
        replies: cfg.replies,
        busy: false,
        noteOpen: false,
        promptOpen: cfg.prompt,
        promptDays: window.__promptDays || [],
        faces: {},
        init() {
            this.$nextTick(() => {
                this.$refs.faces?.content?.querySelectorAll('[data-mood]').forEach((el) => { this.faces[el.dataset.mood] = el.innerHTML; });
            });
        },
        async pick(mood) {
            this.busy = true;
            try {
                const { data } = await window.axios.post(cfg.url, { mood });
                this.mood = data.mood;
                this.noteOpen = !this.note;
                if (data.prompt) {
                    this.promptDays = data.prompt_days;
                    setTimeout(() => { this.promptOpen = true; }, 600);
                }
            } finally { this.busy = false; }
        },
        async saveNote() {
            this.busy = true;
            this.noteError = '';
            try {
                const { data } = await window.axios.post(cfg.url, { mood: this.mood, note: this.note, song_url: this.songUrl });
                this.songUrl = data.song?.url || '';
                this.noteOpen = false;
            } catch (e) {
                this.noteError = e.response?.data?.message || 'Catatan belum tersimpan. Coba lagi, ya.';
            } finally { this.busy = false; }
        },
        respond(response) {
            this.promptOpen = false;
            window.axios.post(window.__promptRespondUrl, { response });
        },
    }));
}
