<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>

    <meta charset="utf-8">

    <title>
        {{ $title ?? 'Clear Code Web Solutions' }}
    </title>

    <meta name="description"
        content="{{ $description ?? 'Clear Code Web Solutions creates modern, high-performance websites and digital solutions.' }}">

    <meta name="keywords"
        content="web design, web development, websites, digital solutions, Laravel, Clear Code Web Solutions">

    <meta name="author" content="Clear Code Web Solutions">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    {{-- Favicon --}}

    <link rel="icon" href="{{ asset('assets/img/app__logo.jpg') }}" sizes="any">

    <link rel="icon" href="{{ asset('assets/img/app__logo.jpg') }}" type="image/svg+xml">

    <link rel="apple-touch-icon" href="{{ asset('assets/img/app__logo.jpg') }}">

    <link rel="manifest" href="{{ asset('assets/img/favicon/manifest.webmanifest') }}">

    {{-- Font Awesome --}}

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

    {{-- Open Graph --}}

    <meta property="og:title" content="{{ $og_title ?? ($title ?? 'Clear Code Web Solutions') }}">

    <meta property="og:description"
        content="{{ $og_description ?? ($description ?? 'Modern, high-performance websites and digital solutions.') }}">

    <meta property="og:url" content="{{ url()->current() }}">

    <meta property="og:type" content="website">

    @yield('og_image')

    {{-- Styles --}}

    <link rel="stylesheet" type="text/css" href="{{ asset('css/plugins-demo.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('css/main-demo.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('css/navbar.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('css/clearcode.css') }}">

    {{-- Browser Color --}}

    <meta name="theme-color" content="#B247B2">
    <meta name="msapplication-navbutton-color" content="#B247B2">
    <meta name="apple-mobile-web-app-status-bar-style" content="#B247B2">

    {{-- Vite --}}

<script>
    (() => {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        if (!timezone) {
            return;
        }

        const current = document.cookie
            .split('; ')
            .find(row => row.startsWith('client_timezone='))
            ?.split('=')[1];

        if (decodeURIComponent(current ?? '') !== timezone) {
            document.cookie =
                `client_timezone=${encodeURIComponent(timezone)}; path=/; max-age=${60 * 60 * 24 * 365}; SameSite=Lax`;

            window.location.reload();
        }
    })();
</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Page Styles --}}

    @stack('styles')

    {{-- Livewire --}}

    @livewireStyles

    

</head>

<body>

    {{-- Initial Page Loader --}}

    <x-home.loader />

    {{-- Navbar --}}

    <x-home.navbar />

    {{-- Page Content --}}

    @if (isset($slot) && trim($slot) !== '')
        {{ $slot }}
    @else
        @yield('content')
    @endif

    {{-- Footer --}}

    <x-home.footer />

    <x-home.to-top />


    {{-- Scripts --}}

    <script src="{{ asset('javascript/js/demo/jquery.min.js') }}"></script>

    <script src="{{ asset('javascript/js/demo/modernizr.js') }}"></script>

    <script src="{{ asset('javascript/js/demo/jquery.ajaxchimp.min.js') }}"></script>

    <script src="{{ asset('javascript/js/demo/demo.js') }}"></script>

    <script src="{{ asset('javascript/js/navbar.js') }}"></script>

    @stack('scripts')

    @livewireScripts

</body>

</html>
