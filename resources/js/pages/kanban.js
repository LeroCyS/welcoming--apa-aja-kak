import Sortable from 'sortablejs';

/** Kanban kasus: drag-and-drop ke kolom status (ubah status) atau kolom buatan guru (hanya pengelompokan). Kolom "Selesai" butuh konfirmasi. */
export default function (Alpine) {
    Alpine.data('caseBoard', () => ({
        transfer: { open: false, action: '', name: '' },
        resolve: { open: false, name: '', card: null, from: null, oldIndex: 0 },
        message: '',
        // Kolom yang diciutkan (default: "Selesai"), diingat per perangkat.
        collapsed: (() => { try { return JSON.parse(localStorage.getItem('wl-kanban-collapsed')) || { resolved: true }; } catch (e) { return { resolved: true }; } })(),
        toggleColumn(key) {
            this.collapsed = { ...this.collapsed, [key]: !this.collapsed[key] };
            try { localStorage.setItem('wl-kanban-collapsed', JSON.stringify(this.collapsed)); } catch (e) { /* abaikan */ }
        },
        init() {
            this.$el.querySelectorAll('[data-column]').forEach((col) => {
                Sortable.create(col, {
                    group: 'cases',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    filter: 'a,button',
                    preventOnFilter: false,
                    onEnd: (e) => {
                        if (e.from === e.to) return;
                        const column = e.to.dataset.column;
                        if (column === 'resolved') {
                            Object.assign(this.resolve, { open: true, name: e.item.dataset.name, card: e.item, from: e.from, oldIndex: e.oldIndex });
                            return;
                        }
                        this.save(e.item, column, e.from, e.oldIndex);
                    },
                });
            });
            this.recount();
        },
        async save(card, column, from, oldIndex) {
            try {
                const { data } = await window.axios.patch(card.dataset.boardUrl, { column });
                this.message = data.warning ? data.message + ' ' + data.warning : data.message;
                setTimeout(() => { this.message = ''; }, 5000);
            } catch (e) {
                from.insertBefore(card, from.children[oldIndex] || null);
                this.message = 'Perubahan belum tersimpan. Coba lagi.';
            }
            this.recount();
        },
        confirmResolve() {
            const r = this.resolve;
            r.open = false;
            this.save(r.card, 'resolved', r.from, r.oldIndex);
        },
        cancelResolve() {
            const r = this.resolve;
            r.open = false;
            r.from.insertBefore(r.card, r.from.children[r.oldIndex] || null);
            this.recount();
        },
        recount() {
            this.$el.querySelectorAll('[data-column]').forEach((col) => {
                const badge = col.parentElement.querySelector('[data-count]');
                if (badge) badge.textContent = col.querySelectorAll('[data-uuid]').length;
            });
        },
        openTransfer(action, name) { Object.assign(this.transfer, { open: true, action, name }); },
    }));
}
