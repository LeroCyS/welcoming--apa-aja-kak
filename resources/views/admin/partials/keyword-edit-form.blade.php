{{-- Form ubah kata kunci (dipakai di tabel desktop & kartu HP). --}}
<form method="POST" action="{{ route('admin.keywords.update', $k) }}" class="grid grid-cols-2 sm:grid-cols-[1fr_140px_170px_auto_auto] gap-3 items-end">
    @csrf @method('PUT')
    <div class="col-span-2 sm:col-span-1"><label class="field-label" for="kp-{{ $suffix }}-{{ $k->id }}">Frasa</label><input id="kp-{{ $suffix }}-{{ $k->id }}" name="phrase" value="{{ $k->phrase }}" class="field" required></div>
    <div><label class="field-label" for="ks-{{ $suffix }}-{{ $k->id }}">Tingkat</label><select id="ks-{{ $suffix }}-{{ $k->id }}" name="severity" class="field">@foreach(\App\Models\SensitiveKeyword::SEVERITIES as $s => [, $label])<option value="{{ $s }}" @selected($k->severity === $s)>{{ $s }} · {{ $label }}</option>@endforeach</select></div>
    <div><label class="field-label" for="kc-{{ $suffix }}-{{ $k->id }}">Kategori</label><select id="kc-{{ $suffix }}-{{ $k->id }}" name="category" class="field">
        @unless(array_key_exists($k->category, \App\Models\SensitiveKeyword::CATEGORIES))<option value="{{ $k->category }}" selected>{{ $k->category }}</option>@endunless
        @foreach(\App\Models\SensitiveKeyword::CATEGORIES as $key => $label)<option value="{{ $key }}" @selected($k->category === $key)>{{ $label }}</option>@endforeach
    </select></div>
    <label class="flex items-center gap-2 text-sm font-bold h-12"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($k->is_active) class="w-5 h-5 accent-sand-deep">Aktif</label>
    <button class="btn btn-sand btn-sm !h-12">Simpan</button>
</form>
