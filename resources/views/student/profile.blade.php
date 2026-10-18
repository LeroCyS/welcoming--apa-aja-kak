@extends('layouts.student', ['active' => 'profil'])
@section('title', 'Profil & Pengaturan')
@php
    $hasPassword = $user->password !== null;
    [$minGrade, $maxGrade] = $user->school->gradeRange();
@endphp
@section('header')
<h1 class="sr-only">Profil & Pengaturan</h1>
@endsection
@section('content')
<div class="grid lg:grid-cols-[360px_minmax(0,1fr)] gap-6 items-start w-full">
    {{-- Kolom kiri: kartu profil + pintasan --}}
    <div class="flex flex-col gap-4 lg:sticky lg:top-24">
        <section class="relative overflow-hidden bg-white border border-line-soft rounded-panel shadow-soft">
            <div class="h-20 bg-terracotta-tint [background-image:radial-gradient(#E9B3A6_1.4px,transparent_1.4px)] [background-size:16px_16px]"></div>
            <div class="px-6 pb-6 -mt-11 flex flex-col items-center gap-2 text-center">
                <span class="w-[88px] h-[88px] rounded-[28px] bg-white p-1.5 shadow-soft"><span class="w-full h-full rounded-[22px] bg-terracotta-tint flex items-center justify-center font-display text-[38px] text-terracotta-ink">{{ $user->initial() }}</span></span>
                <span class="font-display text-[24px] leading-tight mt-1">{{ $user->name ?? 'Murid Anonim' }}</span>
                <span class="text-sm text-ink-soft">{{ $profile->class_name ? $profile->class_name.' · ' : '' }}{{ $user->school->name }}</span>
                <span class="flex flex-wrap justify-center gap-1.5 mt-1">
                    <span class="px-2.5 py-1 rounded-full bg-cream text-xs font-extrabold text-ink-soft whitespace-nowrap">Murid #{{ $user->id }}</span>
                    <span class="px-2.5 py-1 rounded-full bg-sage-tint text-xs font-extrabold text-sage-ink inline-flex items-center gap-1"><x-icon name="lock" size="11" stroke="3" />Anonim</span>
                    @if($user->google_id)<span class="px-2.5 py-1 rounded-full bg-cream text-xs font-extrabold text-ink-soft whitespace-nowrap">Google</span>@endif
                </span>
            </div>
        </section>

        <nav aria-label="Pintasan" class="bg-white border border-line-soft rounded-panel shadow-soft p-2 flex flex-col">
            @foreach([
                [route('student.schedule.index'), 'calendar', 'Jadwal Saya', $upcoming ? $upcoming.' sesi' : null, 'bg-sand-tint text-sand-ink'],
                [route('student.counselors'), 'people-heart', 'Kenalan Guru BK', $counselorCount.' guru', 'bg-sage-tint text-sage-ink'],
                [route('student.stories.create'), 'heart', 'Bagikan ceritamu', null, 'bg-terracotta-tint text-terracotta-ink'],
            ] as [$href, $icon, $label, $meta, $tone])
                <a href="{{ $href }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-2xl no-underline text-ink hover:bg-cream-light transition-colors">
                    <span class="w-9 h-9 flex-none rounded-xl flex items-center justify-center {{ $tone }}"><x-icon :name="$icon" size="18" /></span>
                    <span class="flex-1 font-bold text-[15px]">{{ $label }}</span>
                    @if($meta)<span class="text-xs font-bold text-ink-soft">{{ $meta }}</span>@endif
                    <x-icon name="chevron-right" size="16" class="text-ink-soft transition-transform group-hover:translate-x-0.5" />
                </a>
            @endforeach
        </nav>

        <div class="flex gap-2">
            <a href="{{ route('emergency') }}" class="flex-1 inline-flex items-center justify-center gap-2 h-12 rounded-full border-[1.5px] border-danger-line bg-white text-danger-ink no-underline text-sm font-extrabold transition hover:bg-danger-tint"><x-icon name="phone" size="17" />Bantuan Darurat</a>
            <form method="POST" action="{{ route('logout') }}" data-logout class="flex-1">@csrf<button class="w-full inline-flex items-center justify-center gap-2 h-12 rounded-full border-[1.5px] border-line bg-white text-ink-soft text-sm font-extrabold transition hover:bg-cream-light"><x-icon name="logout" size="17" />Keluar Akun</button></form>
        </div>
    </div>

    {{-- Kolom kanan: pengaturan berkelompok --}}
    <div class="flex flex-col gap-6 min-w-0">
        <section id="pengaturan" aria-labelledby="set-h" class="flex flex-col gap-2.5" x-data="{ save(field, value) { window.axios.patch('{{ route('student.profile.settings') }}', { [field]: value }) } }">
            <h2 id="set-h" class="px-1 !font-sans text-[13px] font-extrabold tracking-[0.08em] uppercase text-ink-soft">Pengaturan</h2>
            <div class="bg-white border border-line-soft rounded-panel shadow-soft divide-y divide-line-soft">
                @foreach([
                    ['notifications_enabled', 'Notifikasi', 'Balasan guru & undangan sesi. Isi pesan tidak ditampilkan.', 'bell', 'bg-sand-tint text-sand-ink'],
                    ['mood_prompt_enabled', 'Pop-up ajakan cerita', 'Muncul saat mood-mu berat beberapa hari.', 'mood', 'bg-sage-tint text-sage-ink'],
                    ['mood_signal_enabled', 'Sinyal mood untuk prioritas', 'Mood menurun boleh menaikkan prioritas ceritamu. Guru hanya melihat label “Tren suasana hati menurun”, bukan jurnalmu.', 'heart', 'bg-terracotta-tint text-terracotta-ink'],
                ] as [$field, $label, $desc, $icon, $tone])
                    <div class="flex items-center gap-4 px-5 py-4" x-data="{ on: {{ $profile->$field ? 'true' : 'false' }} }">
                        <span class="w-10 h-10 flex-none rounded-xl flex items-center justify-center {{ $tone }}"><x-icon :name="$icon" size="19" /></span>
                        <span class="flex-1 flex flex-col gap-0.5 min-w-0"><span class="font-extrabold text-[15px]" id="lbl-{{ $field }}">{{ $label }}</span><span class="text-[13px] leading-snug text-ink-soft">{{ $desc }}</span></span>
                        <button type="button" role="switch" :aria-checked="on.toString()" aria-labelledby="lbl-{{ $field }}" x-on:click="on = !on; save('{{ $field }}', on)" class="relative w-12 h-7 flex-none rounded-full transition" :class="on ? 'bg-sage-deep' : 'bg-toggle-off'"><span class="absolute top-1 w-5 h-5 rounded-full bg-white shadow-xs transition-all" :class="on ? 'left-6' : 'left-1'"></span></button>
                    </div>
                @endforeach

                <div class="px-5 py-4 flex flex-col gap-3" x-data="{ open: {{ $errors->pin->any() ? 'true' : 'false' }} }">
                    <div class="flex items-center gap-4">
                        <span class="w-10 h-10 flex-none rounded-xl flex items-center justify-center bg-cream-light text-ink"><x-icon name="lock" size="19" /></span>
                        <span class="flex-1 flex flex-col gap-0.5 min-w-0"><span class="font-extrabold text-[15px]">Kunci PIN chat {!! $profile->pin_enabled ? '<span class="ml-1 text-[11px] px-2 py-0.5 rounded-full bg-sage-tint text-sage-ink whitespace-nowrap">Aktif</span>' : '' !!}</span><span class="text-[13px] leading-snug text-ink-soft">PIN 6 digit untuk membuka cerita, mood, dan jadwal.</span></span>
                        <button type="button" x-on:click="open = !open" class="btn btn-soft btn-sm !text-sm">{{ $profile->pin_enabled ? 'Matikan' : 'Aktifkan' }}</button>
                    </div>
                    <div x-show="open" x-cloak class="sm:pl-14">
                        @if($profile->pin_enabled)
                            <form method="POST" action="{{ route('student.pin.disable') }}" class="flex gap-3 items-end flex-wrap">
                                @csrf @method('DELETE')
                                <div><label for="cur-pin" class="field-label">PIN saat ini</label><input id="cur-pin" name="current_pin" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="off" class="field !w-40 tracking-[0.4em]" required></div>
                                <button class="btn btn-outline btn-sm !h-12">Matikan PIN</button>
                            </form>
                            @if($errors->pin->has('current_pin'))<p class="field-error">{{ $errors->pin->first('current_pin') }}</p>@endif
                        @else
                            <form method="POST" action="{{ route('student.pin.enable') }}" class="flex gap-3 items-end flex-wrap" onsubmit="try { sessionStorage.setItem('wl-pin-tab', '1') } catch (e) {}">
                                @csrf
                                <div><label for="new-pin" class="field-label">PIN baru (6 angka)</label><input id="new-pin" name="pin" type="password" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="new-password" class="field !w-40 tracking-[0.4em]" required></div>
                                <div><label for="new-pin2" class="field-label">Ulangi PIN</label><input id="new-pin2" name="pin_confirmation" type="password" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="new-password" class="field !w-40 tracking-[0.4em]" required></div>
                                <button class="btn btn-primary btn-sm !h-12">Simpan PIN</button>
                            </form>
                            @if($errors->pin->has('pin'))<p class="field-error">{{ $errors->pin->first('pin') }}</p>@endif
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section id="identitas" aria-labelledby="id-h" class="flex flex-col gap-2.5">
            <h2 id="id-h" class="px-1 !font-sans text-[13px] font-extrabold tracking-[0.08em] uppercase text-ink-soft">Identitas <span class="normal-case tracking-normal font-bold">(opsional)</span></h2>
            <div class="bg-white border border-line-soft rounded-panel shadow-soft p-5 flex flex-col gap-4">
                <p class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-2xl bg-sage-tint text-[13px] leading-snug text-sage-ink"><x-icon name="lock" size="16" class="flex-none" /><span>Hanya terlihat oleh Guru BK saat kamu memilih <b>Buka Identitas</b>.</span></p>
                <form method="POST" action="{{ route('student.profile.identity') }}" class="grid sm:grid-cols-[2fr_1.4fr_1fr] gap-3 items-start">
                    @csrf @method('PATCH')
                    @foreach([['name', 'Nama', 'text', $user->name, 'mis. Alya', 'name'], ['class_name', 'Kelas', 'text', $profile->class_name, 'mis. '.($minGrade >= 10 ? 'XI IPS 2' : $maxGrade.'A'), 'off'], ['grade_level', 'Tingkat', 'number', $profile->grade_level, $minGrade.'-'.$maxGrade, 'off']] as [$n, $l, $t, $v, $ph, $ac])
                        <div><label for="id-{{ $n }}" class="field-label">{{ $l }}</label><input id="id-{{ $n }}" name="{{ $n }}" type="{{ $t }}" value="{{ old($n, $v) }}" placeholder="{{ $ph }}" autocomplete="{{ $ac }}" @if($t === 'number') min="{{ $minGrade }}" max="{{ $maxGrade }}" @endif class="field @if($errors->identity->has($n)) is-invalid @endif">@if($errors->identity->has($n))<p class="field-error">{{ $errors->identity->first($n) }}</p>@endif</div>
                    @endforeach
                    <div class="sm:col-span-3 flex items-center gap-3 flex-wrap">
                        <button class="btn btn-primary btn-sm">Simpan profil</button>
                        <span class="text-xs text-ink-soft">Kosongkan kolom untuk menghapusnya.</span>
                    </div>
                </form>
            </div>
        </section>

        <section aria-labelledby="akun-h" class="flex flex-col gap-2.5">
            <h2 id="akun-h" class="px-1 !font-sans text-[13px] font-extrabold tracking-[0.08em] uppercase text-ink-soft">Akun</h2>
            <div class="bg-white border border-line-soft rounded-panel shadow-soft divide-y divide-line-soft">
                <div class="flex items-center gap-4 px-5 py-4">
                    <span class="w-10 h-10 flex-none rounded-xl flex items-center justify-center bg-cream-light text-ink"><x-icon name="person" size="19" /></span>
                    @if($user->email)
                        <span class="flex-1 flex flex-col gap-0.5 min-w-0"><span class="font-extrabold text-[15px] break-all">{{ $user->email }}</span><span class="text-[13px] text-ink-soft">{{ $user->google_id ? 'Terhubung dengan Google · ' : '' }}hanya untuk masuk, tidak terlihat guru & admin</span></span>
                    @else
                        <span class="flex-1 flex flex-col gap-0.5 min-w-0"><span class="font-extrabold text-[15px]">Masuk dengan Google</span><span class="text-[13px] text-ink-soft">Kami tidak menerima email, nama, atau foto Google-mu.</span></span>
                    @endif
                </div>

                {{-- Akun Google tanpa email tidak bisa masuk dengan password, jadi pengaturan password disembunyikan. --}}
                @if($user->email)
                <div class="px-5 py-4 flex flex-col gap-3" x-data="{ open: {{ $errors->password->any() ? 'true' : 'false' }} }">
                    <div class="flex items-center gap-4">
                        <span class="w-10 h-10 flex-none rounded-xl flex items-center justify-center bg-cream-light text-ink"><x-icon name="lock" size="19" /></span>
                        <span class="flex-1 flex flex-col gap-0.5 min-w-0"><span id="pw-h" class="font-extrabold text-[15px]">{{ $hasPassword ? 'Ganti password' : 'Buat password' }}</span>@unless($hasPassword)<span class="text-[13px] text-ink-soft">Agar bisa masuk juga dengan email.</span>@endunless</span>
                        <button type="button" x-on:click="open = !open" class="btn btn-soft btn-sm !text-sm" x-text="open ? 'Tutup' : '{{ $hasPassword ? 'Ganti' : 'Buat' }}'">{{ $hasPassword ? 'Ganti' : 'Buat' }}</button>
                    </div>
                    <form x-show="open" x-cloak method="POST" action="{{ route('student.profile.password') }}" class="grid {{ $hasPassword ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }} gap-3 items-end sm:pl-14">
                        @csrf
                        @foreach(array_filter([$hasPassword ? ['current_password', 'Password lama', 'current-password'] : null, ['password', 'Password baru', 'new-password'], ['password_confirmation', 'Ulangi password baru', 'new-password']]) as [$n, $l, $ac])
                            <div><label for="pw-{{ $n }}" class="field-label">{{ $l }}</label><input id="pw-{{ $n }}" name="{{ $n }}" type="password" autocomplete="{{ $ac }}" class="field" required>@if($errors->password->has($n))<p class="field-error">{{ $errors->password->first($n) }}</p>@endif</div>
                        @endforeach
                        <button class="btn btn-primary btn-sm sm:col-span-full justify-self-start">Simpan password</button>
                    </form>
                </div>
                @endif

                <details id="sekolah" class="group px-5 py-4" @if($transfer || $errors->transfer->any()) open @endif>
                    <summary class="flex items-center gap-4 cursor-pointer list-none">
                        <span class="w-10 h-10 flex-none rounded-xl flex items-center justify-center bg-cream-light text-ink"><x-icon name="home" size="19" /></span>
                        <span class="flex-1 flex flex-col gap-0.5 min-w-0"><span class="font-extrabold text-[15px] truncate">{{ $user->school->name }}</span><span class="text-[13px] text-ink-soft">{{ $user->school->levelLabel() }}</span></span>
                        <x-icon name="chevron-down" size="16" class="text-ink-soft transition group-open:rotate-180" />
                    </summary>
                    {{-- Permintaan pindah sekolah: admin hanya melihat "Murid #id" dan alasan pilihan. --}}
                    <div class="mt-3 sm:pl-14 flex flex-col gap-3">
                        @if($transfer?->status === 'pending')
                            <div class="flex items-start gap-2.5 px-3.5 py-3 rounded-2xl bg-sand-tint text-sand-ink2">
                                <x-icon name="clock" size="18" class="flex-none mt-px" />
                                <span class="flex-1 min-w-0 flex flex-col gap-0.5"><span class="text-sm font-extrabold">Permintaan pindah sekolah sedang ditinjau</span><span class="text-[13px] leading-snug">{{ $transfer->reasonLabel() }} · dikirim {{ $transfer->created_at->diffForHumans() }}</span></span>
                            </div>
                            <form method="POST" action="{{ route('student.transfer.destroy') }}" data-confirm="Batalkan permintaan pindah sekolah?" data-confirm-ok="Ya, batalkan">@csrf @method('DELETE')<button class="btn btn-outline btn-sm">Batalkan permintaan</button></form>
                        @else
                            @if($transfer?->status === 'rejected')
                                <p class="flex items-start gap-2.5 px-3.5 py-3 rounded-2xl bg-cream-light text-[13px] leading-snug text-ink-soft"><x-icon name="info" size="16" class="flex-none mt-px" /><span>Permintaan pindah sekolahmu belum disetujui admin. Kamu bisa mengajukannya lagi.</span></p>
                            @endif
                            <p class="text-[13px] leading-snug text-ink-soft">Salah pilih atau pindah sekolah? Ajukan ke admin sekolah. Setelah disetujui, cerita di sekolah ini jadi arsip dan kamu memilih sekolah lagi.</p>
                            <form method="POST" action="{{ route('student.transfer.store') }}" class="flex flex-col sm:flex-row gap-2.5 sm:items-end" data-confirm-title="Ajukan pindah sekolah?" data-confirm="Admin sekolah akan meninjau permintaan Murid #{{ $user->id }}. Namamu tidak ikut terkirim." data-confirm-ok="Ya, ajukan">
                                @csrf
                                <div class="flex-1 min-w-0">
                                    <label for="transfer-reason" class="field-label">Alasan</label>
                                    <select id="transfer-reason" name="reason" required class="field @if($errors->transfer->has('reason')) is-invalid @endif">
                                        <option value="" disabled @selected(! old('reason'))>Pilih alasan</option>
                                        @foreach(\App\Models\SchoolTransferRequest::REASONS as $key => $label)<option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>@endforeach
                                    </select>
                                    @if($errors->transfer->has('reason'))<p class="field-error">{{ $errors->transfer->first('reason') }}</p>@endif
                                </div>
                                <button class="btn btn-primary btn-sm sm:!h-12">Ajukan pindah sekolah</button>
                            </form>
                        @endif
                    </div>
                </details>
            </div>
        </section>

        <details class="group bg-sage-tint/70 border border-sage-tint rounded-panel text-sage-ink">
            <summary class="flex items-center gap-3 px-5 py-4 cursor-pointer list-none">
                <x-icon name="lock" size="18" />
                <span class="flex-1 font-extrabold text-[15px]">Privasi & kerahasiaan</span>
                <x-icon name="chevron-down" size="16" class="transition group-open:rotate-180" />
            </summary>
            <div class="px-5 pb-5 flex flex-col gap-3">
                <p class="text-sm leading-relaxed font-bold">@include('partials.confidentiality')</p>
                <ul class="flex flex-col gap-1.5 text-sm leading-[1.55]">
                    @foreach(['Guru BK tidak melihat namamu selama kamu anonim.', 'Email dan akun Google-mu tidak pernah terlihat oleh Guru BK maupun admin.', 'Jurnal mood hanya kamu yang bisa lihat, kecuali catatan yang kamu kirim.', 'Admin sekolah tidak bisa membaca percakapanmu.', 'Membuka identitas hanya terjadi kalau kamu sendiri yang memilihnya.'] as $point)
                        <li class="flex gap-2"><x-icon name="check" size="16" class="mt-0.5 flex-none" />{{ $point }}</li>
                    @endforeach
                </ul>
            </div>
        </details>
    </div>
</div>
@endsection
