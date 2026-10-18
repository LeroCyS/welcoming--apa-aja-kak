<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Welcoming</title>
    <link rel="icon" type="image/png" href="{{ asset('images/welcoming-mark.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-cream flex items-center justify-center px-5 py-12">
    <main class="w-full max-w-xl bg-white border border-line-soft rounded-panel shadow-lift p-8 sm:p-12 flex flex-col items-center text-center gap-4 animate-rise">
        <span class="w-36 h-36 rounded-full bg-cream-deep flex items-center justify-center">
            <img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="relative w-24 h-auto opacity-90">
        </span>
        <span class="inline-flex items-center h-7 px-3 rounded-full bg-sage-tint label-caps !text-[12px] text-sage-ink2 whitespace-nowrap">@yield('code')</span>
        <h1 class="text-[30px] leading-tight">@yield('heading')</h1>
        <p class="text-base leading-relaxed text-ink-soft max-w-md">@yield('message')</p>
        <div class="flex flex-wrap gap-3 justify-center mt-2">
            @hasSection('action')@yield('action')@else<a href="{{ url('/') }}" class="btn btn-primary">Kembali ke beranda</a>@endif
            <a href="{{ url('/darurat') }}" class="btn btn-soft text-danger border-danger-line">Bantuan Darurat</a>
        </div>
    </main>
</body>
</html>
