/**
 * Dropdown bergaya Welcoming, pengganti tampilan <select> bawaan browser.
 * <select> asli tetap ada (tersembunyi) sehingga form, x-model, dan validasi tetap bekerja;
 * pilihan di dropdown mengubah nilainya lalu memicu event input & change.
 * Lewati dengan atribut data-native. Select baru (mis. di dalam modal) ikut diubah otomatis.
 */
const CHEVRON = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>';
const CHECK = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
const valueSetter = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
const indexSetter = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'selectedIndex');
let uid = 0;
let openMenu = null;
// Alpine x-model mengubah pilihan lewat option.selected tanpa event, jadi label disinkronkan berkala (murah: hanya beberapa select).
const syncs = new Set();
setInterval(() => syncs.forEach((fn) => fn()), 400);

function enhance(select) {
    if (select.dataset.wlSelect || select.multiple || select.dataset.native !== undefined || select.size > 1) return;
    select.dataset.wlSelect = '1';
    const id = `wl-sel-${++uid}`;

    const button = document.createElement('button');
    button.type = 'button';
    button.className = `${select.className} wl-select-btn`;
    button.setAttribute('aria-haspopup', 'listbox');
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('aria-controls', id);
    if (select.id) {
        // Label <label for="..."> tetap mengarah ke tombol baru.
        document.querySelectorAll(`label[for="${CSS.escape(select.id)}"]`).forEach((l) => { l.addEventListener('click', (e) => { e.preventDefault(); button.focus(); }); });
        button.setAttribute('aria-labelledby', [...document.querySelectorAll(`label[for="${CSS.escape(select.id)}"]`)].map((l) => (l.id ||= `${id}-lbl`)).join(' ') || '');
    }
    button.innerHTML = `<span class="wl-select-label truncate"></span><span class="wl-select-chevron">${CHEVRON}</span>`;
    const label = button.querySelector('.wl-select-label');

    select.classList.add('wl-select-native');
    select.tabIndex = -1;
    select.setAttribute('aria-hidden', 'true');
    select.insertAdjacentElement('afterend', button);

    const sync = () => {
        if (!select.isConnected) { syncs.delete(sync); button.remove(); return; }
        const opt = select.options[select.selectedIndex];
        const text = opt ? opt.textContent.trim() : '';
        if (label.textContent === text && button.disabled === select.disabled) return;
        label.textContent = text;
        label.classList.toggle('text-ink-soft', !opt || opt.value === '');
        button.disabled = select.disabled;
    };
    // Nilai yang diubah lewat kode (mis. x-model) juga memperbarui tampilan.
    Object.defineProperty(select, 'value', { get() { return valueSetter.get.call(this); }, set(v) { valueSetter.set.call(this, v); sync(); }, configurable: true });
    Object.defineProperty(select, 'selectedIndex', { get() { return indexSetter.get.call(this); }, set(v) { indexSetter.set.call(this, v); sync(); }, configurable: true });
    syncs.add(sync);
    select.addEventListener('change', sync);
    new MutationObserver(sync).observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled'] });
    sync();

    const choose = (index) => {
        if (index !== select.selectedIndex) {
            indexSetter.set.call(select, index);
            sync();
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
        close();
        button.focus();
    };

    let panel = null;
    let active = -1;
    const items = () => [...panel.querySelectorAll('[role="option"]:not([aria-disabled="true"])')];
    const highlight = (i) => {
        const list = items();
        if (!list.length) return;
        active = (i + list.length) % list.length;
        list.forEach((el, k) => el.classList.toggle('is-active', k === active));
        list[active].scrollIntoView({ block: 'nearest' });
        button.setAttribute('aria-activedescendant', list[active].id);
    };
    const place = () => {
        const r = button.getBoundingClientRect();
        const below = window.innerHeight - r.bottom;
        const h = Math.min(panel.scrollHeight, 288);
        panel.style.left = `${Math.max(8, Math.min(r.left, window.innerWidth - Math.max(r.width, 200) - 8))}px`;
        panel.style.minWidth = `${r.width}px`;
        panel.style.top = below < h + 12 && r.top > h + 12 ? `${r.top - h - 6}px` : `${r.bottom + 6}px`;
    };
    const close = () => {
        if (!panel) return;
        panel.remove();
        panel = null;
        openMenu = null;
        button.setAttribute('aria-expanded', 'false');
        button.removeAttribute('aria-activedescendant');
        window.removeEventListener('resize', place);
        window.removeEventListener('scroll', onScroll, true);
    };
    const onScroll = (e) => { if (panel && !panel.contains(e.target)) close(); };
    const open = () => {
        if (select.disabled) return;
        openMenu?.();
        panel = document.createElement('div');
        panel.id = id;
        panel.setAttribute('role', 'listbox');
        panel.className = 'wl-select-panel';
        [...select.options].forEach((o, i) => {
            const el = document.createElement('div');
            el.id = `${id}-o${i}`;
            el.setAttribute('role', 'option');
            el.setAttribute('aria-selected', String(i === select.selectedIndex));
            if (o.disabled) el.setAttribute('aria-disabled', 'true');
            el.className = 'wl-select-option';
            el.innerHTML = `<span class="truncate"></span>${i === select.selectedIndex ? CHECK : ''}`;
            el.firstChild.textContent = o.textContent.trim();
            el.addEventListener('mousedown', (e) => e.preventDefault());
            el.addEventListener('click', () => { if (!o.disabled) choose(i); });
            el.addEventListener('mousemove', () => highlight(items().indexOf(el)));
            panel.appendChild(el);
        });
        document.body.appendChild(panel);
        place();
        openMenu = close;
        button.setAttribute('aria-expanded', 'true');
        highlight(Math.max(0, items().findIndex((el) => el.getAttribute('aria-selected') === 'true')));
        window.addEventListener('resize', place);
        window.addEventListener('scroll', onScroll, true);
    };

    button.addEventListener('click', () => (panel ? close() : open()));
    button.addEventListener('blur', () => setTimeout(() => { if (panel && !panel.contains(document.activeElement)) close(); }, 120));
    let typed = '';
    let typedAt = 0;
    button.addEventListener('keydown', (e) => {
        if (['ArrowDown', 'ArrowUp'].includes(e.key)) {
            e.preventDefault();
            if (!panel) return open();
            highlight(active + (e.key === 'ArrowDown' ? 1 : -1));
        } else if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            if (!panel) return open();
            const el = items()[active];
            if (el) choose(Number(el.id.split('-o').pop()));
        } else if (e.key === 'Escape' && panel) {
            e.preventDefault();
            e.stopPropagation();
            close();
        } else if (e.key.length === 1 && panel) {
            // Ketik huruf untuk melompat ke pilihan.
            typed = Date.now() - typedAt > 700 ? e.key.toLowerCase() : typed + e.key.toLowerCase();
            typedAt = Date.now();
            const k = items().findIndex((el) => el.textContent.trim().toLowerCase().startsWith(typed));
            if (k >= 0) highlight(k);
        }
    });
}

export function initSelects(root = document) {
    root.querySelectorAll('select').forEach(enhance);
    new MutationObserver((records) => {
        records.forEach((r) => r.addedNodes.forEach((n) => {
            if (n.nodeType !== 1) return;
            if (n.tagName === 'SELECT') enhance(n);
            else n.querySelectorAll?.('select').forEach(enhance);
        }));
    }).observe(document.body, { childList: true, subtree: true });
    document.addEventListener('mousedown', (e) => {
        if (openMenu && !e.target.closest('.wl-select-panel, .wl-select-btn')) openMenu();
    });
}
