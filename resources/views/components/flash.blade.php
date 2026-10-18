@if(session('status') || session('error') || $errors->has('general'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition.opacity.duration.250ms role="status" class="fixed top-4 left-1/2 -translate-x-1/2 z-[60] w-[min(520px,calc(100vw-32px))] flex items-start gap-3 pl-3 pr-4 py-3 rounded-card shadow-modal border animate-pop {{ session('error') || $errors->has('general') ? 'bg-terracotta-tint2 text-terracotta-ink border-terracotta-tint' : 'bg-white text-sage-ink border-sage-tint' }}">
        <span class="w-8 h-8 flex-none rounded-xl flex items-center justify-center {{ session('error') || $errors->has('general') ? 'bg-terracotta-tint' : 'bg-sage-tint' }}"><x-icon :name="session('error') || $errors->has('general') ? 'info' : 'check'" size="18" /></span>
        <span class="flex-1 text-[15px] font-bold leading-snug pt-1">{{ session('status') ?? session('error') ?? $errors->first('general') }}</span>
        <button type="button" x-on:click="show = false" aria-label="Tutup" class="-mr-1 mt-0.5 w-7 h-7 rounded-full flex items-center justify-center opacity-70 hover:opacity-100 hover:bg-ink/5"><x-icon name="x" size="18" /></button>
    </div>
@endif
@auth
    {{-- Toast notifikasi baru (diisi oleh lonceng notifikasi lewat Alpine store "toasts"). --}}
    <div x-data aria-live="polite" class="fixed top-4 right-4 z-[70] w-[min(380px,calc(100vw-32px))] flex flex-col gap-2 pointer-events-none no-print">
        <template x-for="t in $store.toasts.items" :key="t.id">
            <div x-transition.opacity class="pointer-events-auto bg-white rounded-card shadow-modal border border-line flex gap-3 p-3.5 items-start animate-pop" :class="t.critical && '!border-danger-line'">
                <span class="w-10 h-10 flex-none rounded-[14px] flex items-center justify-center" :class="'bg-' + t.tone + '-tint text-' + t.tone + '-ink'" x-html="t.icon"></span>
                <a :href="t.url" class="flex-1 min-w-0 no-underline text-ink flex flex-col gap-0.5">
                    <span class="text-[11px] font-extrabold uppercase tracking-[0.06em]" :class="'text-' + t.tone + '-ink'" x-text="t.critical ? 'Penting' : t.label"></span>
                    <span class="text-sm font-extrabold leading-snug" x-text="t.title"></span>
                    <span x-show="t.body" class="text-[13px] text-ink-soft leading-snug line-clamp-2" x-text="t.body"></span>
                </a>
                <button type="button" x-on:click="$store.toasts.dismiss(t.id)" aria-label="Tutup" class="text-ink-soft"><x-icon name="x" size="16" /></button>
            </div>
        </template>
    </div>
@endauth
