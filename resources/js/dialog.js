/**
 * Dialog konfirmasi & isian bergaya Welcoming, pengganti confirm()/prompt() bawaan browser.
 * Keduanya mengembalikan Promise: wlConfirm → true/false, wlPrompt → teks atau null.
 * Form cukup diberi atribut: data-confirm="Teks" (opsional data-confirm-title, data-confirm-ok, data-confirm-tone="danger").
 */
const ICONS = {
    danger: '<path d="M12 3l9.5 17h-19z"/><path d="M12 10v4M12 17h.01"/>',
    default: '<circle cx="12" cy="12" r="9"/><path d="M9.6 9.3a2.5 2.5 0 0 1 4.8.9c0 1.7-2.4 2.3-2.4 3.8M12 17h.01"/>',
    prompt: '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7L11.5 6.8"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1.5-1.5"/>',
};

function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function open({ title, text = '', okLabel = 'Ya, lanjutkan', cancelLabel = 'Batal', tone = 'default', input = null }) {
    return new Promise((resolve) => {
        const previous = document.activeElement;
        const danger = tone === 'danger';
        const root = document.createElement('div');
        root.className = 'scrim fixed inset-0 z-[95] flex items-end sm:items-center justify-center sm:p-6 animate-[fade-in_.18s_ease-out_both]';
        root.innerHTML = `
            <section role="alertdialog" aria-modal="true" aria-labelledby="wl-dlg-title" aria-describedby="wl-dlg-text"
                     class="w-full sm:max-w-[420px] bg-cream rounded-t-[28px] sm:rounded-[28px] shadow-modal p-6 sm:p-7 flex flex-col gap-4 animate-pop">
                <span class="w-14 h-14 rounded-2xl flex items-center justify-center -rotate-3 ${danger ? 'bg-danger-tint text-danger' : 'bg-terracotta-tint text-terracotta-deep'}">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[input ? 'prompt' : (danger ? 'danger' : 'default')]}</svg>
                </span>
                <div class="flex flex-col gap-1.5">
                    <h2 id="wl-dlg-title" class="text-[22px] leading-tight">${escapeHtml(title)}</h2>
                    ${text ? `<p id="wl-dlg-text" class="text-[15px] leading-relaxed text-ink-soft">${escapeHtml(text)}</p>` : ''}
                </div>
                ${input ? `<input type="${input.type || 'text'}" class="field" placeholder="${escapeHtml(input.placeholder || '')}" value="${escapeHtml(input.value || '')}" data-dlg-input>` : ''}
                <p class="field-error !mt-0 hidden" data-dlg-error></p>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 mt-1">
                    <button type="button" class="btn btn-outline !h-12" data-dlg-cancel>${escapeHtml(cancelLabel)}</button>
                    <button type="button" class="btn ${danger ? 'btn-danger' : 'btn-primary'} !h-12" data-dlg-ok>${escapeHtml(okLabel)}</button>
                </div>
            </section>`;
        document.body.appendChild(root);
        const panel = root.querySelector('section');
        const field = root.querySelector('[data-dlg-input]');
        const error = root.querySelector('[data-dlg-error]');
        const okBtn = root.querySelector('[data-dlg-ok]');

        const close = (value) => {
            document.removeEventListener('keydown', onKey, true);
            root.remove();
            previous?.focus?.({ preventScroll: true });
            resolve(value);
        };
        const submit = () => {
            if (!input) return close(true);
            const value = field.value.trim();
            const message = input.validate ? input.validate(value) : '';
            if (message) {
                error.textContent = message;
                error.classList.remove('hidden');
                field.focus();
                return;
            }
            close(value);
        };
        const onKey = (e) => {
            if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); close(input ? null : false); }
            if (e.key === 'Enter' && input && document.activeElement === field) { e.preventDefault(); submit(); }
            if (e.key === 'Tab') {
                // Fokus tetap di dalam dialog.
                const items = [...panel.querySelectorAll('input, button')];
                const i = items.indexOf(document.activeElement);
                const next = e.shiftKey ? (i <= 0 ? items.length - 1 : i - 1) : (i === items.length - 1 ? 0 : i + 1);
                e.preventDefault();
                items[next].focus();
            }
        };
        document.addEventListener('keydown', onKey, true);
        root.addEventListener('click', (e) => { if (e.target === root) close(input ? null : false); });
        root.querySelector('[data-dlg-cancel]').addEventListener('click', () => close(input ? null : false));
        okBtn.addEventListener('click', submit);
        (field || okBtn).focus();
    });
}

export function wlConfirm(options) {
    return open(typeof options === 'string' ? { title: options } : options);
}

export function wlPrompt(options) {
    return open({ okLabel: 'Simpan', ...options, input: { placeholder: options.placeholder, value: options.value, type: options.type, validate: options.validate } });
}

/** Form / tombol submit dengan data-confirm meminta konfirmasi lewat dialog Welcoming sebelum dikirim. */
export function bindConfirmForms() {
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        const source = e.submitter?.dataset.confirm ? e.submitter : (form.dataset.confirm ? form : null);
        if (!source) return;
        if (form.dataset.confirmPassed) {
            delete form.dataset.confirmPassed;
            return;
        }
        e.preventDefault();
        e.stopImmediatePropagation();
        const ok = await wlConfirm({
            title: source.dataset.confirmTitle || source.dataset.confirm,
            text: source.dataset.confirmTitle ? source.dataset.confirm : '',
            okLabel: source.dataset.confirmOk || 'Ya, lanjutkan',
            tone: source.dataset.confirmTone || 'default',
        });
        if (!ok) return;
        form.dataset.confirmPassed = '1';
        form.requestSubmit(e.submitter || undefined);
    }, true);
}
