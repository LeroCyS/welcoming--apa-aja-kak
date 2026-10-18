@php
    $tz = \App\Models\School::current()->timezone;
    $first = $invitation->slots->firstWhere('status', 'available') ?? $invitation->slots->first();
@endphp
<div class="{{ ($bare ?? false) ? '' : 'panel !rounded-card-l p-[18px]' }} flex flex-col gap-3.5">
    <div class="flex gap-3 items-center">
        @if($first)
            <div class="w-[60px] h-16 flex-none rounded-[18px] bg-terracotta-tint flex flex-col items-center justify-center text-terracotta-ink"><span class="text-[11px] font-extrabold uppercase">{{ $first->start_at->timezone($tz)->translatedFormat('D') }}</span><span class="font-display text-2xl leading-none">{{ $first->start_at->timezone($tz)->format('j') }}</span><span class="text-[10px] font-extrabold uppercase">{{ $first->start_at->timezone($tz)->translatedFormat('M') }}</span></div>
        @endif
        <div class="flex flex-col gap-0.5">
            <span class="font-extrabold text-[15px]">{{ $invitation->counselor->displayName() }} ngajak ngobrol langsung</span>
            <span class="text-[13px] text-ink-soft">{{ $invitation->slots->count() }} pilihan waktu · berlaku sampai {{ $invitation->expires_at->timezone($tz)->translatedFormat('j M') }}</span>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('student.conversations.show', $invitation->conversation) }}#undangan-{{ $invitation->id }}" class="flex-[1.3] btn btn-primary btn-sm !text-sm">Terima & Pilih Jadwal</a>
        <form method="POST" action="{{ route('student.invitations.decline', $invitation) }}" class="flex-1">@csrf<button class="w-full btn btn-outline btn-sm !text-sm">Belum siap</button></form>
    </div>
</div>
