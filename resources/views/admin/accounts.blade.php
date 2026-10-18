@extends('layouts.admin', ['active' => 'akun'])
@section('title', 'Kelola Akun')
@section('subtitle', $counts['total'].' akun · '.$counts['pending'].' belum ganti password')
@section('content')
<div class="flex gap-2.5 flex-wrap -mt-2">
    <button type="button" x-data x-on:click="$dispatch('open-modal', 'add-account')" class="btn btn-sand btn-sm !text-sm"><x-icon name="plus" size="18" />Tambah Guru BK</button>
</div>

<div class="flex gap-3 items-center px-4 py-3.5 rounded-[18px] bg-sand-tint text-sm leading-normal text-sand-ink2">
    <x-icon name="lock" size="20" />
    <span>Murid mendaftar sendiri secara anonim. Data murid, percakapan, dan jurnal tidak terlihat di sini.</span>
</div>

@if($transfers->isNotEmpty())
    {{-- Permintaan pindah sekolah dari murid: hanya nomor akun & alasan pilihan, tanpa identitas. --}}
    <section id="pindah-sekolah" aria-labelledby="transfer-h" class="card !rounded-card-l p-4 sm:p-5 flex flex-col gap-3">
        <h2 id="transfer-h" class="!font-sans text-base font-extrabold flex items-center gap-2"><x-icon name="home" size="18" />Permintaan pindah sekolah <span class="min-w-6 h-6 px-1.5 rounded-full bg-sand-tint text-sand-ink text-xs font-extrabold inline-flex items-center justify-center">{{ $transfers->count() }}</span></h2>
        <p class="text-[13px] leading-snug text-ink-soft -mt-1">Setujui = murid dilepaskan dari sekolah ini. Ceritanya di sini menjadi arsip baca-saja, dan ia memilih sekolah lagi saat masuk.</p>
        <ul class="flex flex-col divide-y divide-line-soft">
            @foreach($transfers as $t)
                <li class="py-3 flex items-center gap-3 flex-wrap">
                    <span class="flex-1 min-w-[180px] flex flex-col gap-0.5"><span class="font-extrabold tabular-nums">Murid #{{ $t->student_id }}</span><span class="text-[13px] text-ink-soft">{{ $t->reasonLabel() }} · {{ $t->created_at->diffForHumans() }}</span></span>
                    <span class="flex gap-1.5">
                        <form method="POST" action="{{ route('admin.transfers.reject', $t) }}" data-confirm="Tolak permintaan Murid #{{ $t->student_id }}?" data-confirm-ok="Ya, tolak">@csrf<button class="h-[34px] px-3.5 rounded-full bg-cream text-[13px] font-extrabold text-ink-soft">Tolak</button></form>
                        <form method="POST" action="{{ route('admin.transfers.approve', $t) }}" data-confirm-title="Lepaskan Murid #{{ $t->student_id }} dari sekolah ini?" data-confirm="Ceritanya di sekolah ini menjadi arsip baca-saja, dan murid akan diminta memilih sekolah lagi saat masuk." data-confirm-ok="Ya, setujui">@csrf<button class="h-[34px] px-3.5 rounded-full bg-sand-deep text-white text-[13px] font-extrabold">Setujui</button></form>
                    </span>
                </li>
            @endforeach
        </ul>
    </section>
@endif

<form method="GET" class="flex justify-between items-center gap-3 flex-wrap">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div role="tablist" aria-label="Jenis akun" class="seg">
        <a role="tab" aria-selected="{{ $tab === 'murid' ? 'true' : 'false' }}" href="{{ route('admin.accounts', ['tab' => 'murid']) }}" class="seg-item no-underline {{ $tab === 'murid' ? 'seg-item-on' : '' }}">Murid ({{ $counts['murid'] }})</a>
        <a role="tab" aria-selected="{{ $tab === 'guru' ? 'true' : 'false' }}" href="{{ route('admin.accounts', ['tab' => 'guru']) }}" class="seg-item no-underline {{ $tab === 'guru' ? 'seg-item-on' : '' }}">Guru BK ({{ $counts['guru'] }})</a>
    </div>
    @if($tab === 'murid')
        <div class="relative flex-[0_1_360px] min-w-[220px]">
            <label for="akun-search" class="sr-only">Cari nomor akun murid</label>
            <x-icon name="search" size="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-soft" />
            <input id="akun-search" name="q" type="search" inputmode="numeric" value="{{ request('q') }}" placeholder="Cari nomor akun, mis. 123" class="field !h-11 !pl-10 text-sm">
        </div>
    @endif
    @if($tab === 'guru')
        <div class="relative flex-[0_1_520px] min-w-[220px]">
            <label for="akun-search" class="sr-only">Cari akun</label>
            <x-icon name="search" size="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-soft" />
            <input id="akun-search" name="q" type="search" value="{{ request('q') }}" placeholder="Cari nama, NIP, atau email" class="field !h-11 !pl-10 text-sm">
        </div>
    @endif
</form>

<div class="card !rounded-card-l p-2 overflow-x-auto">
    @if($tab === 'murid')
    <table class="table-wl min-w-[620px]">
        <thead><tr><th scope="col">Akun</th><th scope="col">Terdaftar</th><th scope="col">Terakhir masuk</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead>
        <tbody>
            @forelse($users as $u)
                <tr>
                    <td class="font-extrabold tabular-nums">Murid #{{ $u->id }}</td>
                    <td class="text-ink-soft">{{ $u->created_at?->translatedFormat('j M Y') }}</td>
                    <td class="text-ink-soft">{{ $u->last_login_at?->diffForHumans() ?? '-' }}</td>
                    <td>@if($u->is_active)<x-badge tone="sage" class="!text-xs">Aktif</x-badge>@else<x-badge tone="neutral" class="!text-xs">Nonaktif</x-badge>@endif</td>
                    <td>
                        <div class="flex gap-1.5 flex-wrap">
                            <form method="POST" action="{{ route('admin.accounts.toggle', $u) }}">@csrf<button class="h-[34px] px-3 rounded-full bg-cream text-[13px] font-extrabold text-ink-soft">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                            <form method="POST" action="{{ route('admin.accounts.release', $u) }}" x-data data-confirm-title="Lepaskan Murid #{{ $u->id }} dari sekolah ini?" data-confirm="Ceritanya di sekolah ini menjadi arsip baca-saja, dan murid akan diminta memilih sekolah lagi saat masuk." data-confirm-ok="Ya, lepaskan" data-confirm-tone="danger">@csrf<button class="h-[34px] px-3 rounded-full bg-cream text-[13px] font-extrabold text-ink-soft">Lepaskan dari sekolah</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state title="Belum ada murid yang mendaftar" text="Murid membuat akun sendiri lewat halaman Daftar." /></td></tr>
            @endforelse
        </tbody>
    </table>
    @else
    <table class="table-wl min-w-[820px]">
        <thead><tr><th scope="col">Nama</th><th scope="col">NIP / Kode</th><th scope="col">Nama panggilan</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead>
        <tbody>
            @forelse($users as $u)
                <tr>
                    <td class="font-extrabold">{{ $u->name }}@if($u->email)<span class="block text-xs font-semibold text-ink-soft">{{ $u->email }}</span>@endif</td>
                    <td class="text-ink-soft tabular-nums">{{ $u->identifier }}</td>
                    <td class="text-ink-soft">{{ $u->counselorProfile?->nickname }}</td>
                    <td>@if($u->is_active)<x-badge tone="sage" class="!text-xs">Aktif</x-badge>@else<x-badge tone="neutral" class="!text-xs">Nonaktif</x-badge>@endif</td>
                    <td>
                        <div class="flex gap-1.5">
                            <form method="POST" action="{{ route('admin.accounts.reset-password', $u) }}" x-data data-confirm="Reset password {{ $u->name }} ke NIP/kode?" data-confirm-ok="Ya, reset">@csrf<button class="h-[34px] px-3 rounded-full border-[1.5px] border-line bg-white text-[13px] font-extrabold">Reset Password</button></form>
                            <form method="POST" action="{{ route('admin.accounts.toggle', $u) }}">@csrf<button class="h-[34px] px-3 rounded-full bg-cream text-[13px] font-extrabold text-ink-soft">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state title="Belum ada akun yang cocok" text="Coba ubah kata kunci pencarian." /></td></tr>
            @endforelse
        </tbody>
    </table>
    @endif
</div>
<div class="flex items-center justify-between gap-3 flex-wrap text-[13px] text-ink-soft">
    <span>Menampilkan {{ $users->count() }} dari {{ $users->total() }} {{ $tab === 'murid' ? 'murid' : 'Guru BK' }}</span>
    {{ $users->links('partials.pagination') }}
</div>
@endsection

@push('modals')
<x-modal name="add-account" title="Tambah akun Guru BK" :show="$errors->hasAny(['name', 'identifier', 'email', 'nickname'])">
    <form method="POST" action="{{ route('admin.accounts.store') }}" class="flex flex-col gap-4">
        @csrf
        <p class="text-sm text-ink-soft leading-relaxed -mt-1">Akun murid tidak dibuat admin, murid mendaftar sendiri dengan email atau Google.</p>
        <x-input name="name" label="Nama lengkap" required />
        <x-input name="identifier" label="NIP / Kode" hint="Dipakai untuk masuk. Password awal = NIP/kode." required />
        <x-input name="email" type="email" label="Email (opsional)" />
        <x-input name="nickname" label="Nama panggilan" placeholder="Bu Rina" hint="Profil & jam layanan default dibuat otomatis." required />
        <div class="flex justify-end gap-2 mt-2">
            <button type="button" x-on:click="$dispatch('close-modal', 'add-account')" class="btn btn-outline btn-sm">Batal</button>
            <button class="btn btn-sand btn-sm">Simpan akun</button>
        </div>
    </form>
</x-modal>
@endpush
