<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Library Kiosk') — PUPSJ Libris Nexus</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f2f2f2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .kiosk-header {
            background: #800000;
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
            flex-shrink: 0;
        }
        .kiosk-header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }
        .kiosk-header-brand img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border-radius: 6px;
            flex-shrink: 0;
        }
        .kiosk-header-text h1 {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
        }
        .kiosk-header-text p {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.6);
            margin: 0;
        }
        .kiosk-header-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .kiosk-ln-badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            opacity: 0.85;
            flex-shrink: 0;
        }
        .kiosk-ln-badge span {
            font-size: 0.5rem;
            font-weight: 700;
            color: rgba(255,255,255,0.65);
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .kiosk-user-badge {
            font-size: 0.78rem;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
        }
        .kiosk-logout-btn {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.25);
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
        }
        .kiosk-logout-btn:hover { background: rgba(255,255,255,0.25); }

        .kiosk-main {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 20px;
        }
        .kiosk-content {
            width: 100%;
            max-width: 900px;
        }

        /* Flash messages */
        .flash-success {
            background: #e8f5e9; color: #2e7d32;
            border: 1px solid #a5d6a7; border-radius: 8px;
            padding: 12px 18px; margin-bottom: 20px;
            font-size: 0.88rem; font-weight: 600;
        }
        .flash-error {
            background: #fdecea; color: #c62828;
            border: 1px solid #ef9a9a; border-radius: 8px;
            padding: 12px 18px; margin-bottom: 20px;
            font-size: 0.88rem; font-weight: 600;
        }
        .field-error { color: #c62828; font-size: 0.78rem; margin-top: 4px; }

        /* Form card */
        .k-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,.1);
            overflow: hidden;
        }
        .k-card-head {
            background: #800000;
            padding: 20px 28px;
            color: #fff;
            font-size: 1.05rem;
            font-weight: 700;
        }
        .k-card-head p {
            color: rgba(255,255,255,0.65);
            font-size: 0.78rem;
            font-weight: 400;
            margin-top: 3px;
        }
        .k-card-body { padding: 28px; }

        /* Inputs */
        .k-label { display: block; font-size: 0.82rem; font-weight: 600; color: #444; margin-bottom: 5px; }
        .k-input {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s;
        }
        .k-input:focus { border-color: #800000; }
        .k-form-group { margin-bottom: 18px; }

        /* Buttons */
        .k-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: #800000;
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 700;
            padding: 14px 36px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
        }
        .k-btn:hover { background: #660000; }
        .k-btn-outline {
            background: transparent;
            color: #800000;
            border: 2px solid #800000;
        }
        .k-btn-outline:hover { background: #800000; color: #fff; }
        .k-btn-full { width: 100%; }
        .k-link { color: #800000; font-size: 0.82rem; font-weight: 600; text-decoration: none; }
        .k-link:hover { text-decoration: underline; }

        @media (max-width: 600px) {
            .kiosk-header { padding: 0 16px; height: 56px; }
            .kiosk-main { padding: 24px 12px; }
            .k-card-body { padding: 20px 16px; }
        }
    </style>
    @stack('styles')
</head>
<body>
<header class="kiosk-header">
    <div class="kiosk-header-brand">
        <img src="{{ asset('images/pup-logo.png') }}" alt="PUP Logo"
             onerror="this.style.display='none'">
        <div class="kiosk-header-text">
            <h1>PUPSJ Libris</h1>
            <p>Library Kiosk Terminal</p>
        </div>
    </div>
    <div class="kiosk-header-right">
        @hasSection('kiosk-user')
            @yield('kiosk-user')
        @endif
    </div>
</header>

<main class="kiosk-main">
    <div class="kiosk-content">
        @if(session('kiosk_success'))
            <div class="flash-success">{{ session('kiosk_success') }}</div>
        @endif
        @if(session('kiosk_error'))
            <div class="flash-error">{{ session('kiosk_error') }}</div>
        @endif

        @yield('content')
    </div>
</main>

@stack('scripts')
</body>
</html>
