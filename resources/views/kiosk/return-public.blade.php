{{-- resources/views/kiosk/return-public.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return a Book — PUPSJ Libris Kiosk</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <style>
        /* ── Scanner card ── */
        .scan-card {
            width: 100%;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,0.25);
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .scan-card-head {
            background: rgba(128,0,0,0.55);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 18px 22px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 3px solid #FFC72C;
        }
        .scan-card-head h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
        }
        .scan-card-head p {
            font-family: 'Poppins', sans-serif;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.7);
            margin: 3px 0 0;
        }
        .scan-card-body {
            padding: 30px 22px 26px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        /* Pulsing icon */
        .scan-icon-wrap {
            width: 74px; height: 74px;
            border-radius: 50%;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.25); }
            50%       { box-shadow: 0 0 0 14px rgba(255,255,255,0); }
        }

        /* Hint text */
        .scan-hint {
            font-family: 'Poppins', sans-serif;
            font-size: 0.92rem;
            font-weight: 600;
            color: #fff;
            text-align: center;
            text-shadow: 0 1px 4px rgba(0,0,0,0.3);
        }
        .scan-hint small {
            display: block;
            font-size: 0.72rem;
            font-weight: 400;
            color: rgba(255,255,255,0.65);
            margin-top: 3px;
        }

        /* Live barcode display */
        .scan-barcode-display {
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            padding: 9px 22px;
            letter-spacing: 0.12em;
            min-width: 180px;
            text-align: center;
        }

        /* Status badge */
        .scan-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            border-radius: 20px;
            padding: 5px 14px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .scan-status-badge.scanning  { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
        .scan-status-badge.processing{ background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .scan-status-badge.error-badge { background: rgba(128,0,0,0.07); color: #800000; border-color: rgba(128,0,0,0.2); }
        .status-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #22c55e;
            animation: blink 1.2s infinite;
        }
        .scan-status-badge.scanning  .status-dot { background: #f59e0b; animation: none; }
        .scan-status-badge.processing .status-dot { background: #3b82f6; animation: none; }
        .scan-status-badge.error-badge .status-dot { background: #800000; animation: none; }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.2; }
        }

        /* Hidden scanner input */
        .scan-input-hidden {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 1px; height: 1px;
            overflow: hidden;
        }

        /* ── Result overlays ── */
        .result-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(3px);
            animation: fadeInO 0.2s ease forwards;
        }
        .result-overlay.show { display: flex; }
        .result-overlay.hiding { animation: fadeOutO 0.3s ease forwards; }
        @keyframes fadeInO  { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeOutO { to   { opacity: 0; } }

        .result-card {
            background: #fff;
            border-radius: 20px;
            padding: 44px 48px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.28);
            max-width: 400px;
            width: calc(100% - 32px);
            animation: popIn 0.25s cubic-bezier(.34,1.56,.64,1);
        }
        @keyframes popIn {
            from { transform: scale(0.75); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }
        .result-icon {
            width: 72px; height: 72px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
        }
        .result-icon.success { background: #dcfce7; }
        .result-icon.error   { background: rgba(128,0,0,0.1); }
        .result-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 8px;
        }
        .result-title.success { color: #15803d; }
        .result-title.error   { color: #800000; }
        .result-msg {
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            color: #555;
            line-height: 1.5;
            margin: 0 0 20px;
        }
        .result-dismiss {
            display: inline-block;
            background: #f3f4f6;
            color: #666;
            font-family: 'Poppins', sans-serif;
            font-size: 0.74rem;
            font-weight: 600;
            padding: 6px 18px;
            border-radius: 20px;
        }
        .result-timer {
            display: block;
            font-family: 'Poppins', sans-serif;
            font-size: 0.72rem;
            color: #aaa;
            margin-top: 8px;
        }
    </style>
</head>
<body>
<div class="split-card">
    <div class="hero-side"></div>

    <div class="login-side">
        <div class="seal-circle">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="#F5E642" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
            </svg>
        </div>

        <h1 class="login-title" style="color:#fff;text-shadow:0 2px 8px rgba(0,0,0,0.4);">Return a Book</h1>
        <p class="login-subtitle" style="color:rgba(255,255,255,0.75);">Point your barcode at the scanner to return</p>

        {{-- Smart scanner card --}}
        <div class="scan-card">
            <div class="scan-card-head">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#FFC72C" stroke-width="1.8">
                    <path d="M3 5h2M3 12h2M3 19h2M7 5h2M7 12h2M7 19h2M11 5h2M11 12h2M11 19h2M15 5h2M15 12h2M15 19h2M19 5h2M19 12h2M19 19h2"/>
                </svg>
                <div>
                    <h2>Scan Book Barcode</h2>
                    <p>The system will automatically process the return</p>
                </div>
            </div>
            <div class="scan-card-body">
                <div class="scan-icon-wrap" id="scanIconWrap">
                    <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.9)" stroke-width="1.6">
                        <path d="M3 5h2M3 12h2M3 19h2M7 5h2M7 12h2M7 19h2M11 5h2M11 12h2M11 19h2M15 5h2M15 12h2M15 19h2M19 5h2M19 12h2M19 19h2"/>
                    </svg>
                </div>
                <div class="scan-hint" id="scanHint">
                    Scan your book's barcode now
                    <small>Hold the barcode in front of the scanner</small>
                </div>
                <div class="scan-barcode-display" id="scanBarcodeDisplay" style="display:none;"></div>
                <div class="scan-status-badge" id="scanStatusBadge">
                    <span class="status-dot"></span>
                    Ready to Scan
                </div>

                {{-- Hidden form — scanner populates & submits automatically --}}
                <form method="POST" action="{{ route('kiosk.return-public.post') }}" id="returnForm">
                    @csrf
                    <input type="text" name="book_id" id="scanInput"
                           class="scan-input-hidden"
                           autocomplete="off" autofocus tabindex="-1">
                </form>
            </div>
        </div>

        <a href="{{ route('kiosk.index') }}" class="back-link">← Back to Kiosk Menu</a>
    </div>
</div>

{{-- Success overlay --}}
<div class="result-overlay" id="successOverlay" style="background:rgba(0,0,0,0.5);">
    <div class="result-card">
        <div class="result-icon success">
            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="result-title success">Returned!</div>
        <div class="result-msg" id="successMsg"></div>
        <span class="result-dismiss">Scan Next Book</span>
        <span class="result-timer" id="successTimer"></span>
    </div>
</div>

{{-- Error overlay --}}
<div class="result-overlay" id="errorOverlay" style="background:rgba(0,0,0,0.5);">
    <div class="result-card">
        <div class="result-icon error">
            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#800000" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
        </div>
        <div class="result-title error">Return Failed</div>
        <div class="result-msg" id="errorMsg"></div>
        <span class="result-dismiss">Tap anywhere to dismiss</span>
    </div>
</div>

<script>
(function () {
    const form       = document.getElementById('returnForm');
    const input      = document.getElementById('scanInput');
    const badgeEl    = document.getElementById('scanStatusBadge');
    const displayEl  = document.getElementById('scanBarcodeDisplay');
    const hintEl     = document.getElementById('scanHint');

    let clearTimer = null;

    function setReady() {
        badgeEl.className = 'scan-status-badge';
        badgeEl.innerHTML = '<span class="status-dot"></span> Ready to Scan';
        displayEl.style.display = 'none';
        displayEl.textContent = '';
        hintEl.style.display = '';
        input.value = '';
    }

    function setScanning(val) {
        badgeEl.className = 'scan-status-badge scanning';
        badgeEl.innerHTML = '<span class="status-dot"></span> Scanning…';
        displayEl.style.display = 'block';
        displayEl.textContent = val;
        hintEl.style.display = 'none';
    }

    function setProcessing() {
        badgeEl.className = 'scan-status-badge processing';
        badgeEl.innerHTML = '<span class="status-dot"></span> Processing…';
    }

    // Keep scanner input focused
    function refocus() {
        var active = document.activeElement;
        if (active && (active.id === 'successOverlay' || active.id === 'errorOverlay')) return;
        if (active !== input) input.focus();
    }
    document.addEventListener('click', function(e) {
        if (e.target.closest('#successOverlay') || e.target.closest('#errorOverlay')) return;
        refocus();
    });
    document.addEventListener('keydown', function(e) {
        if (document.activeElement !== input) input.focus();
    });
    refocus();

    // Live barcode display as characters arrive
    input.addEventListener('input', function () {
        var val = input.value.trim();
        if (!val) { setReady(); return; }
        setScanning(val);
        clearTimeout(clearTimer);
        clearTimer = setTimeout(function () {
            if (input.value.trim()) setReady();
        }, 1500);
    });

    // Scanner sends Enter — submit immediately
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(clearTimer);
            if (!input.value.trim()) return;
            setProcessing();
            form.submit();
        }
    });

    // ── Overlays ──
    var successMsg  = @json(session('kiosk_success'));
    var errorMsg    = @json(session('kiosk_error') ?? ($errors->first() ?? null));

    function dismissOverlay(overlay) {
        overlay.classList.add('hiding');
        setTimeout(function () {
            overlay.classList.remove('show', 'hiding');
            setReady();
            input.focus();
        }, 300);
    }

    if (successMsg) {
        var overlay = document.getElementById('successOverlay');
        document.getElementById('successMsg').textContent = successMsg;
        overlay.classList.add('show');

        var secs = 4;
        var timerEl = document.getElementById('successTimer');
        timerEl.textContent = 'Closing in ' + secs + 's…';
        var t = setInterval(function () {
            secs--;
            timerEl.textContent = 'Closing in ' + secs + 's…';
            if (secs <= 0) { clearInterval(t); dismissOverlay(overlay); }
        }, 1000);

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) { clearInterval(t); dismissOverlay(overlay); }
        });
    }

    if (errorMsg) {
        var errOverlay = document.getElementById('errorOverlay');
        document.getElementById('errorMsg').textContent = errorMsg;
        errOverlay.classList.add('show');
        errOverlay.addEventListener('click', function (e) {
            if (e.target === errOverlay) dismissOverlay(errOverlay);
        });
    }
})();
</script>
</body>
</html>
