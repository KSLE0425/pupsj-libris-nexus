{{-- resources/views/faculty/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Login — PUPSJ Libris</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="split-card">

    {{-- Left: transparent, bg image shows through --}}
    <div class="hero-side"></div>

    {{-- Right: glass login panel --}}
    <div class="register-side">
        <div class="register-form-inner">

            <div style="display:flex;justify-content:center;margin-bottom:1rem;">
                <div class="seal-circle">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="#F5E642" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
            </div>

            <h1 class="login-title">Faculty Login</h1>
            <p class="login-subtitle">PUPSJ Libris Faculty Portal</p>

            @if($errors->any())
                <div class="error-box">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if(session('success'))
                <div style="display:flex;align-items:flex-start;gap:10px;background:#ecfdf5;border:1px solid #6ee7b7;border-left:4px solid #10b981;border-radius:10px;padding:13px 16px;margin-bottom:18px;width:100%;box-sizing:border-box;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5" style="flex-shrink:0;margin-top:1px;"><polyline points="20 6 9 17 4 12"/></svg>
                    <div>
                        <p style="margin:0 0 2px;font-size:0.88rem;font-weight:700;color:#065f46;">Account Created!</p>
                        <p style="margin:0;font-size:0.82rem;color:#047857;">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('faculty.login.post') }}" style="width:100%;">
                @csrf

                <div class="form-group">
                    <label>Employee ID</label>
                    <input type="text" name="employee_id" value="{{ old('employee_id') }}" required autofocus placeholder="Enter your employee ID">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="faculty_password" name="password" required placeholder="••••••••">
                        <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('faculty_password', this)" aria-label="Show password" title="Toggle password visibility">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Login</button>
            </form>

            <div style="text-align: center; margin-top: 12px;">
                <a href="{{ route('password.forgot.form', 'faculty') }}" style="color: #800000; font-size: 0.8rem; text-decoration: none;">Forgot Password?</a>
            </div>

            <a href="{{ route('login.selection') }}" class="back-link">← Back to selection</a>

        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    btn.innerHTML = isPassword ? `
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
        </svg>
    ` : `
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
    `;
}
</script>
</body>
</html>