@props(['topic'])
<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-1 rounded-full bg-cream text-xs font-extrabold whitespace-nowrap']) }}>{{ $topic->name }}</span>
