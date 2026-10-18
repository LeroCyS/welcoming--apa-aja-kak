@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'id' => null])
@php $id = $id ?? 'f-'.str_replace(['[', ']', '.'], '-', $name); $err = $errors->first($name); @endphp
<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    @if($label)<label for="{{ $id }}" class="field-label">{{ $label }}</label>@endif
    @if($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('class')->merge(['class' => 'field'.($err ? ' is-invalid' : ''), 'rows' => 4]) }} @if($err) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif>{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : old($name, $value) }}" {{ $attributes->except('class')->merge(['class' => 'field'.($err ? ' is-invalid' : '')]) }} @if($err) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif>
    @endif
    @if($hint && ! $err)<p class="mt-1.5 text-[13px] text-ink-soft">{{ $hint }}</p>@endif
    @if($err)<p id="{{ $id }}-err" class="field-error">{{ $err }}</p>@endif
</div>
