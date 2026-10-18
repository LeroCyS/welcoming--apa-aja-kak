@extends('layouts.student', ['active' => 'cerita', 'hideBottomNav' => true, 'fullScreen' => true])
@section('title', 'Cerita dengan '.$c->counselor->displayName())
@php
    $anon = $c->isAnonymousToCurrentCounselor();
    $nick = $c->counselor->displayName();
    $profile = $student->studentProfile;
@endphp
@section('header')
<h1 class="sr-only">Cerita</h1>
@endsection

@section('content')
<div class="flex-1 min-h-0 flex bg-white border border-line-soft rounded-[28px] shadow-soft overflow-hidden"
     data-module="chat" x-data="chatRoom({{ Js::from([
        'messagesUrl' => route('student.conversations.messages.index', $c),
        'sendUrl' => route('student.conversations.messages.store', $c),
        'channel' => 'conversation.'.$c->uuid,
        'lastId' => $lastId,
        'version' => $version,
        'draftKey' => 'wl-draft-'.$c->uuid,
        'role' => 'student',
        'typingUrl' => $readOnly ? null : route('student.conversations.typing', $c),
        'imageUrl' => route('student.conversations.image.store', $c),
        'imageMaxKb' => config('welcoming.chat_image_max_kb'),
        'messageUrl' => route('student.conversations.messages.update', [$c, '__ID__']),
     ]) }})">
    {{-- Kolom daftar cerita (desktop) --}}
    <aside aria-label="Daftar percakapan" class="hidden lg:flex w-[300px] xl:w-[340px] flex-none flex-col bg-sage-tint border-r border-sage/30">
        <div class="h-[72px] flex-none px-4 flex items-center gap-2 border-b border-sage/30">
            <span class="flex-1 font-display text-[22px] leading-none pl-1">Cerita</span>
            <a href="{{ route('student.conversations.create') }}" class="inline-flex items-center gap-1.5 h-10 px-4 rounded-full bg-terracotta-deep text-white no-underline text-sm font-extrabold transition hover:bg-terracotta-ink"><x-icon name="plus" size="16" />Baru</a>
        </div>
        <div class="flex-1 overflow-y-auto p-2.5">
            <div x-data="storyList(@js(route('student.conversations.feed', ['current' => $c->id])))">@include('student.conversations._list')</div>
        </div>
        @if($profile?->pin_enabled)<span class="flex-none flex items-center gap-1.5 px-5 py-3 border-t border-line-soft text-xs font-bold text-ink-soft"><x-icon name="lock" size="13" />Riwayat terkunci PIN</span>@endif
    </aside>

    {{-- Ruang chat --}}
    <section aria-label="Ruang chat dengan {{ $nick }}" class="relative flex-1 min-w-0 flex flex-col bg-white">
        <header class="flex-none z-20 bg-white border-b border-line-soft" x-data="{ menu: false }">
            <div class="h-[72px] px-3 sm:px-5 flex items-center gap-2.5 sm:gap-3">
                <a href="{{ route('student.conversations.index') }}" aria-label="Kembali ke daftar cerita" class="lg:hidden w-10 h-10 -ml-1 flex-none flex items-center justify-center rounded-full text-ink hover:bg-cream-light"><x-icon name="arrow-left" size="22" /></a>
                <x-counselor-avatar :counselor="$c->counselor" :size="44" :online="$status['online']" />
                <span class="flex-1 min-w-0 flex flex-col gap-0.5">
                    <span class="font-extrabold text-[15px] sm:text-base truncate">{{ $c->counselor->name !== $nick ? $nick.' · '.$c->counselor->name : $nick }}</span>
                    <span class="text-xs sm:text-[13px] text-ink-soft font-bold truncate"><span x-show="typing" x-cloak class="text-sage-ink">sedang mengetik…</span><span x-show="!typing" class="inline-flex items-center gap-1.5"><span x-text="$store.presence.is({{ $c->counselor_id }}, {{ $status['online'] ? 'true' : 'false' }}) ? 'Online' : 'Offline'" :class="$store.presence.is({{ $c->counselor_id }}, {{ $status['online'] ? 'true' : 'false' }}) && 'text-sage-ink'">{{ $status['online'] ? 'Online' : 'Offline' }}</span><span class="hidden sm:inline">· layanan {{ $hours }}</span></span></span>
                </span>
                <div class="relative flex-none">
                    <button type="button" aria-label="Menu percakapan" :aria-expanded="menu" x-on:click="menu = !menu" class="head-btn" :class="menu && '!bg-cream-light'"><x-icon name="more" size="20" /></button>
                    <div x-show="menu" x-cloak x-transition.origin.top.right x-on:click.outside="menu = false" role="menu" class="absolute right-0 top-[calc(100%+8px)] z-30 w-[240px] bg-white border border-line rounded-[20px] p-2 shadow-modal flex flex-col">
                        @if($anon)<button type="button" role="menuitem" x-on:click="menu = false; $dispatch('open-reveal', {})" class="h-11 px-3 rounded-[14px] bg-sage-tint text-sage-ink text-[15px] font-extrabold text-left">Buka Identitas</button>@endif
                        <a role="menuitem" href="{{ route('student.counselors') }}" class="h-11 px-3 flex items-center rounded-[14px] text-ink no-underline text-[15px] font-bold hover:bg-cream-light">Lihat profil {{ $nick }}</a>
                        <a role="menuitem" href="{{ route('student.profile') }}#pengaturan" class="h-11 px-3 flex items-center rounded-[14px] text-ink no-underline text-[15px] font-bold hover:bg-cream-light">Kunci chat dengan PIN</a>
                        <a role="menuitem" href="{{ route('emergency') }}" class="h-11 px-3 flex items-center rounded-[14px] text-danger-ink no-underline text-[15px] font-bold hover:bg-danger-tint">Bantuan Darurat</a>
                    </div>
                </div>
            </div>
            <div class="px-3 sm:px-5 pb-2.5 -mt-1 flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] text-xs">
                <span class="flex-none inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full font-extrabold whitespace-nowrap {{ $anon ? 'bg-sage-tint text-sage-ink' : 'bg-sand-tint text-sand-ink' }}"><x-icon :name="$anon ? 'lock' : 'unlock'" size="13" />{{ $anon ? 'Anonim sebagai '.$c->anonymous_alias : 'Identitasmu terlihat' }}</span>
                @foreach($c->topics as $t)<span class="flex-none h-7 px-2.5 inline-flex items-center rounded-full bg-cream font-extrabold whitespace-nowrap">{{ $t->name }}</span>@endforeach
                <span class="flex-none font-extrabold whitespace-nowrap {{ $c->status === 'resolved' ? 'text-sage-ink2' : 'text-ink-soft' }}" x-text="statusLabel ?? @js($c->studentStatusLabel())">{{ $c->studentStatusLabel() }}</span>
            </div>
        </header>

        {{-- Pesan baru disisipkan JS langsung ke elemen ini, jadi isinya dibuat di tengah lewat padding (bukan pembungkus). --}}
        <div x-ref="scroller" id="chat-messages" class="chat-canvas flex-1 overflow-y-auto flex flex-col gap-3 py-6 px-4 sm:px-[max(24px,calc((100%-860px)/2))]" aria-live="polite" aria-label="Pesan">
            @foreach($messages as $msg){!! $msg['html'] !!}@endforeach
        </div>
        @include('chat.typing', ['role' => 'student', 'name' => $nick, 'pad' => 'chat-canvas px-4 sm:px-[max(24px,calc((100%-860px)/2))]'])
        @unless($readOnly)@include('chat.media-modals', ['role' => 'student'])@endunless
        @include('chat.message-menu')

        @if($readOnly)
            <div class="flex-none bg-cream-light border-t border-line-soft px-4 sm:px-6 py-4 pb-[max(16px,env(safe-area-inset-bottom))] text-sm font-bold text-ink-soft text-center">Cerita ini dari sekolahmu sebelumnya. Kamu masih bisa membacanya, tapi tidak bisa membalas lagi. <a href="{{ route('student.conversations.create') }}" class="font-extrabold text-terracotta-ink">Mulai cerita baru</a></div>
        @else
            <div class="flex-none bg-gradient-to-t from-white via-white to-white/0 px-3 sm:px-6 pt-2 pb-[max(12px,env(safe-area-inset-bottom))]">
                <div class="max-w-[860px] mx-auto flex flex-col gap-2">
                    @if($c->status === 'resolved')
                        <p class="self-center text-xs font-bold text-ink-soft px-3 py-1 rounded-full bg-cream-light">Cerita ini selesai. Kirim pesan untuk membukanya lagi.</p>
                    @endif
                    <p x-show="error" x-text="error" x-cloak class="field-error px-2"></p>
                    {{-- HP: foto & pesan suara digabung di tombol "+" (menu ke atas), tombol kirim ringkas. Desktop: tombol terpisah seperti biasa. --}}
                    <form x-on:submit.prevent="send()" class="flex gap-1.5 sm:gap-2 items-end p-1.5 max-lg:pl-2 rounded-[28px] max-lg:rounded-[24px] bg-white border-[1.5px] border-line shadow-soft transition focus-within:border-ink-soft/60 focus-within:shadow-lift">
                        <div class="lg:hidden self-end mb-[3px]">
                            @include('chat.attach-menu', ['items' => [
                                ['icon' => 'image', 'label' => 'Foto', 'hint' => 'Kirim gambar dari galeri', 'click' => 'pickPhoto()', 'tone' => 'bg-sage-tint text-sage-ink'],
                                ['icon' => 'mic', 'label' => 'Pesan suara', 'hint' => 'Rekam, suaramu bisa disamarkan', 'click' => "\$dispatch('voice-start')", 'tone' => 'bg-terracotta-tint text-terracotta-ink'],
                            ]])
                        </div>
                        <button type="button" x-on:click="pickPhoto()" aria-label="Kirim foto" title="Kirim foto" class="max-lg:hidden w-11 h-11 flex-none rounded-full flex items-center justify-center text-ink-soft hover:bg-cream-light hover:text-ink transition"><x-icon name="image" size="20" /></button>
                        @include('chat.voice-recorder', ['uploadUrl' => route('student.conversations.voice.store', $c), 'wrapperClass' => 'max-lg:contents', 'buttonClass' => 'max-lg:hidden'])
                        <label for="chat-input" class="sr-only">Tulis pesan</label>
                        <textarea id="chat-input" x-ref="input" x-model="body" x-on:input="saveDraft(); autosize(); notifyTyping()" x-on:keydown.enter="if (!$event.shiftKey && window.innerWidth >= 1024) { $event.preventDefault(); send(); }" rows="1" maxlength="2000" placeholder="Tulis pelan-pelan saja…" @unless($hasStarted) autofocus @endunless class="flex-1 min-w-0 min-h-11 max-lg:min-h-[42px] max-h-40 py-2.5 px-2 max-lg:px-1 bg-transparent text-[15px] leading-snug text-ink resize-none focus:outline-none placeholder:text-ink-soft/70"></textarea>
                        <button type="submit" :disabled="sending || !body.trim()" aria-label="Kirim" class="h-11 w-11 sm:w-auto sm:px-5 max-lg:!w-9 max-lg:!h-9 max-lg:!px-0 max-lg:mb-[3px] flex-none rounded-full bg-terracotta-deep text-white inline-flex items-center justify-center gap-2 font-extrabold text-[15px] transition hover:bg-terracotta-ink disabled:opacity-40"><x-icon name="send" size="18" class="max-lg:w-4 max-lg:h-4" /><span class="hidden lg:inline">Kirim</span></button>
                    </form>
                </div>
            </div>
        @endif
    </section>
</div>
@endsection

@push('modals')
{{-- Dialog buka identitas (tidak bisa dibatalkan) --}}
<div x-data="{ open: false, requestId: null }" x-on:open-reveal.window="open = true; requestId = $event.detail?.requestId ?? null" x-show="open" x-cloak class="scrim fixed inset-0 z-50 flex items-end sm:items-start justify-center sm:pt-[130px] sm:px-6">
    <section role="dialog" aria-modal="true" aria-labelledby="reveal-h" x-trap.noscroll="open" x-on:keydown.escape.window="open = false" class="w-full max-w-[480px] bg-cream rounded-t-[28px] sm:rounded-panel p-6 sm:p-7 flex flex-col gap-3.5 shadow-modal animate-pop">
        <div class="w-[60px] h-[60px] rounded-[20px] bg-sage-tint flex items-center justify-center text-sage-ink2 self-center"><x-icon name="user-check" size="30" /></div>
        <h2 id="reveal-h" class="text-[24px] sm:text-[26px] text-center leading-tight">Buka identitas ke {{ $nick }}?</h2>
        <p class="text-sm text-center text-ink-soft -mt-1">{{ $nick }} akan melihat nama dan kelasmu. Ini tidak bisa dibatalkan.</p>
        @if($student->hasFilledIdentity())
        <div class="grid grid-cols-2 gap-2.5">
            <div class="bg-white rounded-[18px] p-3.5 flex flex-col gap-1.5"><span class="text-xs font-extrabold tracking-[0.06em] uppercase text-sage-ink2">Akan terlihat</span><span class="text-sm font-extrabold">{{ $student->name }}</span><span class="text-sm font-extrabold">{{ $profile?->class_name }}</span></div>
            <div class="bg-white rounded-[18px] p-3.5 flex flex-col gap-1.5"><span class="text-xs font-extrabold tracking-[0.06em] uppercase text-ink-soft">Tetap tersembunyi</span><span class="text-[13px] leading-snug text-[#5A4E47]">Jurnal mood, chat dengan guru lain, email, dan akun Google-mu.</span></div>
        </div>
        @else
        <div class="bg-white rounded-[18px] p-4 text-sm leading-relaxed text-[#5A4E47]">Kamu belum mengisi nama di Profil, jadi belum ada identitas yang bisa dibuka. Email dan akun Google-mu tidak akan pernah dibagikan. <a href="{{ route('student.profile') }}#identitas" class="font-extrabold text-terracotta-deep">Isi profil dulu</a></div>
        @endif
        <p class="text-sm leading-relaxed text-ink-soft text-center">Hanya berlaku di percakapan ini. Belum siap juga nggak apa-apa.</p>
        <form method="POST" :action="requestId ? '{{ url('murid/permintaan-identitas') }}/' + requestId : '{{ route('student.conversations.reveal', $c) }}'" class="flex gap-2.5">
            @csrf
            <input type="hidden" name="confirm" value="1">
            <input type="hidden" name="response" value="accepted">
            <button type="button" x-on:click="open = false" class="flex-1 btn btn-outline !h-[50px] !text-[15px]">Tetap anonim dulu</button>
            @if($student->hasFilledIdentity())<button class="flex-1 btn btn-sage !h-[50px] !text-[15px]">Ya, buka identitasku</button>@endif
        </form>
    </section>
</div>
@endpush
