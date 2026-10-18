{{-- Tombol mikrofon + bottom sheet perekaman voice note tersamar. Bisa juga dibuka dari menu lampiran lewat event `voice-start`. --}}
<div x-data="voiceRecorder(@js($uploadUrl))" x-on:voice-start.window="start()" class="flex-none {{ $wrapperClass ?? '' }}">
    <button type="button" x-on:click="start()" aria-label="Rekam pesan suara" class="{{ $buttonClass ?? '' }} w-12 h-12 rounded-full border-[1.5px] border-line bg-white flex items-center justify-center text-ink hover:border-terracotta"><x-icon name="mic" size="20" /></button>

    <div x-show="open" x-cloak class="scrim fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-6">
        <section role="dialog" aria-modal="true" aria-labelledby="vn-h" x-trap.noscroll="open" class="relative w-full max-w-[480px] bg-cream rounded-t-[32px] sm:rounded-panel px-6 pt-3 pb-8 sm:p-8 flex flex-col gap-4 shadow-modal animate-pop">
            <div class="sm:hidden mx-auto w-11 h-[5px] rounded-full bg-toggle-off"></div>
            <button type="button" x-on:click="reset()" aria-label="Tutup" class="absolute right-4 top-4 w-11 h-11 rounded-full bg-white flex items-center justify-center"><x-icon name="x" size="20" /></button>
            <h2 id="vn-h" class="text-2xl pr-12">Pesan suara</h2>

            <p x-show="error" x-text="error" class="p-3.5 rounded-2xl bg-terracotta-tint text-terracotta-ink text-sm font-bold leading-relaxed"></p>

            <div class="flex flex-col gap-2">
                <span class="text-[13px] font-extrabold text-ink-soft">Samarkan suara</span>
                <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Mode samaran">
                    <template x-for="m in modes" :key="m.id">
                        <button type="button" role="radio" :aria-checked="(mode === m.id).toString()" x-on:click="changeMode(m.id)" :disabled="state === 'recording' || state === 'processing' || state === 'uploading'" class="chip !min-h-11" :class="mode === m.id ? 'chip-on' : ''" x-text="m.label"></button>
                    </template>
                </div>
                <p x-show="mode === 'none'" class="text-[13px] font-bold text-terracotta-ink">Suara aslimu akan terdengar oleh guru.</p>
                <p x-show="mode !== 'none'" class="text-[13px] text-ink-soft">Suaramu disamarkan. Hindari menyebut nama.</p>
            </div>

            <div class="bg-white rounded-card p-5 flex flex-col items-center gap-3">
                <template x-if="state === 'recording'">
                    <div class="flex flex-col items-center gap-3">
                        <span class="relative flex w-16 h-16 items-center justify-center"><span class="absolute inset-0 rounded-full bg-terracotta-tint animate-ping"></span><span class="relative w-12 h-12 rounded-full bg-terracotta-deep text-white flex items-center justify-center"><x-icon name="mic" size="22" /></span></span>
                        <span class="font-display text-3xl tabular-nums" x-text="timeLabel(seconds)"></span>
                        <span class="text-xs text-ink-soft">Maksimal 2 menit · berhenti otomatis</span>
                        <button type="button" x-on:click="stop()" class="btn btn-primary btn-sm"><x-icon name="stop" size="18" />Selesai merekam</button>
                    </div>
                </template>
                <template x-if="state === 'processing'">
                    <x-skeleton :rows="0" label="Sedang menyamarkan suaramu…" />
                </template>
                <template x-if="state === 'preview' || state === 'uploading'">
                    <div class="w-full flex flex-col gap-3">
                        <span class="text-sm font-extrabold">Dengarkan dulu sebelum dikirim (<span x-text="timeLabel(duration)"></span>)</span>
                        <audio :src="previewUrl" controls class="w-full"></audio>
                        <div class="flex gap-2 flex-wrap">
                            <button type="button" x-on:click="upload()" :disabled="state === 'uploading'" class="flex-1 btn btn-primary btn-sm"><x-icon name="send" size="16" /><span x-text="state === 'uploading' ? 'Mengirim…' : 'Kirim'"></span></button>
                            <button type="button" x-on:click="rerecord()" :disabled="state === 'uploading'" class="btn btn-outline btn-sm">Rekam Ulang</button>
                            <button type="button" x-on:click="reset()" :disabled="state === 'uploading'" class="btn btn-ghost btn-sm">Batal</button>
                        </div>
                    </div>
                </template>
                <template x-if="state === 'idle' && !error">
                    <span class="text-sm text-ink-soft">Menyiapkan mikrofon…</span>
                </template>
                <template x-if="state === 'idle' && error">
                    <button type="button" x-on:click="start()" class="btn btn-outline btn-sm">Coba lagi</button>
                </template>
            </div>
        </section>
    </div>
</div>
