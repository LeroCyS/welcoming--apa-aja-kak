@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center gap-1.5">
        @if ($paginator->onFirstPage())
            <span class="w-11 h-11 rounded-full flex items-center justify-center text-ink-soft/50" aria-disabled="true"><x-icon name="chevron-left" size="18" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya" class="w-11 h-11 rounded-full bg-white border border-line flex items-center justify-center text-ink"><x-icon name="chevron-left" size="18" /></a>
        @endif
        <span class="px-2 text-sm font-bold text-ink">Hal. {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya" class="w-11 h-11 rounded-full bg-white border border-line flex items-center justify-center text-ink"><x-icon name="chevron-right" size="18" /></a>
        @else
            <span class="w-11 h-11 rounded-full flex items-center justify-center text-ink-soft/50" aria-disabled="true"><x-icon name="chevron-right" size="18" /></span>
        @endif
    </nav>
@endif
