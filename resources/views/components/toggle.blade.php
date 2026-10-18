@props(['name', 'checked' => false, 'label' => '', 'description' => null, 'tone' => 'sage'])
@php $id = 'tg-'.$name; @endphp
<label for="{{ $id }}" {{ $attributes->merge(['class' => 'flex items-center justify-between gap-4 py-3 cursor-pointer']) }} x-data="{ on: {{ $checked ? 'true' : 'false' }} }">
    <span class="flex flex-col gap-0.5">
        <span class="font-extrabold text-[15px]">{{ $label }}</span>
        @if($description)<span class="text-[13px] leading-snug text-ink-soft">{{ $description }}</span>@endif
    </span>
    <input type="hidden" name="{{ $name }}" value="0">
    <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="1" class="sr-only peer" x-model="on" @checked($checked)>
    <span class="relative flex-none w-[52px] h-[30px] rounded-full transition peer-focus-visible:outline-3 peer-focus-visible:outline-terracotta-deep peer-focus-visible:outline-offset-2" :class="on ? '{{ $tone === 'sage' ? 'bg-sage-deep' : 'bg-terracotta-deep' }}' : 'bg-toggle-off'">
        <span class="absolute top-[3px] w-6 h-6 rounded-full bg-white shadow-[0_1px_3px_rgba(62,53,48,.25)] transition-all duration-200" :class="on ? 'left-[25px]' : 'left-[3px]'"></span>
    </span>
</label>
