@extends($layout, ['active' => ''])
@section('title', 'Notifikasi')
@section('content')
<div class="flex justify-between items-center gap-3 flex-wrap">
    <div role="tablist" aria-label="Tampilkan notifikasi" class="seg">
        <a role="tab" href="{{ route('notifications.index') }}" aria-selected="{{ $filter === 'semua' ? 'true' : 'false' }}" class="seg-item no-underline {{ $filter === 'semua' ? 'seg-item-on' : '' }}">Semua</a>
        <a role="tab" href="{{ route('notifications.index', ['tampil' => 'belum']) }}" aria-selected="{{ $filter === 'belum' ? 'true' : 'false' }}" class="seg-item no-underline {{ $filter === 'belum' ? 'seg-item-on' : '' }}">Belum dibaca @if($unread)<span class="ml-1 px-1.5 rounded-full bg-terracotta-deep text-white text-xs whitespace-nowrap">{{ $unread }}</span>@endif</a>
    </div>
    <div class="flex gap-2 flex-wrap items-center" x-data="{ state: window.wlPush?.state() ?? 'unsupported' }">
        <button type="button" x-show="state === 'default'" x-cloak x-on:click="window.wlPush.enable().then((s) => state = s)" class="btn btn-outline btn-sm"><x-icon name="bell" size="15" />Aktifkan notifikasi perangkat</button>
        <span x-show="state === 'granted'" x-cloak class="text-xs font-bold text-sage-ink inline-flex items-center gap-1"><x-icon name="check" size="14" />Notifikasi perangkat aktif</span>
        @if($unread)
            <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-soft btn-sm">Tandai semua dibaca</button></form>
        @endif
    </div>
</div>
@forelse($groups as $day => $items)
    <section class="flex flex-col gap-2" aria-label="{{ $day }}">
        <h2 class="!font-sans text-xs font-extrabold tracking-[0.08em] uppercase text-ink-soft px-1">{{ $day }}</h2>
        <div class="card !rounded-card-l overflow-hidden">
            @foreach($items as $n)
                @php
                    $kind = \App\Support\NotificationKind::for($n->data['kind'] ?? $n->type);
                    $critical = (bool) ($n->data['critical'] ?? false);
                    $tone = $critical ? 'danger' : $kind['tone'];
                @endphp
                <a href="{{ route('notifications.open', $n->id) }}" class="flex gap-3 sm:gap-3.5 items-start px-4 sm:px-5 py-3.5 sm:py-4 border-b border-line-soft last:border-b-0 no-underline text-ink hover:bg-cream-light {{ $n->read_at ? '' : 'bg-cream-light' }}">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 flex-none rounded-xl sm:rounded-[14px] flex items-center justify-center bg-{{ $tone }}-tint text-{{ $tone }}-ink"><x-icon :name="$kind['icon']" size="18" /></span>
                    <span class="flex-1 min-w-0 flex flex-col gap-0.5">
                        {{-- Satu baris: label jenis + jam (sudah dikelompokkan per hari). Waktu relatif hanya di layar lebar. --}}
                        <span class="flex items-center gap-1.5 min-w-0 text-[11px] sm:text-xs whitespace-nowrap"><span class="font-extrabold uppercase tracking-[0.06em] truncate text-{{ $tone }}-ink">{{ $critical ? 'Penting' : $kind['label'] }}</span><span class="flex-none text-ink-soft">· {{ $n->created_at->timezone(\App\Models\School::current()->timezone)->format('H.i') }}<span class="max-sm:hidden"> · {{ $n->created_at->diffForHumans() }}</span></span></span>
                        <span class="text-sm sm:text-[15px] font-bold leading-snug {{ $critical ? 'text-danger-ink' : '' }}">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                        @if(filled($n->data['body'] ?? null))<span class="text-[13px] sm:text-sm text-ink-soft leading-snug line-clamp-2">{{ $n->data['body'] }}</span>@endif
                    </span>
                    @unless($n->read_at)<span class="mt-1.5 w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full flex-none {{ $critical ? 'bg-danger' : 'bg-terracotta-deep' }}" aria-label="Belum dibaca"></span>@endunless
                </a>
            @endforeach
        </div>
    </section>
@empty
    <div class="card !rounded-card-l"><x-empty-state :title="$filter === 'belum' ? 'Semua sudah dibaca' : 'Belum ada notifikasi'" text="Kalau ada kabar baru, kamu akan melihatnya di sini." /></div>
@endforelse
{{ $notifications->links('partials.pagination') }}
@endsection
