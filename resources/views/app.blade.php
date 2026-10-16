<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
        <title>{{ config('app.name', 'Turget.id') }} — Platform Ekosistem Wisata Terverifikasi Indonesia</title>
        <meta name="description" content="Platform ekosistem wisata terverifikasi #1 di Indonesia. Temukan tour guide lokal berlisensi, paket petualangan autentik, penginapan, dan transportasi aman dengan proteksi rekening bersama (escrow).">

        <!-- Favicon & Mobile Web App Manifest -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
        <meta name="theme-color" content="#07121D">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

        <!-- OpenGraph / Social Share Preview (WhatsApp, Telegram, Facebook, LinkedIn) -->
        <meta property="og:site_name" content="Turget.id">
        <meta property="og:type" content="website">
        <meta property="og:title" content="Turget.id — Platform Ekosistem Wisata Terverifikasi Indonesia">
        <meta property="og:description" content="Temukan tour guide lokal berlisensi, paket petualangan autentik, penginapan unik, dan transportasi dengan jaminan rekening bersama (escrow).">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ asset('images/destinations/bali_hero.jpg') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Turget.id — Platform Ekosistem Wisata Terverifikasi Indonesia">
        <meta name="twitter:description" content="Temukan tour guide lokal berlisensi, paket petualangan autentik, penginapan unik, dan transportasi dengan jaminan rekening bersama (escrow).">
        <meta name="twitter:image" content="{{ asset('images/destinations/bali_hero.jpg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Schema.org Structured Data -->
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => url('/') . '/#organization',
                    'name' => 'Turget.id',
                    'url' => url('/'),
                    'logo' => asset('favicon.svg'),
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => url('/') . '/#website',
                    'url' => url('/'),
                    'name' => 'Turget.id',
                    'publisher' => [
                        '@id' => url('/') . '/#organization',
                    ],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => url('/destinations') . '?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-inertia::head />
    </head>
    <body class="turget-body">
        <x-inertia::app />
    </body>
</html>
