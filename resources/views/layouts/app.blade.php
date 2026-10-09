<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#800000">
    <title>@yield('title', 'PUPSJ Libris Nexus') — PUPSJ Libris Nexus</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pup-maroon: #800000;
            --pup-maroon-dark: #5a0000;
            --pup-maroon-deeper: #3a0000;
            --pup-gold: #FFC72C;
            --pup-gold-dark: #e6b328;
            --pup-gold-pale: #fef7e0;
            --bg-cream: #fdfbf7;
            --bg-soft: #f8f6f2;
            --text-dark: #1a1a2e;
            --text-mid: #4a4a5a;
            --text-light: #6b7280;
            --font-serif: 'Playfair Display', Georgia, serif;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-cream);
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        img { max-width: 100%; }
        .app-main { max-width: 1320px; margin: 0 auto; min-height: 60vh; }
    </style>
    @stack('styles')
</head>
<body>

@include('partials.public-nav')

<main class="app-main">
    @yield('content')
</main>

@include('partials.public-footer')

@stack('scripts')
</body>
</html>
