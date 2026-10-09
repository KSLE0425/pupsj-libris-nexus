<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — PUPSJ Libris</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="split-card">
    <div class="hero-side"></div>
    <div class="login-side">
        <div class="seal-circle">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="#F5E642" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/>
            </svg>
        </div>

        <h1 class="login-title">Forgot Password</h1>
        <p class="login-subtitle">Enter your email to receive a reset link</p>

        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        @if(session('success'))
            <div class="error-box" style="background:rgba(200,255,200,0.85);color:#1a6b1a;border-color:rgba(0,140,0,0.2);">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.forgot.send') }}" style="width:100%;">
            @csrf
            <input type="hidden" name="guard" value="{{ $guard }}">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="your@email.com">
            </div>
            <button type="submit" class="submit-btn">Send Reset Link</button>
        </form>

        <a href="{{ $loginRoute }}" class="back-link">← Back to Login</a>
    </div>
</div>
</body>
</html>