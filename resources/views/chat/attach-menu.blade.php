{{-- Tombol "+" lampiran di kolom chat (HP): membuka menu ke atas, seperti lampiran di WhatsApp/ChatGPT.
     $items: list<array{icon: string, label: string, hint: string, click: string, tone?: string, disabled?: bool}> --}}
<div class="relative flex-none" x-data="{ attach: false }" x-on:keydown.escape="attach = false" x-on:click.outside="attach = false">
    <button type="button" x-on:click="attach = !attach" :aria-expanded="attach" aria-haspopup="menu" aria-label="Lampiran" title="Lampiran"
            class="w-9 h-9 rounded-full flex items-center justify-center transition" :class="attach ? 'bg-ink text-cream' : 'bg-cream-light text-ink hover:bg-cream'">
        <x-icon name="plus" size="20" class="transition-transform duration-200" ::class="attach && 'rotate-45'" />
    </button>
    <div x-show="attach" x-cloak role="menu" aria-label="Lampiran"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-1"
         class="absolute left-0 bottom-[calc(100%+12px)] z-40 w-[min(260px,calc(100vw-40px))] origin-bottom-left bg-white border border-line rounded-[20px] p-1.5 shadow-modal flex flex-col">
        @foreach($items as $item)
            <button type="button" role="menuitem" x-on:click="attach = false; {{ $item['click'] }}" @disabled($item['disabled'] ?? false)
                    class="flex items-center gap-3 px-2.5 py-2 rounded-[14px] text-left hover:bg-cream-light disabled:opacity-50 disabled:hover:bg-transparent">
                <span class="w-9 h-9 flex-none rounded-xl flex items-center justify-center {{ $item['tone'] ?? 'bg-cream-light text-ink' }}"><x-icon :name="$item['icon']" size="18" /></span>
                <span class="min-w-0 flex flex-col">
                    <span class="text-sm font-extrabold text-ink">{{ $item['label'] }}</span>
                    <span class="text-xs text-ink-soft leading-snug">{{ $item['hint'] }}</span>
                </span>
            </button>
        @endforeach
    </div>
</div>
