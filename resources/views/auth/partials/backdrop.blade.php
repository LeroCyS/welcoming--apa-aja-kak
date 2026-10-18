{{-- Latar halaman masuk/daftar/onboarding: pola titik + dua potongan kertas berilustrasi (layar lebar). Tiap halaman memakai tokoh & stiker yang berbeda lewat $variant: login | register | onboarding. --}}
@php
    $variant = $variant ?? 'login';
    // [warna kertas, kulit, rambut, gaya rambut, baju, stiker, label]
    $sides = [
        'login' => [
            ['bg-terracotta', '#F1CBA8', '#3E3530', 'long', '#FBEDE9', 'tag:lock:ANONIM', 'murid'],
            ['bg-sage', '#C98E6B', '#2E2723', 'short', '#F5F1E4', 'chat', 'Guru BK'],
        ],
        'register' => [
            ['bg-sand', '#E2B08A', '#4A3A30', 'curly', '#E4EADF', 'heart', 'kamu'],
            ['bg-terracotta', '#C98E6B', '#2E2723', 'long', '#F3E6D0', 'tag:sparkle:GRATIS', 'teman baru'],
        ],
        'onboarding' => [
            ['bg-sage', '#F1CBA8', '#6F5229', 'short', '#FBF8EF', 'wink', 'Guru BK-mu'],
            ['bg-sand', '#F1CBA8', '#3E3530', 'curly', '#F6DDD7', 'tag:home:SEKOLAHMU', 'sekolahmu'],
        ],
    ][$variant] ?? [];
@endphp
<div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
    <div class="absolute inset-0 opacity-70 [background-image:radial-gradient(#D9D0BB_1.4px,transparent_1.4px)] [background-size:26px_26px]"></div>

    @foreach($sides as $i => [$paper, $skin, $hair, $hairStyle, $shirt, $sticker, $label])
        @php $left = $i === 0; @endphp
        <div class="hidden lg:block absolute {{ $left ? 'left-[max(2%,calc(50%-640px))] top-[22%]' : 'right-[max(2%,calc(50%-640px))] top-[36%]' }} w-[clamp(170px,15vw,240px)] aspect-[4/5] {{ $left ? 'animate-[slide-in-left_.9s_cubic-bezier(.22,1,.36,1)_.3s_both,paper-float_7s_ease-in-out_1.2s_infinite]' : 'animate-[slide-in-right_.9s_cubic-bezier(.22,1,.36,1)_.45s_both,paper-float_8s_ease-in-out_1.4s_infinite]' }}">
            <div class="relative w-full h-full {{ $paper }} overflow-hidden {{ $left ? '-rotate-[5deg]' : 'rotate-[4deg]' }}" style="clip-path: {{ $left ? 'polygon(6% 0, 100% 4%, 95% 100%, 0 95%)' : 'polygon(0 5%, 94% 0, 100% 96%, 5% 100%)' }}">
                <div class="absolute inset-0 opacity-25" style="background: repeating-conic-gradient(from 0deg at 50% 110%, rgba(255,255,255,.55) 0deg 6deg, transparent 6deg 18deg)"></div>
                <x-doodle-person :skin="$skin" :hair="$hair" :hair-style="$hairStyle" :shirt="$shirt" class="absolute left-1/2 -translate-x-1/2 bottom-0 h-[84%] w-auto animate-[obj-bob_4s_ease-in-out_infinite]" style="animation-delay: {{ $left ? '0s' : '-2s' }}" />
            </div>

            <span class="absolute {{ $left ? '-top-6 -right-8 rotate-[6deg]' : '-top-8 -left-8 -rotate-[6deg]' }} animate-[pop-in_.5s_cubic-bezier(.34,1.6,.64,1)_both]" style="animation-delay: {{ $left ? '1.2s' : '1.35s' }}">
                @if(str_starts_with($sticker, 'tag:'))
                    @php [, $icon, $text] = explode(':', $sticker); @endphp
                    <span class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white border-[2.5px] border-ink text-ink text-sm font-extrabold shadow-[3px_3px_0_#3E3530]"><x-icon :name="$icon" size="14" stroke="2.8" />{{ $text }}</span>
                @elseif($sticker === 'chat')
                    <svg class="w-16 drop-shadow-[3px_3px_0_#3E3530]" viewBox="0 0 48 44" stroke="#3E3530" stroke-width="2.6" stroke-linejoin="round"><path d="M24 3C12 3 3 10.5 3 20c0 5 2.5 9.4 6.6 12.5L7 41l10.5-5.4c2 .5 4.2.8 6.5.8 12 0 21-7.5 21-17S36 3 24 3z" fill="#D4705F"/><g fill="#fff" stroke="none"><circle cx="15" cy="20" r="3"/><circle cx="24" cy="20" r="3"/><circle cx="33" cy="20" r="3"/></g></svg>
                @elseif($sticker === 'heart')
                    <svg class="w-14 drop-shadow-[3px_3px_0_#3E3530]" viewBox="0 0 48 44" stroke="#3E3530" stroke-width="2.6" stroke-linejoin="round"><path d="M24 40S5 29 5 15.5A9.5 9.5 0 0 1 24 10a9.5 9.5 0 0 1 19 5.5C43 29 24 40 24 40z" fill="#8FA382"/></svg>
                @elseif($sticker === 'wink')
                    <svg class="w-14 drop-shadow-[3px_3px_0_#3E3530]" viewBox="0 0 48 48" fill="none" stroke="#3E3530" stroke-width="2.6" stroke-linecap="round"><circle cx="24" cy="24" r="20" fill="#F3E6D0"/><circle cx="17" cy="20" r="2.4" fill="#3E3530" stroke="none"/><path d="M27 20q3.5-3 7 0"/><path d="M15 29q9 8 18 0"/></svg>
                @endif
            </span>

            <span class="absolute -bottom-8 {{ $left ? 'left-[18%] -rotate-[4deg]' : 'right-[14%] rotate-[3deg]' }} font-hand text-[26px] leading-none text-ink whitespace-nowrap animate-[fade-in_.6s_ease-out_both]" style="animation-delay: {{ $left ? '1.4s' : '1.55s' }}">{{ $label }}</span>
        </div>
    @endforeach
</div>
