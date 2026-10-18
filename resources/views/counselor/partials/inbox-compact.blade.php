@php $dots = collect(\App\Models\Conversation::urgencyStyles())->map(fn ($s) => $s[2])->all(); @endphp
@foreach($items as $it)
    @php $on = ($current ?? null) === $it['uuid']; @endphp
    <a href="{{ $it['url'] }}" @if($on) aria-current="page" @endif class="no-underline text-ink px-2 py-1.5 rounded-xl flex gap-2.5 items-center {{ $on ? 'bg-sage-tint' : 'hover:bg-cream-light' }} {{ $it['status'] === 'resolved' ? 'opacity-60' : '' }}">
        @if($it['identified'])<x-avatar-anon :name="$it['display_name']" :size="30" :radius="10" />@else<x-avatar-anon :avatar="$it['avatar']" :size="30" :radius="10" />@endif
        <span class="flex-1 min-w-0 flex flex-col gap-0.5">
            <span class="flex items-center gap-1.5 text-[13px] {{ $it['unread'] ? 'font-extrabold' : 'font-bold' }}"><span class="w-[7px] h-[7px] rounded-full flex-none {{ $dots[$it['urgency']] }}"></span><span class="truncate">{{ $it['display_name'] }}</span>@if($it['flagged'])<x-icon name="flag" size="12" class="text-danger-ink" />@endif</span>
            <span class="text-[11px] truncate {{ $on ? 'text-sage-ink' : 'text-ink-soft' }}"><x-message-preview :icon="$it['preview_icon'] ?? null" :prefix="($it['preview_mine'] ?? false) ? 'Kamu: ' : ''" :text="($it['preview_mine'] ?? false) ? \Illuminate\Support\Str::after($it['preview'], 'Kamu: ') : $it['preview']" /></span>
        </span>
        @if($it['unread'])<span class="w-2 h-2 rounded-full bg-terracotta-deep flex-none"></span>@endif
    </a>
@endforeach
