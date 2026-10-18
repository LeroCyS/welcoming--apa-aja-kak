{{-- Ilustrasi orang bergaya doodle (bahu ke atas), sama dengan kolase hero landing. --}}
@props(['skin' => '#F1CBA8', 'hair' => '#3E3530', 'hairStyle' => 'long', 'shirt' => '#FBEDE9'])
<svg {{ $attributes->merge(['aria-hidden' => 'true']) }} viewBox="0 0 120 120" fill="none" stroke="#3E3530" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
    @if($hairStyle === 'long')<path d="M30 54c-2-28 12-40 30-40s32 12 30 40l3 40c-7 5-16 5-21 0V56H48v38c-5 5-14 5-21 0z" fill="{{ $hair }}"/>@endif
    <path d="M12 122c4-28 23-40 48-40s44 12 48 40z" fill="{{ $shirt }}"/>
    <path d="M50 70v14c0 6 20 6 20 0V70" fill="{{ $skin }}"/>
    <ellipse cx="60" cy="50" rx="24" ry="27" fill="{{ $skin }}"/>
    @if($hairStyle === 'short')<path d="M35 46c-1-19 11-30 26-30 14 0 26 10 25 28-7-7-17-9-29-7-9 1-16 4-22 9z" fill="{{ $hair }}"/>@endif
    @if($hairStyle === 'long')<path d="M36 46c3-15 13-22 24-22s21 7 24 22c-11-2-19-7-24-13-5 6-13 11-24 13z" fill="{{ $hair }}"/>@endif
    @if($hairStyle === 'curly')<path d="M34 48c-6-6-4-16 3-19-1-8 7-14 14-11 4-6 14-6 18 0 7-3 15 3 14 11 7 3 9 13 3 19-4-7-12-10-19-8-6-4-14-4-20 0-6-2-11 1-13 8z" fill="{{ $hair }}"/>@endif
    <path d="M49 53q3.5-3.5 7 0M64 53q3.5-3.5 7 0"/>
    <path d="M51 62q9 9 18 0z" fill="#fff"/>
    <circle cx="45" cy="60" r="3.5" fill="#D4705F" stroke="none" opacity=".35"/><circle cx="75" cy="60" r="3.5" fill="#D4705F" stroke="none" opacity=".35"/>
</svg>
