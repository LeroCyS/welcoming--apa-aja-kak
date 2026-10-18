<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-cream @yield('body-class')">
    <x-flash />
    @yield('content')
    @stack('scripts')
</body>
</html>
