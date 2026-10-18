@props(['tagline' => null, 'taglineClass' => 'text-terracotta-ink', 'href' => '/', 'size' => 52])
{{-- Logo berbentuk huruf W, jadi teksnya cukup "elcoming" yang menempel sejajar ke logo (sama seperti halaman publik). Tagline di bawahnya, rata dengan huruf "e". --}}
<a href="{{ $href }}" aria-label="Welcoming" {{ $attributes->merge(['class' => 'flex flex-col px-2 no-underline text-ink']) }}>
    <span class="flex items-center">
        <img src="{{ asset('images/welcoming-mark.png') }}" alt="" style="width: {{ $size }}px" class="h-auto block">
        <span class="hidden sm:inline font-display text-xl leading-none -ml-0.5" aria-hidden="true">elcoming</span>
    </span>
    @if($tagline)<span class="hidden sm:block mt-1 text-[10px] leading-none font-extrabold tracking-[0.1em] {{ $taglineClass }}" style="padding-left: {{ $size - 2 }}px">{{ $tagline }}</span>@endif
</a>
