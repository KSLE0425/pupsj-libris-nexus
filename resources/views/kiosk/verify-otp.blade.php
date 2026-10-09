{{-- resources/views/kiosk/verify-otp.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code — PUPSJ Libris Kiosk</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="split-card">

    <div class="hero-side"></div>

    <div class="login-side">

        <div class="seal-circle">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="#F5E642" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
        </div>

        <h1 class="login-title">Enter Verification Code</h1>
        <p class="login-subtitle">Check your email for the 6-digit code — valid for 10 minutes</p>

        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('kiosk.verify-otp.post') }}" style="width:100%;">
            @csrf
            <div class="form-group">
                <label>Verification Code</label>
                <input type="text" name="code" id="code"
                       value="{{ old('code') }}" required autofocus
                       maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                       placeholder="000000"
                       style="font-size:1.6rem; letter-spacing:10px; text-align:center;">
            </div>
            <button type="submit" class="submit-btn">Verify Code</button>
        </form>

        <a href="{{ route('kiosk.forgot-password') }}" class="back-link">← Resend code</a>
    </div>
</div>
</body>
</html>
