{{-- Pratinjau pesan di daftar percakapan: ikon garis (pesan non-teks) + potongan teks. --}}
@props(['icon' => null, 'prefix' => '', 'text' => ''])
{{ $prefix }}@if($icon)<x-icon :name="$icon" size="14" stroke="2.2" class="inline-block align-[-2px] mr-1" />@endif{{ $text }}
