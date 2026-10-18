{{-- Daftar percakapan murid (mobile: halaman; desktop: kolom kiri). --}}
@php $tz = \App\Models\School::current()->timezone; @endphp
<div class="flex flex-col gap-2">
    @foreach($conversations as $item)
        @php $on = isset($c) && $c->id === $item->id; $st = $availability->statusFor($item->counselor); $last = $item->latestMessage; @endphp
        <a href="{{ route('student.conversations.show', $item) }}" @if($on) aria-current="page" @endif class="no-underline text-ink p-3 rounded-[18px] flex gap-3 border transition {{ $on ? 'bg-white border-sage-deep ring-2 ring-sage/30 shadow-soft' : 'bg-white/75 border-white shadow-xs hover:bg-white hover:border-sage/50 hover:-translate-y-px' }}">
            <x-counselor-avatar :counselor="$item->counselor" :size="44" :radius="15" :online="$st['online']" class="" />
            <span class="flex-1 min-w-0 flex flex-col gap-1">
                <span class="flex justify-between gap-1.5"><span class="font-extrabold text-[15px] truncate">{{ $item->counselor->displayName() }}</span><span class="text-xs flex-none {{ $item->unread_count ? 'font-extrabold text-terracotta-ink' : 'text-ink-soft' }}">{{ $item->last_message_at?->timezone($tz)->isToday() ? $item->last_message_at->timezone($tz)->format('H.i') : $item->last_message_at?->timezone($tz)->translatedFormat('j M') }}</span></span>
                <span class="text-[13px] truncate {{ $item->unread_count ? 'font-bold text-ink' : 'text-ink-soft' }}"><x-message-preview :prefix="$last?->sender_type === 'student' ? 'Kamu: ' : ''" :icon="$last?->previewIcon()" :text="$last?->preview(60)" /></span>
                <span class="flex gap-1 flex-wrap items-center">
                    @foreach($item->topics->take(2) as $t)<span class="text-[11px] font-extrabold px-2 py-0.5 rounded-full bg-cream-light whitespace-nowrap">{{ $t->name }}</span>@endforeach
                    <span class="text-[11px] font-extrabold {{ $item->status === 'resolved' ? 'text-sage-ink2' : 'text-ink-soft' }}">· {{ $item->studentStatusLabel() }}</span>
                    @if($item->unread_count)<span class="ml-auto min-w-5 h-5 px-1.5 rounded-full bg-terracotta-deep text-white text-[11px] font-extrabold flex items-center justify-center leading-none pt-px" aria-label="{{ $item->unread_count }} pesan belum dibaca">{{ $item->unread_count }}</span>@endif
                </span>
            </span>
        </a>
    @endforeach
</div>
