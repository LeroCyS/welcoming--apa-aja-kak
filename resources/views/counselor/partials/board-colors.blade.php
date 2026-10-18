{{-- Pilihan warna kolom kanban buatan guru. --}}
<fieldset class="flex items-center gap-2">
    <legend class="sr-only">Warna kolom</legend>
    <span class="text-xs font-extrabold text-ink-soft mr-1">Warna</span>
    @foreach(\App\Models\CaseBoardColumn::COLORS as $key => [$bg, $dot])
        <label class="cursor-pointer" title="{{ ucfirst($key) }}">
            <input type="radio" name="color" value="{{ $key }}" class="peer sr-only" @checked($current === $key)>
            <span class="block w-6 h-6 rounded-full {{ $dot }} ring-2 ring-offset-2 ring-transparent peer-checked:ring-ink peer-focus-visible:ring-ink-soft transition"></span>
        </label>
    @endforeach
</fieldset>
