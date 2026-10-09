{{-- resources/views/login-selection.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUPSJ Libris — Welcome</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <style>
        /* Back to Home Button Styles */
        .back-home-wrapper {
            position: fixed;
            top: 24px;
            left: 24px;
            z-index: 100;
        }

        .back-home-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(128, 0, 0, 0.9);
            color: #fff;
            padding: 8px 16px;
            border-radius: 40px;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(245, 230, 66, 0.3);
            transition: all 0.2s ease;
        }

        .back-home-btn svg {
            stroke: #F5E642;
        }

        .back-home-btn:hover {
            background: var(--maroon, #800000);
            border-color: #F5E642;
            transform: translateX(-2px);
        }
    </style>
</head>
<body>

<!-- Back to Selection Button -->
<div class="back-home-wrapper">
    <a href="{{ url('/') }}" class="back-home-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Back to selection
    </a>
</div>

<div class="split-card">

    {{-- Left: transparent, bg image shows through --}}
    <div class="hero-side"></div>

    {{-- Right: glass panel --}}
    <div class="login-side">

        {{-- Book icon --}}
        <div class="seal-circle">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="#F5E642" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
            </svg>
        </div>

        <h1 class="login-title">Hi, PUPian!</h1>
        <p class="login-subtitle">Please click or tap your destination.</p>

        {{-- User button (Student) --}}
        <a href="{{ route('student.login') }}" class="portal-btn portal-btn-solid">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            Student
        </a>

        {{-- Faculty button --}}
        <a href="{{ route('faculty.login') }}" class="portal-btn portal-btn-solid">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            Faculty
        </a>

        {{-- Admin button --}}
        <a href="{{ route('admin.login') }}" class="portal-btn portal-btn-outline">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3zm0 4l4 1.78V11c0 3.28-2.27 6.35-4 7.44-1.73-1.09-4-4.16-4-7.44V7.78L12 6z"/>
            </svg>
            Admin
        </a>


        <p class="terms-text">
            By using this service, you agree to the PUP Online Services
            <a href="#">Terms of Use</a> and <a href="#">Privacy Statement</a>
        </p>
    </div>
</div>
</body>
</html>