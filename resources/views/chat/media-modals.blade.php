{{-- Dialog kirim foto dan lightbox foto. Diletakkan DI DALAM elemen chatRoom. --}}
<input type="file" x-ref="photoInput" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" x-on:change="photoChosen($event)">

<x-modal name="chat-photo" title="Kirim foto" max-width="max-w-md">
    <div class="flex flex-col gap-3.5">
        <template x-if="photoUrl"><img :src="photoUrl" alt="Pratinjau foto" class="w-full max-h-[50vh] object-contain rounded-2xl bg-white"></template>
        <label for="photo-caption" class="sr-only">Keterangan foto</label>
        <textarea id="photo-caption" x-model="caption" rows="2" maxlength="1000" placeholder="Tambahkan keterangan (opsional)…" class="field text-[15px]"></textarea>
        <p class="text-xs text-ink-soft flex gap-1.5"><x-icon name="lock" size="13" class="mt-px flex-none" />Lokasi dan data kamera di foto dihapus otomatis sebelum disimpan.</p>
        <p x-show="mediaError" x-text="mediaError" class="field-error !mt-0"></p>
        <div class="flex gap-2.5">
            <button type="button" x-on:click="cancelPhoto()" class="flex-1 btn btn-outline">Batal</button>
            <button type="button" x-on:click="sendPhoto()" :disabled="uploading" class="flex-1 btn {{ $role === 'student' ? 'btn-primary' : 'btn-sage' }}"><span x-text="uploading ? 'Mengirim…' : 'Kirim foto'">Kirim foto</span></button>
        </div>
    </div>
</x-modal>


{{-- Lightbox foto --}}
<div x-data="{ src: null }" x-on:open-photo.window="src = $event.detail" x-show="src" x-cloak x-on:keydown.escape.window="src = null" x-on:click="src = null" class="fixed inset-0 z-[80] bg-ink/90 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Foto">
    <img :src="src" alt="Foto ukuran penuh" class="max-w-full max-h-full object-contain rounded-xl">
    <button type="button" class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/90 flex items-center justify-center text-ink" aria-label="Tutup"><x-icon name="x" size="20" /></button>
</div>
