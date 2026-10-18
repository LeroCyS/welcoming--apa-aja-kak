@php
    $user = auth()->user();
    $active = $active ?? trim($__env->yieldContent('active', 'dashboard'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body class="role-admin min-h-screen bg-cream">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:p-3 focus:bg-white focus:rounded-xl">Lewati ke konten</a>
    <x-flash />
    <x-app-topbar role="admin" :active="$active" />
    <main id="konten" class="container-wl pt-6 pb-12 flex flex-col gap-5">
        <header class="flex flex-col gap-0.5 min-w-0">
            <h1 class="text-2xl lg:text-[28px] leading-tight">@yield('heading', trim($__env->yieldContent('title')))</h1>
            @hasSection('subtitle')<span class="text-sm text-ink-soft">@yield('subtitle')</span>@endif
        </header>
        @yield('content')
    </main>
    @stack('modals')
    @stack('scripts')
</body>
</html>
