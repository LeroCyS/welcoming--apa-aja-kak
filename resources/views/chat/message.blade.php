{{--
    Satu pesan di ruang chat. $role = penonton ('student' | 'counselor').
    Murid: pesan murid di kanan (terracotta), guru di kiri (sage). Guru: sebaliknya.
--}}
@php
    $mine = ($role === 'student' && $m->sender_type === 'student') || ($role === 'counselor' && $m->sender_type === 'counselor');
    $time = $m->created_at->timezone($tz)->format('H.i');
    // Pesan sendiri: warna solid (murid terracotta, guru sage). Pesan lawan bicara: kartu putih bergaris.
    $bubbleBg = $mine
        ? ($m->sender_type === 'student' ? 'bg-terracotta-deep text-white' : 'bg-sage-deep text-white')
        : 'bg-white border border-line-soft text-ink';
    $radius = $mine ? 'rounded-[20px_20px_6px_20px]' : 'rounded-[20px_20px_20px_6px]';
    $align = $mine ? 'self-end items-end' : 'self-start items-start';
    $tints = \App\Services\MoodService::TINTS;
    // Edit / hapus seperti WhatsApp. Pesan murid berkata kunci kritis tetap terbaca oleh Guru BK walau dihapus/diedit.
    $viewer = $ctx['viewer'];
    $critical = $role === 'counselor' && (($ctx['flags'][$m->id] ?? collect())->max('severity') ?? 0) >= \App\Models\Message::CRITICAL_SEVERITY;
    $hideContent = $m->isRetracted() && ! $critical;
    $menu = in_array($m->type, \App\Models\Message::DELETABLE_TYPES, true) ? [
        'id' => $m->id,
        'own' => $mine,
        'canEdit' => $m->canBeEditedBy($viewer),
        'canRetract' => $m->canBeRetractedBy($viewer),
        'body' => $m->canBeEditedBy($viewer) ? $m->body : null,
    ] : null;
@endphp
<div class="contents" data-message-id="{{ $m->id }}">
@if($separator)
    <div class="self-center text-xs font-bold text-ink-soft px-3 py-1 rounded-full bg-cream-deep my-1">{{ $separator }}</div>
@endif

@if($hideContent)
    <div class="{{ $align }} max-w-[86%] sm:max-w-[70%] flex flex-col gap-1">
        <div class="px-4 py-2.5 {{ $radius }} border-[1.5px] border-dashed border-line bg-white/70 text-sm italic text-ink-soft inline-flex items-center gap-2"><x-icon name="x" size="14" class="flex-none" />{{ $mine ? 'Kamu menghapus pesan ini' : 'Pesan ini dihapus' }}</div>
        @include('chat.receipt')
    </div>
@else
@switch($m->type)
    @case('text')
        @php $flags = $ctx['flags'][$m->id] ?? collect(); @endphp
        <div class="{{ $align }} max-w-[86%] sm:max-w-[70%] flex flex-col gap-1">
            <div class="px-4 py-3 {{ $radius }} {{ $bubbleBg }} text-[15px] leading-[1.55] whitespace-pre-line wrap-anywhere shadow-[0_1px_2px_rgba(62,53,48,.08)]">@if($flags->isNotEmpty() && $role === 'counselor'){!! \App\Support\Highlight::phrases(e($m->body), $flags->pluck('keyword.phrase')->all()) !!}@else{{ $m->body }}@endif</div>
            @if($role === 'counselor' && $critical && filled($m->original_body) && $m->original_body !== $m->body)
                <details class="max-w-full text-xs">
                    <summary class="cursor-pointer font-extrabold text-danger-ink">Lihat teks asli sebelum diedit</summary>
                    <p class="mt-1 px-3 py-2 rounded-xl bg-danger-tint/60 text-[13px] leading-relaxed whitespace-pre-line wrap-anywhere">{!! \App\Support\Highlight::phrases(e($m->original_body), $flags->pluck('keyword.phrase')->all()) !!}</p>
                </details>
            @endif
            @include('chat.receipt')
            @if($role === 'counselor' && $flags->isNotEmpty())
                @php $sev = $flags->max('severity'); @endphp
                <span class="inline-flex items-center gap-1 text-xs font-extrabold {{ $sev >= 3 ? 'text-danger' : 'text-danger-ink' }}"><x-icon name="flag" size="14" />{{ $sev >= 3 ? 'Kata kunci kritis terdeteksi' : 'Kata kunci sensitif terdeteksi' }}</span>
            @endif
        </div>
        @break

    @case('voice')
        @php $vn = $m->voiceNote; @endphp
        <div class="{{ $align }} max-w-[86%] sm:max-w-[70%] flex flex-col gap-1" x-data="{ showTx: false }">
            <div class="px-4 py-3 {{ $radius }} {{ $bubbleBg }} flex flex-col gap-2 min-w-[240px]">
                <span class="inline-flex items-center gap-1.5 text-xs font-extrabold {{ $mine ? 'text-white/85' : ($m->sender_type === 'student' ? 'text-terracotta-ink' : 'text-sage-ink') }}"><x-icon name="mic" size="14" />Pesan suara · {{ gmdate('i:s', $vn?->duration_seconds ?? 0) }}</span>
                @if(! $vn || $vn->audio_deleted_at)
                    <span class="text-sm italic {{ $mine ? 'text-white/80' : 'text-ink-soft' }}">Audio sudah dihapus otomatis</span>
                @else
                    <audio controls preload="none" src="{{ route('voice-notes.stream', $vn) }}" class="w-full max-w-[300px] h-10"></audio>
                @endif
                @if($vn && $vn->disguise_mode !== 'none')
                    <span class="text-xs font-bold inline-flex items-center gap-1 {{ $mine ? 'text-white/80' : 'text-ink-soft' }}"><x-icon name="lock" size="12" />Suara disamarkan ({{ \App\Models\VoiceNote::MODE_LABELS[$vn->disguise_mode] }})</span>
                @endif
                @if($role === 'counselor' && $vn)
                    @if($vn->transcript_status === 'done' || $vn->transcript_status === 'skipped')
                        <button type="button" x-on:click="showTx = !showTx" class="self-start text-xs font-extrabold text-sage-ink underline" x-text="showTx ? 'Sembunyikan transkrip' : 'Lihat transkrip'">Lihat transkrip</button>
                        <p x-show="showTx" x-cloak class="text-sm leading-relaxed bg-white/70 rounded-xl px-3 py-2">{{ $vn->transcript }}</p>
                    @elseif($vn->transcript_status === 'pending')
                        <span class="text-xs text-ink-soft">Transkrip sedang diproses…</span>
                    @else
                        <span class="text-xs text-ink-soft">Transkrip gagal dibuat.</span>
                    @endif
                @endif
            </div>
            @include('chat.receipt')
        </div>
        @break

    @case('image')
        @php $img = $m->meta['image'] ?? null; $src = route('chat-images.show', $m); @endphp
        <div class="{{ $align }} max-w-[86%] sm:max-w-[60%] flex flex-col gap-1">
            <div class="p-1.5 {{ $radius }} {{ $bubbleBg }} flex flex-col gap-1.5">
                <button type="button" x-on:click="$dispatch('open-photo', @js($src))" class="block overflow-hidden rounded-2xl bg-white/60" aria-label="Lihat foto ukuran penuh">
                    <img src="{{ $src }}" alt="Foto{{ filled($m->body) ? ': '.$m->body : '' }}" loading="lazy" @if($img) width="{{ $img['width'] }}" height="{{ $img['height'] }}" @endif class="block max-h-[320px] w-auto max-w-full object-cover">
                </button>
                @if(filled($m->body))<p class="px-2.5 pb-1.5 text-[15px] leading-[1.55] whitespace-pre-line wrap-anywhere">{{ $m->body }}</p>@endif
            </div>
            @include('chat.receipt')
        </div>
        @break

    @case('song')
        {{-- Lagu di chat sudah tidak bisa dikirim lagi; pesan lama tetap ditampilkan. --}}
        @php $song = $m->meta['song'] ?? null; @endphp
        @if($song)
        <div class="{{ $align }} w-[min(360px,86%)] flex flex-col gap-1">
            @if(filled($m->body))<div class="px-4 py-3 {{ $radius }} {{ $bubbleBg }} text-[15px] leading-[1.55] whitespace-pre-line wrap-anywhere">{{ $m->body }}</div>@endif
            <x-song-card :song="$song" />
            @include('chat.receipt')
        </div>
        @endif
        @break

    @case('article')
        @php $a = $ctx['articles'][$m->meta['article_id'] ?? 0] ?? null; @endphp
        <div class="{{ $align }} max-w-[92%] sm:max-w-[70%] flex flex-col gap-1.5">
            @if(filled($m->body))<div class="px-4 py-3 {{ $radius }} {{ $bubbleBg }} text-[15px] leading-[1.55]">{{ $m->body }}</div>@endif
            @if($a)
                <div class="bg-white border border-line rounded-card p-3 flex gap-3 items-center w-full sm:min-w-[300px]">
                    <span class="w-16 h-16 flex-none rounded-2xl overflow-hidden"><x-article-cover :article="$a" height="h-16" :icon-size="36" /></span>
                    <span class="flex-1 flex flex-col gap-1 min-w-0"><span class="text-[11px] font-extrabold tracking-[0.06em] uppercase text-sage-ink2">{{ $a->category?->name }} · {{ $a->read_minutes }} menit</span><span class="font-extrabold text-sm leading-tight">{{ $a->title }}</span></span>
                    @if($role === 'student')
                        <a href="{{ route('student.selfhelp.show', $a) }}" class="inline-flex items-center h-11 px-4 rounded-full bg-sage-deep text-white no-underline text-[13px] font-extrabold">Baca</a>
                    @else
                        <span class="text-xs font-extrabold text-sage-ink2">Terkirim</span>
                    @endif
                </div>
            @endif
            @include('chat.receipt')
        </div>
        @break

    @case('session_invite')
        @php $inv = $ctx['invitations'][$m->meta['invitation_id'] ?? 0] ?? null; @endphp
        @if($inv)
            @include('chat.cards.invitation', ['inv' => $inv])
        @endif
        @break

    @case('identity_request')
        @php $req = $ctx['identityRequests'][$m->meta['identity_request_id'] ?? 0] ?? null; @endphp
        @if($req)
            @if($role === 'student')
                <div class="self-start max-w-[92%] sm:max-w-[62%] bg-cream-light border-[1.5px] border-dashed border-sage rounded-card p-4 flex flex-col gap-2.5">
                    <span class="font-extrabold text-[15px]">{{ $req->counselor->displayName() }} ingin mengenalmu lebih dekat. Mau membuka identitasmu?</span>
                    @if($req->message)<span class="text-sm leading-relaxed text-[#5A4E47]">“{{ $req->message }}”</span>@endif
                    @if($req->status === 'pending' && $ctx['anonToCounselor'])
                        <div class="flex gap-2 flex-wrap">
                            <button type="button" x-on:click="$dispatch('open-reveal', { requestId: {{ $req->id }} })" class="btn btn-sage btn-sm !h-11 !text-sm">Buka Identitas</button>
                            <form method="POST" action="{{ route('student.identity-requests.respond', $req) }}">@csrf<input type="hidden" name="response" value="declined"><button class="btn btn-outline btn-sm !h-11 !text-sm">Tetap Anonim</button></form>
                        </div>
                    @elseif($req->status === 'accepted')
                        <span class="self-start text-xs font-extrabold text-sage-ink px-3 py-1.5 rounded-full bg-sage-tint">Kamu membuka identitas. {{ $req->counselor->displayName() }} sekarang melihat namamu.</span>
                    @else
                        <span class="self-start text-xs font-extrabold text-ink-soft px-3 py-1.5 rounded-full bg-cream-deep">Kamu memilih tetap anonim. Nggak apa-apa.</span>
                    @endif
                </div>
            @else
                <div class="self-end text-xs font-bold text-ink-soft px-3 py-1.5 rounded-full bg-cream-deep">
                    Permintaan buka identitas terkirim · {{ ['pending' => 'menunggu jawaban', 'accepted' => 'diterima', 'declined' => 'murid memilih tetap anonim'][$req->status] }}
                </div>
            @endif
        @endif
        @break

    @case('mood_share')
        @php $mood = $m->meta['mood'] ?? ($ctx['moods'][$m->meta['mood_entry_id'] ?? 0]->mood ?? 'neutral'); @endphp
        <div class="{{ $align }} max-w-[86%] sm:max-w-[62%] flex flex-col gap-1">
            <div class="bg-white border border-line rounded-card p-4 flex flex-col gap-2.5">
                <span class="flex items-center gap-3">
                    <span class="w-11 h-11 flex-none rounded-[14px] flex items-center justify-center" style="background: {{ $tints[$mood] ?? '#EDE5D0' }}"><x-mood-face :mood="$mood" size="28" /></span>
                    <span class="flex flex-col"><span class="text-xs font-extrabold uppercase tracking-[0.06em] text-ink-soft">Catatan mood dibagikan</span><span class="font-extrabold text-[15px]">{{ \App\Models\MoodEntry::LABELS[$mood] ?? '' }} · {{ isset($m->meta['date']) ? \Carbon\Carbon::parse($m->meta['date'])->translatedFormat('j M') : '' }}</span></span>
                </span>
                @if(filled($m->body))<p class="text-[15px] leading-relaxed">“{{ $m->body }}”</p>@endif
                @if(! empty($m->meta['song']))<x-song-card :song="$m->meta['song']" caption="lagu hari itu" />@endif
            </div>
            @include('chat.receipt')
        </div>
        @break

    @case('follow_up')
        @php $fu = $ctx['followUps'][$m->meta['follow_up_id'] ?? 0] ?? null; $labels = ['better' => 'Lebih baik', 'same' => 'Sama aja', 'worse' => 'Makin berat']; @endphp
        <div class="self-start max-w-[92%] sm:max-w-[62%] bg-white border border-line rounded-card p-4 flex flex-col gap-3">
            <span class="flex items-center gap-2.5"><span class="w-10 h-10 flex-none rounded-xl bg-sand-tint flex items-center justify-center text-sand-ink"><x-icon name="heart" size="20" /></span><span class="font-extrabold text-[15px]">Hai, gimana kabarmu sekarang?</span></span>
            @if($fu && ! $fu->response && $role === 'student')
                <form method="POST" action="{{ route('student.follow-ups.respond', $fu) }}" class="flex gap-2 flex-wrap">
                    @csrf
                    <button name="response" value="better" class="btn btn-soft btn-sm !h-11 !text-sm"><x-mood-face mood="happy" size="20" />Lebih baik</button>
                    <button name="response" value="same" class="btn btn-soft btn-sm !h-11 !text-sm"><x-mood-face mood="neutral" size="20" />Sama aja</button>
                    <button name="response" value="worse" class="btn btn-soft btn-sm !h-11 !text-sm"><x-mood-face mood="sad" size="20" />Makin berat</button>
                </form>
            @elseif($fu?->response)
                <span class="self-start text-sm font-extrabold px-3 py-1.5 rounded-full {{ $fu->response === 'worse' ? 'bg-terracotta-tint text-terracotta-ink' : ($fu->response === 'same' ? 'bg-sand-tint text-sand-ink' : 'bg-sage-tint text-sage-ink') }}">{{ $role === 'student' ? 'Jawabanmu: ' : 'Jawaban murid: ' }}{{ $labels[$fu->response] }}</span>
            @else
                <span class="text-xs text-ink-soft">Menunggu jawaban murid.</span>
            @endif
        </div>
        @break

    @case('auto_reply')
        @if($role === 'student')
            <div class="self-start max-w-[92%] sm:max-w-[70%] bg-white border border-line rounded-card p-4 flex flex-col gap-2.5">
                <span class="text-xs font-extrabold text-ink-soft">Balasan otomatis · {{ $m->meta['counselor'] ?? $ctx['counselorName'] }} sedang tidak tersedia</span>
                <span class="text-sm leading-relaxed">{{ $m->body }}</span>
                <span class="flex gap-2 flex-wrap">
                    <a href="{{ route('student.selfhelp.index') }}" class="inline-flex items-center gap-1.5 h-11 px-3.5 rounded-full bg-sage-tint text-sage-ink no-underline text-[13px] font-extrabold"><x-icon name="book" size="16" />Ruang Self-Help</a>
                    <a href="{{ route('emergency') }}" class="inline-flex items-center gap-1.5 h-11 px-3.5 rounded-full bg-danger-tint text-danger-ink no-underline text-[13px] font-extrabold"><x-icon name="phone" size="16" />Merasa tidak aman? Kontak darurat</a>
                </span>
            </div>
        @else
            <div class="self-center text-xs font-bold text-ink-soft px-3 py-1.5 rounded-full bg-cream-deep">Balasan otomatis terkirim ({{ $time }}) · di luar jam layanan/offline</div>
        @endif
        @break

    @case('emergency_info')
        @if($role === 'student')
            <div class="self-stretch sm:self-start sm:max-w-[80%] bg-white border-[1.5px] border-danger-line rounded-card p-4 flex flex-col gap-3">
                <span class="flex items-center gap-2.5"><span class="w-10 h-10 flex-none rounded-xl bg-danger-tint flex items-center justify-center text-danger"><x-icon name="phone" size="20" /></span><span class="font-extrabold text-[15px]">Kontak bantuan darurat</span></span>
                <span class="text-sm leading-relaxed">{{ $m->body }}</span>
                @include('partials.emergency-contacts', ['contacts' => $ctx['contacts'], 'compact' => true])
                <a href="{{ route('emergency') }}" class="self-start text-sm font-extrabold text-danger">Buka halaman Bantuan Darurat</a>
            </div>
        @else
            <div class="self-center text-xs font-extrabold text-danger-ink px-3 py-1.5 rounded-full bg-danger-tint inline-flex items-center gap-1.5"><x-icon name="phone" size="13" />Kartu kontak darurat dikirim otomatis ke murid · {{ $time }}</div>
        @endif
        @break

    @default
        @if(($m->meta['testimonial_cta'] ?? false) && $role === 'student')
            <div class="self-start max-w-[92%] sm:max-w-[62%] bg-sage-tint rounded-card p-4 flex flex-col gap-2.5 text-sage-ink">
                <span class="text-[15px] leading-relaxed font-bold">Senang dengarnya! <x-icon name="heart" size="16" stroke="2.4" class="inline-block align-[-2px]" /> Mau berbagi cerita tanpa nama untuk menguatkan teman sekolahmu?</span>
                <a href="{{ route('student.stories.create') }}" class="self-start btn btn-sage btn-sm !h-11 !text-sm">Bagikan ceritamu</a>
            </div>
            @break
        @endif
        <div class="self-center text-center max-w-[90%] text-xs font-bold text-ink-soft px-3 py-1.5 rounded-full bg-cream-deep">{{ $m->body }} · {{ $time }}</div>
@endswitch
@endif
</div>
