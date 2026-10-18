{{-- Isian link lagu opsional (YouTube/Spotify), seperti menambahkan lagu di story. --}}
@props(['name' => 'song_url', 'value' => null, 'id' => 'song-url', 'label' => 'Tambahkan lagu (opsional)'])
<div {{ $attributes->except(['x-model'])->merge(['class' => 'flex flex-col gap-1.5']) }}>
    <label for="{{ $id }}" class="text-sm font-bold inline-flex items-center gap-1.5"><x-icon name="play" size="15" class="text-sage-deep" />{{ $label }}</label>
    <input id="{{ $id }}" name="{{ $name }}" type="url" inputmode="url" value="{{ $value }}" maxlength="500" placeholder="Tempel link lagu dari YouTube atau Spotify" class="field text-sm @error($name) is-invalid @enderror" {{ $attributes->only(['x-model']) }}>
    @error($name)<p class="field-error !mt-0">{{ $message }}</p>@enderror
</div>
