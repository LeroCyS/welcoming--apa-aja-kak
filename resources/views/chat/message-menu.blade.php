{{-- Pilihan pesan (edit / hapus), seperti WhatsApp. Ada di dalam chatRoom (murid & guru). --}}
<div x-show="msgMenu" x-cloak class="scrim fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-6" x-transition.opacity x-on:keydown.escape.window="closeMessageMenu()">
    <section role="dialog" aria-modal="true" aria-labelledby="msg-menu-h" x-on:click.outside="closeMessageMenu()" x-trap.noscroll="msgMenu" class="w-full sm:max-w-[400px] bg-cream rounded-t-[28px] sm:rounded-[28px] shadow-modal p-5 sm:p-6 flex flex-col gap-3 animate-pop">
        {{-- Pilihan untuk satu pesan --}}
        <template x-if="msgMenu && !editing">
            <div class="flex flex-col gap-2">
                <h2 id="msg-menu-h" class="text-xl mb-1">Pilihan pesan</h2>
                <button type="button" x-show="msgMenu.canEdit" x-on:click="startEdit()" class="h-12 px-4 rounded-2xl bg-white border border-line-soft flex items-center gap-3 text-[15px] font-bold text-ink text-left transition hover:bg-cream-light"><x-icon name="edit" size="18" />Edit pesan</button>
                <button type="button" x-on:click="deleteMessage('me')" :disabled="menuBusy" class="h-12 px-4 rounded-2xl bg-white border border-line-soft flex items-center gap-3 text-[15px] font-bold text-ink text-left transition hover:bg-cream-light"><x-icon name="trash" size="18" />Hapus untuk saya</button>
                <button type="button" x-show="msgMenu.canRetract" x-on:click="deleteMessage('all')" :disabled="menuBusy" class="h-12 px-4 rounded-2xl bg-white border border-danger-line flex items-center gap-3 text-[15px] font-bold text-danger-ink text-left transition hover:bg-danger-tint"><x-icon name="trash" size="18" />Hapus untuk semua orang</button>
                <p x-show="msgMenu.own && !msgMenu.canRetract" class="px-1 text-xs text-ink-soft">Hapus untuk semua orang hanya bisa dalam {{ \App\Models\Message::RETRACT_LABEL }}, edit dalam {{ \App\Models\Message::EDIT_MINUTES }} menit setelah dikirim.</p>
            </div>
        </template>
        {{-- Edit --}}
        <template x-if="msgMenu && editing">
            <form x-on:submit.prevent="saveEdit()" class="flex flex-col gap-3">
                <h2 class="text-xl">Edit pesan</h2>
                <label for="msg-edit" class="sr-only">Teks pesan</label>
                <textarea id="msg-edit" x-ref="editInput" x-model="editText" rows="4" maxlength="2000" class="field !rounded-[18px]"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" x-on:click="editing = false" class="btn btn-outline btn-sm">Batal</button>
                    <button class="btn btn-primary btn-sm" :disabled="menuBusy || !editText.trim()">Simpan</button>
                </div>
            </form>
        </template>
        <p x-show="menuError" x-text="menuError" class="field-error !mt-0"></p>
        <button type="button" x-show="!editing" x-on:click="closeMessageMenu()" class="btn btn-ghost btn-sm self-center !text-sm text-ink-soft">Batal</button>
    </section>
</div>
