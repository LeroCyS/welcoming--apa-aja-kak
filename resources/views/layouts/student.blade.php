{{-- Layout murid: topbar (menu utama + tombol keamanan + hamburger), HP tetap memakai bottom nav. Keluar Cepat selalu terlihat di topbar; Keluar Akun ada di menu hamburger & Profil. --}}
@php
    $user = auth()->user();
    $active = $active ?? trim($__env->yieldContent('active', 'beranda'));
    $unreadStories = \App\Models\Message::whereIn('conversation_id', \App\Models\Conversation::forStudent($user)->select('id'))
        ->where('sender_type', '!=', 'student')->whereNull('read_at')->distinct()->count('conversation_id');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body class="role-student min-h-screen bg-cream" data-quick-exit-shortcut>
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:p-3 focus:bg-white focus:rounded-xl">Lewati ke konten</a>
    <x-flash />
    <x-student-topbar :active="$active" :unread="$unreadStories" @class(['hidden lg:block' => ($hideBottomNav ?? false) && ! ($fullScreen ?? false)]) />
    @if($fullScreen ?? false)
    {{-- Ruang chat: topbar tetap ada, konten mengisi sisa tinggi layar di dalam container. --}}
    <main id="konten" class="{{ ($hideBottomNav ?? false) ? 'h-[calc(100dvh-76px)]' : 'h-[calc(100dvh-76px-96px)] lg:h-[calc(100dvh-76px)]' }} container-wl pt-3 lg:pt-5 pb-[max(12px,env(safe-area-inset-bottom))] lg:pb-5 flex flex-col overflow-hidden">
    @else
    <main id="konten" class="container-wl pt-5 {{ ($hideBottomNav ?? false) ? 'pb-0' : 'pb-32' }} lg:pt-7 lg:pb-12 flex flex-col gap-6 @yield('main-class')">
    @endif
        @hasSection('header')
            @yield('header')
        @else
            <header class="flex items-center gap-3 min-w-0 no-print">
                @hasSection('back')
                    <a href="@yield('back')" aria-label="Kembali" class="head-btn flex-none"><x-icon name="arrow-left" size="20" /></a>
                @endif
                <div class="flex flex-col gap-1 min-w-0">
                    <h1 class="text-[28px] lg:text-[34px] leading-tight">@yield('heading', trim($__env->yieldContent('title')))</h1>
                    @hasSection('subtitle')<p class="text-[15px] lg:text-base text-ink-soft">@yield('subtitle')</p>@endif
                </div>
            </header>
        @endif
        @yield('content')
    </main>
    @unless($hideBottomNav ?? false)<x-bottom-nav :active="$active" />@endunless
    @stack('modals')
    @stack('scripts')
</body>
</html>
