{{-- Layout Guru BK: topbar ringkas (menu utama + hamburger), konten di tengah. --}}
@php
    $user = auth()->user();
    $active = $active ?? trim($__env->yieldContent('active', 'dashboard'));
    $inboxBadge = \App\Models\Conversation::where('counselor_id', $user->id)->where('status', '!=', 'resolved')
        ->whereNotNull('last_student_message_at')
        ->where(fn ($q) => $q->whereNull('last_counselor_message_at')->orWhereColumn('last_student_message_at', '>', 'last_counselor_message_at'))->count();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body class="role-counselor min-h-screen bg-cream">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:p-3 focus:bg-white focus:rounded-xl">Lewati ke konten</a>
    <x-flash />
    <x-app-topbar role="counselor" :active="$active" :badge="$inboxBadge" />
    <main id="konten" class="container-wl pt-6 pb-12 flex flex-col gap-5 @yield('main-class')">
        @hasSection('header')
            @yield('header')
        @else
            <header class="flex items-center gap-3 min-w-0 no-print">
                @unless($active === 'dashboard' && ! $__env->hasSection('back'))
                    <x-back-button :href="$__env->hasSection('back') ? trim($__env->yieldContent('back')) : null" :fallback="route('counselor.dashboard')" />
                @endunless
                <div class="flex flex-col gap-0.5 min-w-0">
                    <h1 class="text-2xl lg:text-[28px] leading-tight">@yield('heading', trim($__env->yieldContent('title')))</h1>
                    @hasSection('subtitle')<span class="text-sm text-ink-soft">@yield('subtitle')</span>@endif
                </div>
            </header>
        @endif
        @yield('content')
    </main>
    @stack('modals')
    @stack('scripts')
</body>
</html>
