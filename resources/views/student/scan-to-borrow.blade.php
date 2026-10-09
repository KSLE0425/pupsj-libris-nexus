@extends('layouts.student')

@section('title', 'Scan to Borrow')

@push('styles')
<style>
    :root { --maroon: #800000; --maroon-dark: #5a0000; --maroon-light: #9a1a1a; --yellow: #FFC72C; }
    * { font-family: 'Poppins', system-ui, -apple-system, sans-serif; box-sizing: border-box; }
    body { background: #f5f5f5; color: #1f2937; }
    
    .page-header { margin-bottom: 24px; }
    .page-header h1 { font-size: 1.6rem; font-weight: 800; color: var(--maroon); margin-bottom: 4px; }
    .page-header p { font-size: 0.82rem; color: #888; margin: 0; }

    .scanner-card {
        background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 24px; overflow: hidden;
    }
    .scanner-header {
        padding: 18px 24px; background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
        display: flex; align-items: center; gap: 12px;
    }
    .scanner-icon {
        width: 48px; height: 48px; background: rgba(255,255,255,0.15);
        border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .scanner-title h3 { font-size: 1.1rem; font-weight: 700; color: #fff; margin: 0 0 4px 0; }
    .scanner-title p { font-size: 0.75rem; color: rgba(255,255,255,0.8); margin: 0; }
    .scanner-body { padding: 24px; }

    .btn-start {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        background: var(--yellow); color: var(--maroon); font-size: 0.85rem; font-weight: 700;
        padding: 12px 28px; border-radius: 40px; border: none; cursor: pointer;
        transition: all 0.2s ease; width: 100%;
    }
    .btn-start:hover { background: #e8d800; transform: translateY(-2px); }

    #video-container { margin-top: 20px; border-radius: 16px; overflow: hidden; background: #000; box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
    #video-container video { width: 100%; max-height: 360px; object-fit: cover; display: block; }

    .status-box { margin-top: 20px; padding: 14px 18px; border-radius: 12px; font-size: 0.85rem; font-weight: 500; text-align: center; }
    .status-success { background: #E8F5E9; color: #2E7D32; border-left: 4px solid #4CAF50; }
    .status-error { background: #FFEBEE; color: #C62828; border-left: 4px solid #F44336; }
@media (max-width: 768px) {
        .scanner-header { padding: 14px 18px; }
        .scanner-body { padding: 18px; }
        .btn-start { padding: 10px 20px; }
}
</style>
@endpush

@section('content')
<div class="page-header">
<h1>Scan to Borrow</h1>
    <p>Scan a book's QR code to borrow</p>
</div>

<div class="scanner-card">
    <div class="scanner-header">
        <div class="scanner-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <rect x="2" y="3" width="20" height="18" rx="2" ry="2"/>
                <circle cx="9" cy="12" r="1"/>
                <circle cx="15" cy="12" r="1"/>
                <path d="M12 8v4"/>
                <path d="M12 16h.01"/>
            </svg>
        </div>
        <div class="scanner-title">
<h3>Scan to Borrow</h3>
            <p>Position the book's QR code in front of the camera</p>
        </div>
    </div>
    <div class="scanner-body">
        <button id="startScannerBtn" class="btn-start">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            Start Camera
        </button>
        <div id="video-container" style="display: none;">
            <video id="video" playsinline style="transform: scaleX(-1);"></video>
        </div>
        <div id="result" class="status-box" style="display: none;"></div>
    </div>
</div>


<script src="https://unpkg.com/@zxing/library@0.18.6/umd/index.min.js"></script>
<script>
let codeReader = null;
let isProcessing = false;

async function findBook(identifier) {
    let res = await fetch(`/student/book-by-barcode/${encodeURIComponent(identifier)}`);
    if (res.ok) return await res.json();
    res = await fetch(`/student/book-by-hash/${encodeURIComponent(identifier)}`);
    if (res.ok) return await res.json();
    res = await fetch(`/student/books/${identifier}`);
    if (res.ok) return await res.json();
    throw new Error('Book not found');
}
async function processIdentifier(identifier) {
    try {
        const book = await findBook(identifier);
        if (book.status === 'archived') {
            showResult('error', 'This book is archived and cannot be borrowed.');
            isProcessing = false; return;
        }
        const res = await fetch('/student/borrow', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ book_id: book.id })
        });
        const data = await res.json();
        if (res.ok) {
            showResult('success', data.message);
            setTimeout(() => { window.location.href = '/student/dashboard'; }, 2000);
        } else {
            showResult('error', data.message || 'Failed to borrow book.');
            isProcessing = false;
        }
    } catch (err) {
        console.error(err);
        showResult('error', err.message || 'Failed to process.');
        isProcessing = false;
    }
}

function showResult(type, message) {
    const div = document.getElementById('result');
    div.className = `status-box status-${type}`;
    div.innerHTML = message;
    div.style.display = 'block';
}

document.getElementById('startScannerBtn').addEventListener('click', async function() {
    const btn = this; btn.disabled = true;
    btn.innerHTML = 'Starting camera...';
    try {
        const video = document.getElementById('video');
        const container = document.getElementById('video-container');
        container.style.display = 'block'; btn.style.display = 'none';
        codeReader = new ZXing.BrowserMultiFormatReader();
        await codeReader.decodeFromVideoDevice(null, 'video', async (result, err) => {
            if (result && !isProcessing) {
                isProcessing = true;
                let scanned = result.getText();
                if (scanned.startsWith('BOOK:')) scanned = scanned.substring(5);
                await processIdentifier(scanned);
            }
        });
    } catch (err) {
        console.error('Camera error:', err);
        showResult('error', 'Cannot access camera.');
        btn.disabled = false; btn.innerHTML = 'Start Camera'; btn.style.display = 'flex';
        document.getElementById('video-container').style.display = 'none';
    }
});
</script>
@endsection