<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

{{-- <link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png"> --}}

    <link
        rel="icon"
        href="{{ asset('assets/img/app__logo.jpg') }}"
        sizes="any"
    >

    <link
        rel="icon"
        href="{{ asset('assets/img/app__logo.jpg') }}"
        type="image/svg+xml"
    >

    <link
        rel="apple-touch-icon"
        href="{{ asset('assets/img/app__logo.jpg') }}"
    >

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
.auth-field input {
    background: rgba(255, 255, 255, 0.035) !important;
    border-color: rgba(255, 255, 255, 0.09) !important;
    color: white !important;
    border-radius: 0.75rem !important;
}

.auth-field input::placeholder {
    color: rgba(255, 255, 255, 0.22) !important;
}

.auth-field input:focus {
    border-color: rgba(168, 85, 247, 0.55) !important;
    box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.10) !important;
}

.auth-field label {
    color: rgba(255, 255, 255, 0.58) !important;
}
</style>

@fluxAppearance
