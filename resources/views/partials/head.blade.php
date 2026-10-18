<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#F5F1E4">
{{-- Judul tab: "Halaman · Welcoming". Halaman bisa memakai judul utuh lewat @section('full-title'), mis. landing page. --}}
@php
    // @section('title', '...') sudah di-escape Blade; di-decode dulu agar {{ }} di bawah tidak meng-escape dua kali (mis. "&amp;amp;").
    $wlPageTitle = html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $wlFullTitle = html_entity_decode(trim($__env->yieldContent('full-title')), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?: ($wlPageTitle ? $wlPageTitle.' · Welcoming' : 'Welcoming');
@endphp
<title>{{ $wlFullTitle }}</title>
<meta name="description" content="@yield('description', 'Welcoming: ruang cerita anonim untuk murid dan Guru BK. Cerita kapan saja, didengar tanpa dihakimi.')">
<meta property="og:site_name" content="Welcoming">
<meta property="og:title" content="{{ $wlFullTitle }}">
<meta property="og:description" content="@yield('description', 'Welcoming: ruang cerita anonim untuk murid dan Guru BK. Cerita kapan saja, didengar tanpa dihakimi.')">
<meta property="og:image" content="{{ asset('images/welcoming-mark.png') }}">
<link rel="icon" type="image/png" href="{{ asset('images/welcoming-mark.png') }}">
@php
    $wlUser = auth()->user();
    // Halaman yang dikunci PIN: bila tab/aplikasi baru dibuka (sessionStorage kosong), langsung minta PIN sebelum apa pun tampil.
    // Layar kunci (PIN / Keluar Cepat) tidak boleh menjalankan polling ke halaman murid yang sedang terkunci.
    $wlLocked = request()->routeIs('student.pin.*') || session(\App\Http\Middleware\EnsureNotQuickLocked::SESSION_KEY);
    $wlPinGuard = $wlUser?->isStudent() && $wlUser->studentProfile?->pin_enabled
        && in_array('pin.unlocked', request()->route()?->gatherMiddleware() ?? [], true);
@endphp
@if($wlPinGuard)
<script>
    try {
        if (!sessionStorage.getItem('wl-pin-tab')) {
            document.documentElement.style.visibility = 'hidden';
            location.replace(@js(route('student.pin.lock', ['ulang' => 1])) + '&kembali=' + encodeURIComponent(location.pathname + location.search));
        }
    } catch (e) { /* sessionStorage diblokir: tetap dilindungi batas idle di server */ }
</script>
@endif
<script>
    try { if (localStorage.getItem('wl-sidebar-mini') === '1') document.documentElement.classList.add('sb-mini'); } catch (e) {}
    window.wlToggleSidebar = function () {
        const mini = document.documentElement.classList.toggle('sb-mini');
        try { localStorage.setItem('wl-sidebar-mini', mini ? '1' : '0'); } catch (e) {}
    };
    window.Welcoming = {
        quickExitUrl: @js(config('welcoming.quick_exit_url')),
        quickExitBeacon: @js(route('quick-exit')),
        userId: @js(auth()->id()),
        role: @js($wlUser?->role),
        schoolId: @js($wlUser?->school_id),
        locked: @js((bool) $wlLocked),
        presence: @js($wlUser?->isCounselor() ? [
            'initial' => (bool) $wlUser->counselorProfile?->is_online,
            'toggleUrl' => route('counselor.presence.toggle'),
            'heartbeatUrl' => route('counselor.presence.heartbeat'),
            'interval' => config('welcoming.presence_heartbeat_seconds'),
        ] : null),
        counselorStatusUrl: @js($wlUser?->isStudent() && $wlUser->school_id && ! $wlLocked ? route('student.counselors.statuses') : null),
        pushKey: @js(config('webpush.vapid.public_key')),
        pushSubscribeUrl: @js($wlUser ? route('push.subscribe') : null),
        notificationsEnabled: @js($wlUser?->isStudent() ? (bool) ($wlUser->studentProfile?->notifications_enabled ?? true) : (bool) $wlUser),
        realtime: @js(filled(config('broadcasting.connections.reverb.key')) && config('broadcasting.default') === 'reverb'),
    };
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('head')
