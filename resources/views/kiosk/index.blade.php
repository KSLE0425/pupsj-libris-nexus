{{-- resources/views/kiosk/index.blade.php (login form) --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Login — PUPSJ Libris</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
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
        <p class="login-subtitle">PUPSJ Libris — Sign in to borrow or return books</p>

        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        @if(session('kiosk_success'))
            <div class="error-box" style="background:rgba(200,255,200,0.85);color:#1a6b1a;border-color:rgba(0,140,0,0.2);">
                {{ session('kiosk_success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('kiosk.login') }}" style="width:100%;">
            @csrf
            <div class="form-group">
                <label>Student Number / Employee ID</label>
                <input type="text" name="identifier" id="identifier"
                       value="{{ old('identifier') }}" required autofocus
                       placeholder="e.g. 2024-00001-SJ-0">
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" id="kiosk_login_password" name="password" required placeholder="••••••••">
                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('kiosk_login_password', this)" title="Show Password" aria-label="Toggle password visibility">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="submit-btn">Sign In</button>
        </form>

        <div style="text-align:center; margin-top:12px;">
            <a href="{{ route('kiosk.forgot-password') }}" style="color:#fff; font-size:0.8rem; text-decoration:none; font-weight:600; opacity:0.85;">Forgot Password?</a>
        </div>

        <p class="register-text">
            New here? <a href="{{ route('kiosk.register') }}">Create an account</a>
        </p>

        <a href="{{ route('kiosk.index') }}" class="back-link">← Back to Kiosk Menu</a>
    </div>
</div>

<script>
function togglePasswordVisibility(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.innerHTML = isPassword ? `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
        </svg>
    ` : `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
    `;
    btn.title = isPassword ? 'Hide Password' : 'Show Password';
}
</script>
</body>
</html>
