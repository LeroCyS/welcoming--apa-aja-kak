@extends('layouts.admin', ['active' => 'pengaturan'])
@section('title', 'Pengaturan Sekolah')
@section('subtitle', 'Berlaku untuk semua murid dan Guru BK')
@section('content')
{{-- HP: tab jadi grid 2x2 yang rata; layar lebar: satu baris. --}}
<div role="tablist" aria-label="Bagian pengaturan" class="seg max-sm:!grid max-sm:grid-cols-2 max-sm:gap-1 max-sm:!rounded-[22px] sm:self-start">
    @foreach(['sekolah' => 'Data Sekolah', 'kontak' => 'Kontak Darurat', 'kata-kunci' => 'Kata Kunci Sensitif', 'laporan-iseng' => 'Laporan Iseng'.($pranks->isNotEmpty() ? ' ('.$pranks->count().')' : '')] as $key => $label)
        <a role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}" href="{{ route('admin.settings', ['tab' => $key]) }}" class="seg-item no-underline text-center max-sm:!py-2 {{ $tab === $key ? 'seg-item-on' : '' }}">{{ $label }}</a>
    @endforeach
</div>

@if($tab === 'sekolah')
<form method="POST" action="{{ route('admin.settings.school') }}" enctype="multipart/form-data" class="flex flex-col gap-5">
    @csrf @method('PUT')
    <section aria-labelledby="sk-h" class="card !rounded-[26px] p-5 sm:p-6 flex flex-col gap-4">
        <h2 id="sk-h" class="text-[21px]">Data sekolah</h2>
        <div class="flex flex-col sm:flex-row gap-5 sm:items-start" x-data="{ preview: @js($school->logo_path ? Storage::disk('public')->url($school->logo_path) : null) }">
            <div class="flex sm:flex-col gap-4 sm:gap-2 items-center">
                <span class="w-20 h-20 sm:w-28 sm:h-28 flex-none rounded-[22px] sm:rounded-panel bg-cream border border-line flex items-center justify-center overflow-hidden text-xs font-bold text-ink-soft text-center">
                    <template x-if="preview"><img :src="preview" alt="Logo sekolah" class="w-full h-full object-contain"></template>
                    <span x-show="!preview">Belum ada logo</span>
                </span>
                <span class="flex flex-col gap-1 sm:items-center"><label for="sk-logo" class="max-sm:inline-flex max-sm:items-center max-sm:self-start max-sm:h-9 max-sm:px-4 max-sm:rounded-full max-sm:border-[1.5px] max-sm:border-line max-sm:bg-white text-[13px] font-extrabold text-sand-ink cursor-pointer">Ganti logo</label><span class="sm:hidden text-xs text-ink-soft">PNG, JPG, atau WEBP</span></span>
                <input id="sk-logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" data-shrink class="sr-only" x-on:change="preview = URL.createObjectURL($event.target.files[0])">
                @error('logo')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="flex-1 min-w-0 grid grid-cols-[repeat(auto-fit,minmax(min(240px,100%),1fr))] gap-3">
                <x-input name="name" label="Nama sekolah" :value="$school->name" required />
                <div>
                    <label for="sk-level" class="field-label">Jenjang</label>
                    <select id="sk-level" name="level" class="field">@foreach($levels as $key => $level)<option value="{{ $key }}" @selected(old('level', $school->level) === $key)>{{ $level['label'] }}</option>@endforeach</select>
                    @error('level')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="sk-tz" class="field-label">Zona waktu</label>
                    <select id="sk-tz" name="timezone" class="field">@foreach($timezones as $tz => $label)<option value="{{ $tz }}" @selected(old('timezone', $school->timezone) === $tz)>{{ $label }}</option>@endforeach</select>
                </div>
                <x-input name="address" type="textarea" label="Alamat" :value="$school->address" rows="2" class="col-span-full" />
            </div>
        </div>
    </section>
    <section aria-labelledby="jl-h" class="card !rounded-[26px] p-5 sm:p-6 flex flex-col gap-3.5">
        <div class="flex flex-col gap-0.5"><h2 id="jl-h" class="text-[21px]">Jam layanan BK default</h2><span class="text-[13px] text-ink-soft">Dipakai untuk Guru BK baru (Senin-Jumat). Setiap guru bisa menyesuaikan di profilnya.</span></div>
        <div class="grid grid-cols-2 sm:flex gap-3 sm:gap-4 items-end">
            <x-input name="default_service_start" type="time" label="Mulai" :value="substr($school->default_service_start, 0, 5)" class="sm:w-40" />
            <x-input name="default_service_end" type="time" label="Selesai" :value="substr($school->default_service_end, 0, 5)" class="sm:w-40" />
        </div>
    </section>
    <div class="flex justify-end"><button class="btn btn-sand max-sm:w-full">Simpan perubahan</button></div>
</form>

@endif

@if($tab === 'laporan-iseng')
<section aria-labelledby="pr-h" class="card !rounded-[26px] p-5 sm:p-6 flex flex-col gap-3.5">
    <div class="flex flex-col gap-0.5">
        <h2 id="pr-h" class="text-[21px]">Laporan iseng dari Guru BK</h2>
        <span class="text-[13px] text-ink-soft">Cerita yang ditandai iseng/palsu oleh Guru BK. Isi dan identitas murid tetap tersembunyi.</span>
    </div>
    @if($pranks->isEmpty())
        <p class="text-ink-soft text-sm">Belum ada laporan iseng.</p>
    @else
    <ul class="md:hidden flex flex-col divide-y divide-line-soft -mx-1">
        @foreach($pranks as $p)
            <li class="px-1 py-3.5 flex flex-col gap-2.5">
                <span class="flex flex-col gap-0.5"><span class="font-extrabold">{{ $p->anonymous_alias }}</span><span class="text-[13px] text-ink-soft">Ditandai {{ $p->prankReporter?->displayName() ?? '-' }} · {{ $p->prank_reported_at->timezone($school->timezone)->translatedFormat('j M Y, H.i') }}</span><span class="text-[13px] text-ink-soft">Laporan iseng lain dari murid ini: {{ $p->same_student_reports - 1 }}</span></span>
                @if($p->student_left_at)
                    <span class="text-[13px] font-bold text-ink-soft">Sudah dikeluarkan</span>
                @else
                    <form method="POST" action="{{ route('admin.pranks.ban', $p) }}" data-confirm-title="Keluarkan murid ini dari sekolah?" data-confirm="Ia tidak bisa bergabung lagi ke sekolah ini." data-confirm-ok="Ya, keluarkan" data-confirm-tone="danger">@csrf<button class="h-[38px] px-4 rounded-full bg-terracotta-tint text-[13px] font-extrabold text-terracotta-ink">Keluarkan dari sekolah</button></form>
                @endif
            </li>
        @endforeach
    </ul>
    <div class="max-md:hidden overflow-x-auto">
        <table class="table-wl min-w-[640px]">
            <thead><tr><th>Alias murid</th><th>Ditandai</th><th>Oleh</th><th>Laporan iseng lain dari murid ini</th><th></th></tr></thead>
            <tbody>
                @foreach($pranks as $p)
                    <tr>
                        <td class="font-bold">{{ $p->anonymous_alias }}</td>
                        <td>{{ $p->prank_reported_at->timezone($school->timezone)->translatedFormat('j M Y, H.i') }}</td>
                        <td>{{ $p->prankReporter?->displayName() ?? '-' }}</td>
                        <td>{{ $p->same_student_reports - 1 }}</td>
                        <td>
                            @if($p->student_left_at)
                                <span class="text-[13px] font-bold text-ink-soft">Sudah dikeluarkan</span>
                            @else
                                <form method="POST" action="{{ route('admin.pranks.ban', $p) }}" data-confirm-title="Keluarkan murid ini dari sekolah?" data-confirm="Ia tidak bisa bergabung lagi ke sekolah ini." data-confirm-ok="Ya, keluarkan" data-confirm-tone="danger">
                                    @csrf
                                    <button class="h-[34px] px-3 rounded-full bg-terracotta-tint text-[13px] font-extrabold text-terracotta-ink">Keluarkan dari sekolah</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</section>
@endif

@if($tab === 'kontak')
<section aria-labelledby="hl-h" class="card !rounded-[26px] p-5 sm:p-6 flex flex-col gap-3.5" x-data="contactEditor()" data-module="sortable-list">
    <div class="flex justify-between items-center gap-3 flex-wrap">
        <div class="flex flex-col gap-0.5"><h2 id="hl-h" class="text-[21px]">Kontak hotline darurat</h2><span class="text-[13px] text-ink-soft">Tampil di halaman <a href="{{ route('emergency') }}" class="text-danger font-extrabold">Bantuan Darurat</a>. Pastikan nomor sudah diverifikasi.<span class="max-md:hidden"> Seret untuk mengubah urutan.</span></span></div>
        <button type="button" x-on:click="edit(null)" class="btn btn-outline btn-sm !h-[42px] !text-sm max-sm:w-full"><x-icon name="plus" size="16" />Tambah kontak</button>
    </div>
    {{-- HP: kartu (urutan diatur di layar lebar dengan seret). --}}
    <ul class="md:hidden flex flex-col gap-2.5">
        @forelse($contacts as $c)
            <li class="rounded-[20px] border border-line-soft p-4 flex flex-col gap-2.5">
                <span class="flex items-start justify-between gap-3">
                    <span class="min-w-0 flex flex-col gap-0.5"><span class="font-extrabold">{{ $c->name }}</span><span class="text-xs text-ink-soft leading-snug">{{ $c->description }}</span></span>
                    @if($c->is_active)<x-badge tone="sage" class="!text-xs flex-none">Tampil</x-badge>@else<x-badge class="!text-xs flex-none">Disembunyikan</x-badge>@endif
                </span>
                <span class="flex gap-4 flex-wrap text-sm"><span class="flex items-center gap-1.5"><x-icon name="phone" size="15" class="text-ink-soft" /><span class="num">{{ $c->phone ?: '-' }}</span></span>@if($c->whatsapp)<span class="flex items-center gap-1.5"><x-icon name="chat" size="15" class="text-ink-soft" /><span class="num">{{ $c->whatsapp }}</span></span>@endif</span>
                <span class="flex gap-1.5">
                    <button type="button" x-on:click="edit({{ Js::from($c->only(['id', 'name', 'description', 'phone', 'whatsapp', 'is_active'])) }})" class="h-[34px] px-3.5 rounded-full border-[1.5px] border-line bg-white text-[13px] font-extrabold">Edit</button>
                    <form method="POST" action="{{ route('admin.contacts.destroy', $c) }}" data-confirm="Hapus kontak ini?" data-confirm-ok="Ya, hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button class="h-[34px] px-3.5 rounded-full bg-cream text-[13px] font-extrabold text-ink-soft">Hapus</button></form>
                </span>
            </li>
        @empty
            <li><x-empty-state title="Belum ada kontak darurat" text="Tambahkan minimal satu kontak resmi yang sudah diverifikasi." /></li>
        @endforelse
    </ul>
    <div class="max-md:hidden overflow-x-auto">
        <table class="table-wl min-w-[760px]">
            <thead><tr><th scope="col" class="w-12"><span class="sr-only">Urutan</span></th><th scope="col">Nama lembaga</th><th scope="col">Telepon</th><th scope="col">WhatsApp</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead>
            <tbody data-sortable data-sortable-url="{{ route('admin.contacts.reorder') }}">
                @forelse($contacts as $c)
                    <tr data-id="{{ $c->id }}">
                        <td><span data-handle class="cursor-grab text-ink-soft" aria-label="Seret untuk mengubah urutan"><x-icon name="grip" size="20" /></span></td>
                        <td class="font-extrabold">{{ $c->name }}<span class="block text-xs font-semibold text-ink-soft">{{ $c->description }}</span></td>
                        <td class="text-ink-soft num">{{ $c->phone ?: '-' }}</td>
                        <td class="text-ink-soft num">{{ $c->whatsapp ?: '-' }}</td>
                        <td>@if($c->is_active)<x-badge tone="sage" class="!text-xs">Tampil</x-badge>@else<x-badge class="!text-xs">Disembunyikan</x-badge>@endif</td>
                        <td>
                            <div class="flex gap-1.5">
                                <button type="button" x-on:click="edit({{ Js::from($c->only(['id', 'name', 'description', 'phone', 'whatsapp', 'is_active'])) }})" class="h-[34px] px-3 rounded-full border-[1.5px] border-line bg-white text-[13px] font-extrabold">Edit</button>
                                <form method="POST" action="{{ route('admin.contacts.destroy', $c) }}" data-confirm="Hapus kontak ini?" data-confirm-ok="Ya, hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button class="h-[34px] px-3 rounded-full bg-cream text-[13px] font-extrabold text-ink-soft">Hapus</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="Belum ada kontak darurat" text="Tambahkan minimal satu kontak resmi yang sudah diverifikasi." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="open" x-cloak class="scrim fixed inset-0 z-50 flex items-center justify-center p-4">
        <section role="dialog" aria-modal="true" aria-labelledby="ct-h" class="w-full max-w-lg bg-cream rounded-panel shadow-modal animate-pop p-5 sm:p-7" x-on:click.outside="open = false" x-trap.noscroll="open">
            <h2 id="ct-h" class="text-2xl mb-4" x-text="form.id ? 'Edit kontak' : 'Tambah kontak'"></h2>
            <form method="POST" :action="form.id ? '{{ url('admin/pengaturan/kontak') }}/' + form.id : '{{ route('admin.contacts.store') }}'" class="flex flex-col gap-3.5">
                @csrf
                <template x-if="form.id"><input type="hidden" name="_method" value="PUT"></template>
                <div><label class="field-label" for="ct-name">Nama lembaga</label><input id="ct-name" name="name" class="field" x-model="form.name" required></div>
                <div><label class="field-label" for="ct-desc">Deskripsi singkat</label><input id="ct-desc" name="description" class="field" x-model="form.description"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="field-label" for="ct-phone">Telepon</label><input id="ct-phone" name="phone" class="field" x-model="form.phone" inputmode="tel"></div>
                    <div><label class="field-label" for="ct-wa">WhatsApp</label><input id="ct-wa" name="whatsapp" class="field" x-model="form.whatsapp" inputmode="tel" placeholder="62812…"></div>
                </div>
                <label class="flex items-center gap-2.5 text-sm font-bold"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="w-5 h-5 accent-sand-deep">Tampilkan di halaman Bantuan Darurat</label>
                <div class="flex justify-end gap-2 mt-2"><button type="button" class="btn btn-outline btn-sm" x-on:click="open = false">Batal</button><button class="btn btn-sand btn-sm">Simpan</button></div>
            </form>
        </section>
    </div>
</section>
@endif

@if($tab === 'kata-kunci')
<section aria-labelledby="kk-h" class="card !rounded-[26px] p-5 sm:p-6 flex flex-col gap-4" x-data="{ editing: null }">
    <div class="flex flex-col gap-0.5">
        <h2 id="kk-h" class="text-[21px]">Kata kunci sensitif</h2>
        <span class="text-[13px] text-ink-soft leading-relaxed">Menandai pesan agar Guru BK cepat tanggap, tanpa memblokir. Tingkat 3 = kritis: murid langsung mendapat kontak darurat.</span>
    </div>
    <form method="POST" action="{{ route('admin.keywords.store') }}" class="grid grid-cols-2 sm:grid-cols-[1fr_160px_180px_auto] gap-3 items-end">
        @csrf
        <x-input name="phrase" label="Frasa baru" placeholder="contoh: gak kuat lagi" required class="col-span-2 sm:col-span-1" />
        <div><label for="kk-sev" class="field-label">Tingkat</label><select id="kk-sev" name="severity" class="field"><option value="1">1 · Perhatian</option><option value="2">2 · Serius</option><option value="3">3 · Kritis</option></select></div>
        <div><label for="kk-cat" class="field-label">Kategori</label><select id="kk-cat" name="category" class="field">@foreach(\App\Models\SensitiveKeyword::CATEGORIES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
        <button class="btn btn-sand btn-sm !h-12 max-sm:col-span-2">Tambah</button>
    </form>
    <ul class="md:hidden flex flex-col divide-y divide-line-soft">
        @foreach($keywords as $k)
            @php [$tone, $sevLabel] = \App\Models\SensitiveKeyword::SEVERITIES[$k->severity]; @endphp
            <li class="py-3 flex flex-col gap-2.5">
                <span class="flex items-start justify-between gap-3">
                    <span class="min-w-0 flex flex-col gap-1"><span class="font-extrabold wrap-anywhere">{{ $k->phrase }}</span><span class="text-xs text-ink-soft">{{ $k->categoryLabel() }} · @if($k->is_active)<span class="font-extrabold text-sage-ink2">Aktif</span>@else<span class="font-extrabold">Nonaktif</span>@endif</span></span>
                    <x-badge :tone="$tone" class="!text-xs flex-none">{{ $k->severity }} · {{ $sevLabel }}</x-badge>
                </span>
                <span class="flex gap-1.5">
                    <button type="button" x-on:click="editing = editing === {{ $k->id }} ? null : {{ $k->id }}" class="h-[34px] px-3.5 rounded-full border-[1.5px] border-line bg-white text-[13px] font-extrabold">Edit</button>
                    <form method="POST" action="{{ route('admin.keywords.destroy', $k) }}" data-confirm="Hapus kata kunci ini?" data-confirm-ok="Ya, hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button class="h-[34px] px-3.5 rounded-full bg-cream text-[13px] font-extrabold text-ink-soft">Hapus</button></form>
                </span>
                <div x-show="editing === {{ $k->id }}" x-cloak class="rounded-2xl bg-cream-light p-3">@include('admin.partials.keyword-edit-form', ['suffix' => 'm'])</div>
            </li>
        @endforeach
    </ul>
    <div class="max-md:hidden overflow-x-auto">
        <table class="table-wl min-w-[720px]">
            <thead><tr><th scope="col">Frasa (ternormalisasi)</th><th scope="col">Tingkat</th><th scope="col">Kategori</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead>
            <tbody>
                @foreach($keywords as $k)
                    <tr>
                        <td class="font-extrabold">{{ $k->phrase }}</td>
                        <td>@php $t = \App\Models\SensitiveKeyword::SEVERITIES[$k->severity]; @endphp<x-badge :tone="$t[0]" class="!text-xs">{{ $k->severity }} · {{ $t[1] }}</x-badge></td>
                        <td class="text-ink-soft">{{ $k->categoryLabel() }}</td>
                        <td>@if($k->is_active)<span class="text-xs font-extrabold text-sage-ink2">Aktif</span>@else<span class="text-xs font-extrabold text-ink-soft">Nonaktif</span>@endif</td>
                        <td>
                            <div class="flex gap-1.5 items-center">
                                <button type="button" x-on:click="editing = editing === {{ $k->id }} ? null : {{ $k->id }}" class="h-[34px] px-3 rounded-full border-[1.5px] border-line bg-white text-[13px] font-extrabold">Edit</button>
                                <form method="POST" action="{{ route('admin.keywords.destroy', $k) }}" data-confirm="Hapus kata kunci ini?" data-confirm-ok="Ya, hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button class="h-[34px] px-3 rounded-full bg-cream text-[13px] font-extrabold text-ink-soft">Hapus</button></form>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="editing === {{ $k->id }}" x-cloak>
                        <td colspan="5" class="bg-cream-light">
                            <div class="py-2">@include('admin.partials.keyword-edit-form', ['suffix' => 'd'])</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
@endsection

@push('scripts')
<script>
    function contactEditor() {
        return {
            open: false,
            form: {},
            edit(c) { this.form = c ? { ...c, is_active: !!c.is_active } : { id: null, name: '', description: '', phone: '', whatsapp: '', is_active: true }; this.open = true; },
        };
    }
</script>
@endpush
