/** Wizard onboarding murid setelah register: tutorial → jenjang → sekolah → Guru BK (opsional). */
export default function (Alpine) {
    Alpine.data('onboarding', (cfg) => ({
        ...cfg,
        slide: 0,
        search: '',
        counselors: [],
        loading: false,
        submitting: false,
        get steps() {
            return cfg.firstTime ? ['tutorial', 'jenjang', 'sekolah', 'guru'] : ['jenjang', 'sekolah', 'guru'];
        },
        get schoolOptions() {
            const q = this.search.trim().toLowerCase();
            return this.schools.filter((s) => s.level === this.level && (!q || s.name.toLowerCase().includes(q) || (s.address || '').toLowerCase().includes(q)));
        },
        get selectedSchool() {
            return this.schools.find((s) => String(s.id) === this.schoolId);
        },
        init() {
            if (this.step === 'guru' && this.schoolId) this.loadCounselors();
            this.$watch('step', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        },
        async loadCounselors() {
            if (!this.schoolId) return;
            this.loading = true;
            try {
                const { data } = await window.axios.get(cfg.counselorsUrl, { params: { school_id: this.schoolId } });
                this.counselors = data;
            } catch (e) {
                this.counselors = [];
            } finally {
                this.loading = false;
            }
        },
    }));
}
