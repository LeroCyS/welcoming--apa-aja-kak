@props(['rows' => 3, 'label' => 'Sebentar ya, lagi disiapkan…'])
<div {{ $attributes->merge(['class' => 'flex flex-col gap-3']) }} role="status" aria-live="polite">
    <div class="flex items-center gap-3">
        <svg width="32" height="32" viewBox="0 0 40 40" fill="none" stroke-width="2.4" stroke-linecap="round" class="animate-spin"><circle cx="20" cy="20" r="15" stroke="#E6DFCC"/><path d="M20 5a15 15 0 0 1 15 15" stroke="#D4705F"/></svg>
        <span class="text-[15px] font-bold">{{ $label }}</span>
    </div>
    @for($i = 0; $i < $rows; $i++)
        <div class="bg-white rounded-card p-3.5 flex gap-3 items-center">
            <span class="w-14 h-14 rounded-[14px] bg-line-soft animate-pulse-soft flex-none"></span>
            <div class="flex-1 flex flex-col gap-2"><span class="skeleton h-2.5 w-2/5"></span><span class="skeleton h-3 w-11/12"></span><span class="skeleton h-3 w-3/4"></span></div>
        </div>
    @endfor
</div>
