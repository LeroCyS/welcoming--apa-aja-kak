export default function (Alpine) {
    Alpine.data('startStory', (cfg) => ({
        step: 1,
        maxStep: 1,
        counselor: cfg.old.counselor_id ? Number(cfg.old.counselor_id) : 'any',
        selectedTopics: cfg.old.topics || [],
        anonymous: cfg.old.is_anonymous,
        submitting: false,
        stepError: '',
        topics: cfg.topics,
        studentName: cfg.studentName,
        studentClass: cfg.studentClass || '',
        steps: [
            { title: 'Pilih Guru BK', heading: 'Mau cerita ke siapa?', sub: 'Pilih guru yang kamu rasa paling nyaman. Bisa diganti nanti.' },
            { title: 'Pilih topik', heading: 'Ceritanya tentang apa?', sub: 'Boleh pilih lebih dari satu. Bingung? Pilih “Belum tahu / Campur aduk”.' },
            { title: 'Mode identitas', heading: 'Mau tampil sebagai siapa?', sub: 'Setelah ini kamu langsung masuk ke ruang chat dan bisa mulai mengetik.' },
        ],
        get counselorOptions() {
            return [...cfg.counselors, { id: 'any', name: 'Siapa saja yang tersedia', initial: '?', palette: ['#EDE5D0', '#3E3530'], photo: null, note: 'Ke guru yang online & paling longgar' }];
        },
        init() {
            if (cfg.hasErrors) { this.step = 3; this.maxStep = 3; return; }
            if (cfg.preselect) { this.step = 2; this.maxStep = 2; }
        },
        counselorName() {
            if (this.counselor === 'any') return 'Guru BK yang tersedia';
            return cfg.counselors.find((c) => c.id === this.counselor)?.name || 'Guru BK';
        },
        topicSummary() {
            const names = this.topics.filter((t) => this.selectedTopics.includes(t.id)).map((t) => t.name);
            return names.length ? names.join(', ') : 'Belum pilih topik';
        },
        summary(n) {
            return [this.counselorName(), this.selectedTopics.length ? this.selectedTopics.length + ' topik' : '-', this.anonymous ? 'Anonim' : 'Tampilkan nama'][n - 1];
        },
        toggleTopic(id) {
            this.selectedTopics = this.selectedTopics.includes(id) ? this.selectedTopics.filter((t) => t !== id) : [...this.selectedTopics, id];
        },
        validate() {
            this.stepError = '';
            if (this.step === 2 && this.selectedTopics.length === 0) this.stepError = 'Pilih minimal satu topik, ya.';
            return !this.stepError;
        },
        next() {
            if (!this.validate()) return;
            this.step = Math.min(this.steps.length, this.step + 1);
            this.maxStep = Math.max(this.maxStep, this.step);
            this.$nextTick(() => this.$refs.heading?.focus());
        },
        prev() { this.stepError = ''; this.step = Math.max(1, this.step - 1); },
        go(n) {
            if (n > this.maxStep) return;
            if (n > this.step && !this.validate()) return;
            this.step = n;
        },
    }));
}
