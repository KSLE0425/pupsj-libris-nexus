@extends('layouts.kiosk')

@section('title', 'Kiosk Dashboard')

@section('kiosk-user')
    <span class="kiosk-user-badge">{{ $user->first_name }} {{ $user->last_name }}</span>
    <form method="POST" action="{{ route('kiosk.logout') }}" style="display:inline;">
        @csrf
        <button type="submit" class="kiosk-logout-btn">Sign Out</button>
    </form>
@endsection

@push('styles')
<style>
    .kiosk-flash {
        padding: 14px 18px;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-size: 0.88rem;
        font-weight: 600;
        margin-bottom: 20px;
        border-left: 4px solid;
    }
    .kiosk-flash.success { background: #ecfdf5; color: #065f46; border-left-color: #10b981; }
    .kiosk-flash.error   { background: rgba(128,0,0,0.07); color: #800000; border-left-color: #800000; }

    /* ── SMART SCAN CARD ── */
    .scan-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 6px 28px rgba(128,0,0,0.13);
        overflow: hidden;
        margin-bottom: 28px;
        border: 1px solid rgba(128,0,0,0.08);
    }
    .scan-card-head {
        background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
        padding: 22px 28px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        border-bottom: 3px solid #FFC72C;
    }
    .scan-card-head h2 {
        font-size: 1.2rem;
        font-weight: 800;
        color: #fff;
        margin: 0;
        line-height: 1.2;
    }
    .scan-card-head p {
        font-size: 0.76rem;
        color: rgba(255,255,255,0.7);
        margin: 4px 0 0;
    }
    .scan-card-body {
        padding: 36px 28px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 18px;
        background: linear-gradient(180deg, #fff 0%, #fdf9f9 100%);
    }
    .scan-icon-wrap {
        width: 80px; height: 80px;
        border-radius: 50%;
        background: rgba(128,0,0,0.07);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(128,0,0,0.18); }
        50%       { box-shadow: 0 0 0 14px rgba(128,0,0,0); }
    }
    .scan-hint {
        font-size: 1rem;
        font-weight: 600;
        color: #800000;
        text-align: center;
    }
    .scan-hint small {
        display: block;
        font-size: 0.76rem;
        font-weight: 400;
        color: #888;
        margin-top: 4px;
    }
    /* Hidden capture input — visually invisible but focusable */
    .scan-input-hidden {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 1px; height: 1px;
        overflow: hidden;
    }
    /* Scan status badge */
    .scan-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .scan-status-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #22c55e;
        animation: blink 1.2s infinite;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.2; }
    }
    /* Live barcode feedback */
    .scan-barcode-display {
        font-family: 'Courier New', monospace;
        font-size: 1.3rem;
        font-weight: 700;
        color: #800000;
        background: #fdf8f8;
        border: 2px solid rgba(128,0,0,0.2);
        border-radius: 10px;
        padding: 10px 24px;
        letter-spacing: 0.12em;
        min-width: 200px;
        text-align: center;
    }
    .scan-status.scanning { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .scan-status.scanning .scan-status-dot { background: #f59e0b; animation: none; opacity: 1; }
    .scan-status.processing { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .scan-status.processing .scan-status-dot { background: #3b82f6; animation: none; opacity: 1; }

    /* ── CHECKED OUT TABLE ── */
    .borrows-table { width: 100%; border-collapse: collapse; font-size: 0.87rem; }
    .borrows-table th { text-align: left; padding: 11px 16px; background: #6a0000; color: white; font-size: 0.74rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
    .borrows-table td { padding: 13px 16px; border-bottom: 1px solid #f4f4f4; color: #333; }
    .borrows-table tbody tr:hover { background: #fffbf0; }
    .borrows-table tr:last-child td { border-bottom: none; }
    .borrow-book-title { font-weight: 600; color: #800000; }
    .borrow-since { font-size: 0.75rem; color: #9ca3af; }
    .empty-borrows {
        text-align: center;
        padding: 40px 32px;
        color: #9ca3af;
        font-size: 0.88rem;
    }
    .empty-borrows svg { margin: 0 auto 12px; display: block; opacity: 0.3; }
    .checkout-head {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 3px;
    }

    /* ── SCAN RESULT OVERLAY ── */
    .scan-result-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.55);
        backdrop-filter: blur(3px);
        animation: fadeInOverlay 0.2s ease;
    }
    @keyframes fadeInOverlay {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    .scan-result-card {
        background: #fff;
        border-radius: 20px;
        padding: 44px 52px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        max-width: 420px;
        width: 90%;
        animation: popIn 0.25s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes popIn {
        from { transform: scale(0.7); opacity: 0; }
        to   { transform: scale(1);   opacity: 1; }
    }
    .scan-result-icon {
        width: 72px; height: 72px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px;
    }
    .scan-result-icon.success { background: #dcfce7; }
    .scan-result-icon.error   { background: rgba(128,0,0,0.10); }
    .scan-result-title {
        font-size: 1.45rem;
        font-weight: 800;
        margin-bottom: 8px;
        line-height: 1.2;
    }
    .scan-result-title.success { color: #15803d; }
    .scan-result-title.error   { color: #800000; }
    .scan-result-msg {
        font-size: 0.92rem;
        color: #555;
        line-height: 1.5;
    }
    .scan-result-dismiss {
        margin-top: 22px;
        display: inline-block;
        background: #f3f4f6;
        color: #666;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 6px 18px;
        border-radius: 20px;
    }
    .scan-result-overlay.hiding {
        animation: fadeOutOverlay 0.3s ease forwards;
    }
    @keyframes fadeOutOverlay {
        to { opacity: 0; }
    }
</style>
@endpush

@section('content')

{{-- Prominent scan result overlay --}}
@if(session('kiosk_success'))
<div class="scan-result-overlay" id="scanResultOverlay">
    <div class="scan-result-card" id="scanResultCard">
        <div class="scan-result-icon success">
            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="scan-result-title success">Success!</div>
        <div class="scan-result-msg">{{ session('kiosk_success') }}</div>

        <span class="scan-result-dismiss" id="autoDismissLabel">Closing automatically…</span>
    </div>
</div>
@elseif(session('kiosk_error'))
<div class="scan-result-overlay" id="scanResultOverlay">
    <div class="scan-result-card">
        <div class="scan-result-icon error">
            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#800000" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
        </div>
        <div class="scan-result-title error">Scan Failed</div>
        <div class="scan-result-msg">{{ session('kiosk_error') }}</div>
        <span class="scan-result-dismiss">Tap anywhere to dismiss</span>
    </div>
</div>
@endif

{{-- Borrow duration confirmation (when overdue fines enabled) --}}
@if(session('kiosk_pending_borrow'))
@php
    $pendingMaxDays = (int) session('kiosk_pending_max_days', 7);
    $pendingInLibrary = (bool) session('kiosk_pending_in_library_only');
@endphp
<div class="scan-result-overlay" id="borrowDurationOverlay" style="display:flex;">
    <div class="scan-result-card" style="max-width:420px; text-align:left;" onclick="event.stopPropagation()">
        <div class="scan-result-title success" style="text-align:center; margin-bottom:8px;">Confirm Borrow Duration</div>
        <div class="scan-result-msg" style="text-align:center; margin-bottom:16px;">
            <strong>{{ session('kiosk_pending_book_title') }}</strong>
        </div>
        <form method="POST" action="{{ route('kiosk.smart-scan') }}" id="confirmBorrowForm">
            @csrf
            <input type="hidden" name="book_id" value="{{ session('kiosk_pending_book_id') }}">
            <input type="hidden" name="confirm_borrow" value="1">
            @if($pendingInLibrary)
                <input type="hidden" name="in_library" value="1">
                <div style="background:#eef2ff;border:1px solid #a5b4fc;border-radius:10px;padding:14px 16px;margin-bottom:16px;">
                    <div style="font-weight:700;color:#3730a3;font-size:0.9rem;margin-bottom:4px;">In-library use only</div>
                    <div style="font-size:0.8rem;color:#4b5563;">This book must stay in the library and be returned before you leave. Due today.</div>
                </div>
            @else
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#374151;text-transform:uppercase;margin-bottom:6px;">How many days will you borrow?</label>
                <select name="borrow_days" id="borrowDaysSelect" required
                    style="width:100%;padding:10px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:1rem;font-family:inherit;margin-bottom:8px;">
                    @for($d = 1; $d <= $pendingMaxDays; $d++)
                        <option value="{{ $d }}" @selected($d === $pendingMaxDays)>{{ $d }} day{{ $d > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
                <p style="font-size:0.75rem;color:#6b7280;margin:0 0 16px;">Maximum allowed: {{ $pendingMaxDays }} day{{ $pendingMaxDays > 1 ? 's' : '' }}.</p>
            @endif
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('borrowDurationOverlay').style.display='none'"
                    style="padding:10px 18px;border:1.5px solid #800000;background:#fff;color:#800000;border-radius:8px;font-weight:700;cursor:pointer;font-family:inherit;">Cancel</button>
                <button type="submit"
                    style="padding:10px 18px;border:none;background:#800000;color:#FFC72C;border-radius:8px;font-weight:700;cursor:pointer;font-family:inherit;">Confirm Borrow</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Validation errors from failed scan submissions --}}
@if($errors->any())
<div class="scan-result-overlay" id="scanResultOverlay">
    <div class="scan-result-card">
        <div class="scan-result-icon error">
            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#800000" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="scan-result-title error">Scan Error</div>
        <div class="scan-result-msg">{{ $errors->first() }}</div>
        <span class="scan-result-dismiss">Tap anywhere to dismiss</span>
    </div>
</div>
@endif

{{-- Smart Scan Panel --}}
<div class="scan-card">
    <div class="scan-card-head">
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#FFC72C" stroke-width="1.8">
            <path d="M3 5h2M3 12h2M3 19h2M7 5h2M7 12h2M7 19h2M11 5h2M11 12h2M11 19h2M15 5h2M15 12h2M15 19h2M19 5h2M19 12h2M19 19h2"/>
        </svg>
        <div>
            <h2>Scan to Borrow or Return</h2>
            <p>Point your barcode at the scanner — the system will automatically borrow or return the book</p>
        </div>
    </div>
    <div class="scan-card-body">
        <div class="scan-icon-wrap" id="scanIconWrap">
            <svg width="38" height="38" fill="none" viewBox="0 0 24 24" stroke="#800000" stroke-width="1.6">
                <path d="M3 5h2M3 12h2M3 19h2M7 5h2M7 12h2M7 19h2M11 5h2M11 12h2M11 19h2M15 5h2M15 12h2M15 19h2M19 5h2M19 12h2M19 19h2"/>
            </svg>
        </div>
        <div class="scan-hint" id="scanHint">
            Scan your book's barcode now
            <small>The scanner will automatically detect borrow or return</small>
        </div>
        {{-- Live barcode display --}}
        <div class="scan-barcode-display" id="scanBarcodeDisplay" style="display:none;"></div>
        <div class="scan-status" id="scanStatus">
            <span class="scan-status-dot"></span>
            Ready to Scan
        </div>
        {{-- Camera scanner UI (hidden by default) --}}
        <div id="cameraScannerWrap" style="display:none;width:100%;max-width:420px;">
            <div style="position:relative;border-radius:14px;overflow:hidden;background:#000;border:3px solid #800000;">
                <video id="cameraScannerVideo" autoplay playsinline muted style="width:100%;display:block;max-height:280px;object-fit:cover;"></video>
                <canvas id="cameraScannerCanvas" style="display:none;"></canvas>
                {{-- Scan crosshair overlay --}}
                <div style="position:absolute;inset:0;pointer-events:none;display:flex;align-items:center;justify-content:center;">
                    <div style="width:180px;height:180px;border:3px solid #FFC72C;border-radius:12px;box-shadow:0 0 0 4000px rgba(0,0,0,0.35);position:relative;">
                        <span style="position:absolute;top:-3px;left:-3px;width:24px;height:24px;border-top:4px solid #FFC72C;border-left:4px solid #FFC72C;border-radius:3px 0 0 0;"></span>
                        <span style="position:absolute;top:-3px;right:-3px;width:24px;height:24px;border-top:4px solid #FFC72C;border-right:4px solid #FFC72C;border-radius:0 3px 0 0;"></span>
                        <span style="position:absolute;bottom:-3px;left:-3px;width:24px;height:24px;border-bottom:4px solid #FFC72C;border-left:4px solid #FFC72C;border-radius:0 0 0 3px;"></span>
                        <span style="position:absolute;bottom:-3px;right:-3px;width:24px;height:24px;border-bottom:4px solid #FFC72C;border-right:4px solid #FFC72C;border-radius:0 0 3px 0;"></span>
                    </div>
                </div>
            </div>
            <p style="text-align:center;font-size:0.78rem;color:#888;margin-top:8px;">Point camera at QR code to scan automatically</p>
        </div>
        {{-- Toggle camera button --}}
        <button type="button" id="toggleCameraBtn"
                style="display:inline-flex;align-items:center;gap:8px;background:rgba(128,0,0,0.08);color:#800000;border:1.5px solid rgba(128,0,0,0.25);border-radius:8px;padding:8px 18px;font-size:0.8rem;font-weight:600;cursor:pointer;transition:all 0.2s;font-family:inherit;"
                onmouseover="this.style.background='rgba(128,0,0,0.14)'" onmouseout="this.style.background='rgba(128,0,0,0.08)'"
                title="Switch to camera QR scanning">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
            Scan with Camera
        </button>
        {{-- Hidden form — scanner populates & submits automatically --}}
        <form method="POST" action="{{ route('kiosk.smart-scan') }}" id="smart-scan-form">
            @csrf
            <input type="text" name="book_id" id="smart-scan-input"
                   class="scan-input-hidden"
                   autocomplete="off" autofocus tabindex="-1">
        </form>
    </div>
</div>

{{-- Currently Checked Out --}}
<div class="k-card" style="border-radius:18px; box-shadow:0 6px 28px rgba(128,0,0,0.10); border:1px solid rgba(128,0,0,0.08);">
    <div class="k-card-head" style="background:linear-gradient(135deg,#800000 0%,#5a0000 100%); border-bottom:3px solid #FFC72C;">
        <div class="checkout-head">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#FFC72C" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            Currently Checked Out
        </div>
        <p class="panel-sub">Books you currently have borrowed</p>
    </div>
    <div>
        @if($activeBorrows->count())
            <table class="borrows-table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Book ID</th>
                        <th>Borrowed On</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeBorrows as $borrow)
                    <tr>
                        <td>
                            <div class="borrow-book-title">{{ $borrow->book->title ?? 'Unknown' }}</div>
                            @if($borrow->book->author ?? false)
                                <div class="borrow-since">{{ $borrow->book->author }}</div>
                            @endif
                        </td>
                        <td style="color:#888; font-family: monospace;">{{ $borrow->book_id }}</td>
                        <td><span class="borrow-since">{{ $borrow->time_in->format('M d, Y') }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-borrows">
                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                No books currently borrowed.
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form       = document.getElementById('smart-scan-form');
    const input      = document.getElementById('smart-scan-input');
    const statusEl   = document.getElementById('scanStatus');
    const displayEl  = document.getElementById('scanBarcodeDisplay');
    const hintEl     = document.getElementById('scanHint');

    let clearTimer = null;

    function setReady() {
        statusEl.className = 'scan-status';
        statusEl.innerHTML = '<span class="scan-status-dot"></span> Ready to Scan';
        if (displayEl) { displayEl.style.display = 'none'; displayEl.textContent = ''; }
        if (hintEl) hintEl.style.display = '';
        input.value = '';
    }

    function setScanning(val) {
        statusEl.className = 'scan-status scanning';
        statusEl.innerHTML = '<span class="scan-status-dot"></span> Scanning…';
        if (displayEl) { displayEl.style.display = 'block'; displayEl.textContent = val; }
        if (hintEl) hintEl.style.display = 'none';
    }

    function setProcessing() {
        statusEl.className = 'scan-status processing';
        statusEl.innerHTML = '<span class="scan-status-dot"></span> Processing…';
    }

    // Keep the hidden input focused at all times
    function refocus() {
        if (document.activeElement !== input) input.focus();
    }
    document.addEventListener('click', function(e) {
        // Don't steal focus when user is dismissing the result overlay
        if (e.target.closest('#scanResultOverlay')) return;
        refocus();
    });
    document.addEventListener('keydown', function(e) {
        if (document.activeElement !== input) input.focus();
    });
    refocus();

    // Show live barcode as characters arrive
    input.addEventListener('input', function() {
        const val = input.value.trim();
        if (!val) { setReady(); return; }
        setScanning(val);
        // Auto-clear if scanner stops sending characters after 1.5s (incomplete scan)
        clearTimeout(clearTimer);
        clearTimer = setTimeout(function() {
            if (input.value.trim()) {
                setReady();
            }
        }, 1500);
    });

    // Scanner sends Enter after barcode — submit immediately
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(clearTimer);
            const val = input.value.trim();
            if (!val) return;
            setProcessing();
            form.submit();
        }
    });

    @if(session('kiosk_success'))
        // Auto-dismiss overlay after 3s and reset scanner
        setTimeout(setReady, 3000);
    @endif
})();

// Scan result overlay — auto-dismiss and tap-to-dismiss
(function() {
    var overlay = document.getElementById('scanResultOverlay');
    if (!overlay) return;

    function dismissOverlay() {
        overlay.classList.add('hiding');
        setTimeout(function() { overlay.remove(); }, 300);
        // Re-focus scanner input after dismissal
        var inp = document.getElementById('smart-scan-input');
        if (inp) inp.focus();
    }

    @if(session('kiosk_success'))
        // Auto-dismiss all success overlays after 3 seconds
        setTimeout(dismissOverlay, 3000);
    @endif

    // Tap/click on backdrop (not the card) to dismiss
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) dismissOverlay();
    });
})();

// ── Camera QR Scanner ──
(function() {
    const toggleBtn   = document.getElementById('toggleCameraBtn');
    const cameraWrap  = document.getElementById('cameraScannerWrap');
    const video       = document.getElementById('cameraScannerVideo');
    const canvas      = document.getElementById('cameraScannerCanvas');
    const form        = document.getElementById('smart-scan-form');
    const hiddenInput = document.getElementById('smart-scan-input');
    const scanHint    = document.getElementById('scanHint');
    const scanIconWrap= document.getElementById('scanIconWrap');
    const statusEl    = document.getElementById('scanStatus');

    if (!toggleBtn || !cameraWrap || !video || !canvas || !form) return;

    let cameraActive = false;
    let stream = null;
    let rafId = null;
    let submitted = false;

    // Load jsQR dynamically
    function loadJsQR(cb) {
        if (window.jsQR) { cb(); return; }
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js';
        s.onload = function() {
            if (!window.jsQR) {
                alert('QR scanner library failed to load. Check your network connection and try again.');
                return;
            }
            cb();
        };
        s.onerror = function() {
            alert('QR scanner library failed to load. Check your network connection and try again.');
        };
        document.head.appendChild(s);
    }

    function getCameraConstraints() {
        return {
            video: {
                facingMode: { ideal: 'environment' },
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
    }

    function startCameraStream(constraints) {
        return navigator.mediaDevices.getUserMedia(constraints)
            .catch(function() {
                // Fall back to any available camera (desktop kiosks often lack rear camera)
                return navigator.mediaDevices.getUserMedia({ video: true });
            });
    }

    function startCamera() {
        loadJsQR(function() {
            startCameraStream(getCameraConstraints())
            .then(function(s) {
                stream = s;
                video.srcObject = s;
                video.setAttribute('playsinline', 'true');
                video.muted = true;
                var playPromise = video.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(function() {});
                }
                cameraActive = true;
                submitted = false;
                cameraWrap.style.display = '';
                if (scanHint) scanHint.style.display = 'none';
                if (scanIconWrap) scanIconWrap.style.display = 'none';
                statusEl.className = 'scan-status';
                statusEl.innerHTML = '<span class="scan-status-dot" style="background:#FFC72C;"></span> Camera Active \u2014 Point at QR code';
                toggleBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Stop Camera';
                toggleBtn.style.background = 'rgba(128,0,0,0.14)';
                tick();
            })
            .catch(function(err) {
                alert('Camera access denied or not available: ' + err.message);
            });
        });
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(function(t) { t.stop(); });
            stream = null;
        }
        if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
        cameraActive = false;
        cameraWrap.style.display = 'none';
        if (scanHint) scanHint.style.display = '';
        if (scanIconWrap) scanIconWrap.style.display = '';
        statusEl.className = 'scan-status';
        statusEl.innerHTML = '<span class="scan-status-dot"></span> Ready to Scan';
        toggleBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg> Scan with Camera';
        toggleBtn.style.background = 'rgba(128,0,0,0.08)';
        // Re-focus hidden input for hardware scanner
        if (hiddenInput) hiddenInput.focus();
    }

    let barcodeDetector = null;
    if ('BarcodeDetector' in window) {
        try {
            barcodeDetector = new BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13', 'ean_8', 'code_39'] });
        } catch(e) {
            barcodeDetector = null;
        }
    }

    async function tick() {
        if (!cameraActive) return;
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width  = video.videoWidth;
            canvas.height = video.videoHeight;

            // 1. Try native fast BarcodeDetector API if supported
            if (barcodeDetector) {
                try {
                    const barcodes = await barcodeDetector.detect(video);
                    if (barcodes && barcodes.length > 0 && !submitted) {
                        const rawVal = barcodes[0].rawValue;
                        if (rawVal) {
                            submitted = true;
                            hiddenInput.value = rawVal.trim();
                            statusEl.className = 'scan-status processing';
                            statusEl.innerHTML = '<span class="scan-status-dot" style="background:#3b82f6;"></span> Processing\u2026';
                            stopCamera();
                            form.submit();
                            return;
                        }
                    }
                } catch(e) {
                    // Fall back to canvas/jsQR
                }
            }

            // 2. Fallback to jsQR with willReadFrequently: true to eliminate canvas warnings and improve performance
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            if (ctx) {
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                if (window.jsQR) {
                    const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'attemptBoth' });
                    if (code && code.data && !submitted) {
                        submitted = true;
                        hiddenInput.value = code.data.trim();
                        statusEl.className = 'scan-status processing';
                        statusEl.innerHTML = '<span class="scan-status-dot" style="background:#3b82f6;"></span> Processing\u2026';
                        stopCamera();
                        form.submit();
                        return;
                    }
                }
            }
        }
        rafId = requestAnimationFrame(tick);
    }

    toggleBtn.addEventListener('click', function() {
        if (cameraActive) {
            stopCamera();
        } else {
            startCamera();
        }
    });
})();

</script>
@endpush
