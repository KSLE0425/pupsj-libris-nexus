{{-- resources/views/kiosk/forgot-password.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — PUPSJ Libris Kiosk</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="split-card">

    <div class="hero-side"></div>

    <div class="login-side">

        <div class="seal-circle">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="#F5E642" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
            </svg>
        </div>

        <h1 class="login-title">Reset Password</h1>
        <p class="login-subtitle">Enter your Student Number or Employee ID to receive a verification code</p>

        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        @if(session('kiosk_error'))
            <div class="error-box">{{ session('kiosk_error') }}</div>
        @endif

        <form method="POST" action="{{ route('kiosk.send-otp') }}" style="width:100%;">
            @csrf
            <div class="form-group">
                <label>Student Number / Employee ID</label>
                <input type="text" name="identifier" id="identifier"
                       value="{{ old('identifier') }}" required autofocus
                       placeholder="e.g. 2024-00001-SJ-0">
            </div>
            <button type="submit" class="submit-btn">Send Verification Code</button>
        </form>

        <a href="{{ route('kiosk.sign-in') }}" class="back-link">← Back to Login</a>
    </div>
</div>
</body>
</html>
