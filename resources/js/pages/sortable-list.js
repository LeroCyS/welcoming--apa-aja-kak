import Sortable from 'sortablejs';

/** Daftar yang bisa diurutkan ulang (mis. kontak darurat). POST {ids: []} ke data-sortable-url. */
export default function () {
    document.querySelectorAll('[data-sortable]').forEach((el) => {
        Sortable.create(el, {
            handle: '[data-handle]',
            animation: 150,
            onEnd: () => {
                const ids = [...el.querySelectorAll('[data-id]')].map((r) => r.dataset.id);
                window.axios.post(el.dataset.sortableUrl, { ids });
            },
        });
    });
}
