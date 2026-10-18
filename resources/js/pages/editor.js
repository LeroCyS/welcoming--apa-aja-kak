/** Editor artikel sederhana (contenteditable + execCommand). Body tetap disanitasi di server. */
export default function (Alpine) {
    Alpine.data('articleEditor', (cfg) => ({
        title: cfg.title || '',
        excerpt: cfg.excerpt || '',
        body: cfg.body || '',
        cover: cfg.cover,
        categories: cfg.categories,
        category: String(document.getElementById('ed-cat')?.value || ''),
        init() {
            this.$refs.editor.innerHTML = this.body || '<p></p>';
            document.execCommand('defaultParagraphSeparator', false, 'p');
        },
        sync() { this.body = this.$refs.editor.innerHTML; },
        cmd(name, value = null) { this.$refs.editor.focus(); document.execCommand(name, false, value); this.sync(); },
        block(tag) { this.cmd('formatBlock', tag === 'p' ? 'P' : tag.toUpperCase()); },
        link() { this.askUrl('Tambah tautan', 'https://…', 'createLink'); },
        image() { this.askUrl('Sisipkan gambar dari URL', 'https://…/gambar.jpg', 'insertImage'); },
        /** Minta URL lewat dialog Welcoming; posisi kursor di editor dikembalikan sebelum disisipkan. */
        async askUrl(title, placeholder, command) {
            const sel = window.getSelection();
            const range = sel?.rangeCount ? sel.getRangeAt(0) : null;
            const url = await window.wlPrompt({ title, placeholder, type: 'url', okLabel: 'Sisipkan', validate: (v) => (/^https?:\/\//.test(v) ? '' : 'Alamat harus diawali http:// atau https://') });
            if (!url) return;
            this.$refs.editor.focus();
            if (range && this.$refs.editor.contains(range.commonAncestorContainer)) {
                sel.removeAllRanges();
                sel.addRange(range);
            }
            document.execCommand(command, false, url);
            this.sync();
        },
        uploading: false,
        uploadError: '',
        /** Sisipkan foto dari perangkat: diperkecil di browser, diunggah, lalu ditaruh di posisi kursor. */
        async uploadImage(e) {
            const input = e.target;
            const file = input.files?.[0];
            input.value = '';
            if (!file) return;
            const range = window.getSelection()?.rangeCount ? window.getSelection().getRangeAt(0) : null;
            this.uploading = true;
            this.uploadError = '';
            try {
                const fd = new FormData();
                fd.append('image', await window.wlShrinkImage(file));
                const { data } = await window.axios.post(cfg.imageUrl, fd);
                this.$refs.editor.focus();
                if (range && this.$refs.editor.contains(range.commonAncestorContainer)) {
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
                document.execCommand('insertImage', false, data.url);
                this.sync();
            } catch (err) {
                this.uploadError = err.response?.data?.message || 'Foto belum terunggah. Coba lagi, ya.';
            } finally {
                this.uploading = false;
            }
        },
        minutes() { const words = (this.$refs.editor?.innerText || '').trim().split(/\s+/).filter(Boolean).length; return Math.max(1, Math.ceil(words / 200)); },
        slugify(s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); },
    }));
}
