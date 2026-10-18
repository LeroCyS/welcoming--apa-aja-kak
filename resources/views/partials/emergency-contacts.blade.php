{{-- Kartu kontak darurat (dipakai di /darurat, Mulai Cerita langkah 3, dan kartu emergency_info di chat). --}}
@php $contacts = $contacts ?? \App\Models\School::current()->emergencyContacts()->where('is_active', true)->get(); $compact = $compact ?? false; @endphp
{{-- Tampilan penuh memakai subgrid: ikon, nama, nomor, keterangan, dan tombol sejajar antar kartu walau panjang teksnya berbeda. --}}
<div class="grid {{ $compact ? 'grid-cols-1 gap-2.5' : 'grid-cols-[repeat(auto-fit,minmax(min(280px,100%),1fr))] gap-4' }}">
    @foreach($contacts as $c)
        <div class="bg-white border-[1.5px] border-danger-line {{ $compact ? 'rounded-card p-4 gap-3 flex flex-col' : 'rounded-card-l p-[22px] shadow-soft grid grid-rows-subgrid row-span-5 gap-y-1' }}">
            @if($compact)
                <div class="flex flex-col gap-1">
                    <span class="font-extrabold text-[15px]">{{ $c->name }}</span>
                    @if($c->description)<span class="text-[13px] text-ink-soft leading-snug">{{ $c->description }}</span>@endif
                </div>
            @else
                <span class="tile w-10 h-10 !rounded-xl bg-danger-tint text-danger mb-1"><x-icon name="phone" size="20" /></span>
                <span class="font-extrabold text-lg">{{ $c->name }}</span>
                <span class="num text-[26px] text-ink">{{ $c->phone ?: $c->whatsapp }}</span>
                <span class="text-[13px] text-ink-soft leading-snug">{{ $c->description }}</span>
            @endif
            <div class="flex gap-2 {{ $compact ? '' : 'self-end mt-2.5' }}">
                @if($c->telUrl())
                    <a href="{{ $c->telUrl() }}" class="flex-1 btn btn-danger {{ $compact ? '!h-11 !text-sm' : '!text-[15px]' }}"><x-icon name="phone" size="18" />Telepon</a>
                @elseif($c->phone)
                    <span class="flex-1 btn btn-danger opacity-60 {{ $compact ? '!h-11 !text-sm' : '!text-[15px]' }}" aria-disabled="true" title="Nomor resmi belum diisi admin">Nomor belum diisi</span>
                @endif
                @if($c->waUrl())
                    <a href="{{ $c->waUrl() }}" target="_blank" rel="noopener" class="flex-1 btn btn-outline {{ $compact ? '!h-11 !text-sm' : '!text-[15px]' }} !font-extrabold">WhatsApp</a>
                @endif
            </div>
        </div>
    @endforeach
</div>
