{{-- resources/views/kiosk/portal.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Kiosk — PUPSJ Libris</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <style>
        .portal-choices { width:100%; display:flex; flex-direction:column; gap:12px; margin-top:6px; }
        .portal-choice {
            display:flex; align-items:center; gap:16px;
            padding:16px 18px; border-radius:12px;
            background:rgba(255,255,255,0.08); border:1.5px solid rgba(255,255,255,0.18);
            text-decoration:none; color:white; cursor:pointer;
            transition:all .25s;
        }
        .portal-choice:hover {
            background:rgba(245,230,66,0.14); border-color:rgba(245,230,66,0.55);
            transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,0.25);
        }
        .portal-choice-icon {
            width:46px; height:46px; border-radius:11px;
            background:rgba(245,230,66,0.13); border:1px solid rgba(245,230,66,0.2);
            display:flex; align-items:center; justify-content:center; flex-shrink:0;
            transition:background .25s;
        }
        .portal-choice:hover .portal-choice-icon { background:rgba(245,230,66,0.22); }
        .portal-choice-title { font-weight:700; font-size:0.97rem; line-height:1.2; margin-bottom:3px; color:#fff; }
        .portal-choice-sub   { font-size:0.76rem; color:rgba(255,255,255,0.6); font-weight:400; }
    </style>
</head>
<body>
<div class="split-card">

    <div class="hero-side"></div>

    <div class="login-side">

        <div class="seal-circle">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="#F5E642" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
            </svg>
        </div>

        <h1 class="login-title">Library Kiosk</h1>
        <p class="login-subtitle">PUPSJ Libris — Self-Service Terminal</p>

        @if(session('kiosk_success'))
            <div class="error-box" style="background:rgba(200,255,200,0.85);color:#1a6b1a;border-color:rgba(0,140,0,0.2);">
                {{ session('kiosk_success') }}
            </div>
        @endif

        <div class="portal-choices">
            <a href="{{ route('kiosk.sign-in') }}" class="portal-choice">
                <div class="portal-choice-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F5E642" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                </div>
                <div>
                    <div class="portal-choice-title">Sign In</div>
                    <div class="portal-choice-sub">Log in to borrow books</div>
                </div>
            </a>

            <a href="{{ route('kiosk.register') }}" class="portal-choice">
                <div class="portal-choice-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F5E642" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                </div>
                <div>
                    <div class="portal-choice-title">Create Account</div>
                    <div class="portal-choice-sub">Register as a new library patron</div>
                </div>
            </a>

            <a href="{{ route('kiosk.return-public') }}" class="portal-choice">
                <div class="portal-choice-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F5E642" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><polyline points="9 11 12 14 18 8"/></svg>
                </div>
                <div>
                    <div class="portal-choice-title">Return a Book</div>
                    <div class="portal-choice-sub">Return without logging in</div>
                </div>
            </a>
        </div>

    </div>
</div>
</body>
</html>
