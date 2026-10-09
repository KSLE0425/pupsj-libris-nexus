{{-- resources/views/admin/verify-otp.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Verification — PUPSJ Libris</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <style>
        .otp-inputs {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin: 24px 0 8px;
        }
        .otp-digit {
            width: 60px;
            height: 64px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 800;
            font-family: 'Poppins', sans-serif;
            border: 2px solid #d1d5db;
            border-radius: 10px;
            color: #800000;
            outline: none;
            padding: 0;
            line-height: 1;
            transition: border-color 0.18s, box-shadow 0.18s;
            -moz-appearance: textfield;
        }
        .otp-digit:focus {
            border-color: #800000;
            box-shadow: 0 0 0 3px rgba(128,0,0,0.12);
        }
        .otp-digit::-webkit-inner-spin-button,
        .otp-digit::-webkit-outer-spin-button { -webkit-appearance: none; }
        .otp-timer {
            text-align: center;
            font-size: 0.78rem;
            color: #888;
            margin-bottom: 20px;
        }
        .otp-timer span { font-weight: 700; color: #800000; }
        .resend-link {
            display: none;
            text-align: center;
            font-size: 0.82rem;
            margin-bottom: 20px;
        }
        .resend-link a { color: #800000; font-weight: 600; text-decoration: none; }
        .resend-link a:hover { text-decoration: underline; }
        .resend-link.visible { display: block; }
    </style>
</head>
<body>
<div class="split-card">
    <div class="hero-side"></div>

    <div class="login-side">
        <div class="seal-circle">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="#F5E642" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3zm0 4l4 1.78V11c0 3.28-2.27 6.35-4 7.44-1.73-1.09-4-4.16-4-7.44V7.78L12 6z"/>
            </svg>
        </div>

        <h1 class="login-title">Verify Identity</h1>
        <p class="login-subtitle">A 6-digit code was sent to your admin email</p>

        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif
        @if(session('otp_resent'))
            <div class="error-box" style="background:rgba(200,255,200,0.85);color:#1a6b1a;border-color:rgba(0,140,0,0.2);">
                A new code was sent to your email.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.otp.verify') }}" id="otpForm" style="width:100%;">
            @csrf
            {{-- Hidden single input that collects the full code --}}
            <input type="hidden" name="otp_code" id="otpHidden">

            <div class="otp-inputs">
                @for($i = 1; $i <= 6; $i++)
                    <input type="number" class="otp-digit" id="d{{ $i }}" maxlength="1" min="0" max="9"
                           inputmode="numeric" autocomplete="one-time-code" tabindex="{{ $i }}">
                @endfor
            </div>

            <div class="otp-timer" id="timerDisplay">Code expires in <span id="countdown">10:00</span></div>
            <div class="resend-link" id="resendRow">
                Code expired? <a href="{{ route('admin.otp.resend') }}">Resend code</a>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn" disabled>Verify Code</button>
        </form>

        <a href="{{ route('admin.login') }}" class="back-link">← Back to Login</a>
    </div>
</div>

<script>
(function () {
    const digits = document.querySelectorAll('.otp-digit');
    const hidden  = document.getElementById('otpHidden');
    const submitBtn = document.getElementById('submitBtn');

    function collectCode() {
        const code = Array.from(digits).map(d => d.value).join('');
        hidden.value = code;
        submitBtn.disabled = code.length !== 6;
    }

    digits.forEach(function (inp, idx) {
        inp.addEventListener('input', function () {
            // Strip non-numeric and keep only first char
            inp.value = inp.value.replace(/\D/g, '').slice(-1);
            collectCode();
            if (inp.value && idx < digits.length - 1) {
                digits[idx + 1].focus();
            }
        });
        inp.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !inp.value && idx > 0) {
                digits[idx - 1].focus();
            }
        });
        inp.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            pasted.split('').forEach(function (ch, i) {
                if (digits[i]) digits[i].value = ch;
            });
            collectCode();
            const nextEmpty = Array.from(digits).findIndex(d => !d.value);
            if (nextEmpty !== -1) digits[nextEmpty].focus();
            else digits[digits.length - 1].focus();
        });
    });

    // Focus first digit on load
    if (digits[0]) digits[0].focus();

    // Countdown timer — 10 minutes
    let total = 600;
    const countdownEl = document.getElementById('countdown');
    const timerRow    = document.getElementById('timerDisplay');
    const resendRow   = document.getElementById('resendRow');

    const timer = setInterval(function () {
        total--;
        if (total <= 0) {
            clearInterval(timer);
            countdownEl.textContent = '0:00';
            timerRow.style.display  = 'none';
            resendRow.classList.add('visible');
            return;
        }
        const m = Math.floor(total / 60);
        const s = String(total % 60).padStart(2, '0');
        countdownEl.textContent = m + ':' + s;
        if (total <= 60) countdownEl.style.color = '#dc2626';
        // Show resend link after 60 seconds (540 remaining)
        if (total === 540) resendRow.classList.add('visible');
    }, 1000);

    // Auto-submit when all 6 filled
    document.getElementById('otpForm').addEventListener('submit', function () {
        collectCode();
    });
})();
</script>
</body>
</html>
