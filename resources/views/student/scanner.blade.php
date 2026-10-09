@extends('layouts.student')

@section('title', 'QR Scanner')

@push('styles')
<style>
    /* ── PAGE HEADER ── */
    .page-header {
        margin-bottom: 24px;
    }
    .page-header h1 {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--maroon);
        line-height: 1.2;
        margin-bottom: 4px;
    }
    .page-header p {
        font-size: 0.82rem;
        color: #888;
        margin: 0;
    }

    /* ── SCANNER CARD ── */
    .scanner-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .scanner-card:hover {
        box-shadow: 0 8px 28px rgba(128, 0, 0, 0.12);
    }

    .scanner-header {
        padding: 18px 24px;
        background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .scanner-icon {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .scanner-title h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #fff;
        margin: 0 0 4px 0;
    }
    .scanner-title p {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.8);
        margin: 0;
    }

    .scanner-body {
        padding: 24px;
    }

    /* Start Button */
    .btn-start {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: var(--yellow);
        color: var(--maroon);
        font-family: 'Poppins', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 12px 28px;
        border-radius: 40px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 100%;
    }
    .btn-start:hover {
        background: #e8d800;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 230, 66, 0.3);
    }

    /* Video Container */
    #video-container {
        margin-top: 20px;
        border-radius: 16px;
        overflow: hidden;
        background: #000;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }
    #video-container video {
        width: 100%;
        max-height: 360px;
        object-fit: cover;
        display: block;
    }

    /* Status Box */
    .status-box {
        margin-top: 20px;
        padding: 14px 18px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 500;
        text-align: center;
        animation: fadeInUp 0.3s ease;
    }
    .status-success {
        background: #E8F5E9;
        color: #2E7D32;
        border-left: 4px solid #4CAF50;
    }
    .status-error {
        background: #FFEBEE;
        color: #C62828;
        border-left: 4px solid #F44336;
    }

    /* AI Modal Styling */
    #aiModal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.2s ease;
    }
    .ai-modal-content {
        background: white;
        border-radius: 20px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        animation: slideUp 0.3s ease;
    }
    .ai-modal-header {
        padding: 18px 22px;
        background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
        color: white;
        display: flex;
        align-items: center;
        gap: 10px;
        border-radius: 20px 20px 0 0;
    }
    .ai-modal-header svg {
        flex-shrink: 0;
    }
    .ai-modal-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
    }
    .ai-modal-body {
        padding: 20px;
    }
    .ai-modal-body p {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 16px;
    }
    .ai-book-list {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
    }
    .ai-book-item {
        padding: 14px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }
    .ai-book-item:hover {
        background: #fafafa;
        border-radius: 10px;
    }
    .ai-book-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--maroon);
        text-decoration: none;
        display: block;
        margin-bottom: 4px;
    }
    .ai-book-title:hover {
        text-decoration: underline;
    }
    .ai-book-author {
        font-size: 0.75rem;
        color: #999;
        margin-bottom: 6px;
    }
    .ai-book-reason {
        font-size: 0.7rem;
        color: #aaa;
        font-style: italic;
    }
    .ai-modal-footer {
        padding: 16px 20px;
        border-top: 1px solid #f0f0f0;
        text-align: center;
    }
    .close-modal-btn {
        background: var(--maroon);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .close-modal-btn:hover {
        background: var(--maroon-light);
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
        .scanner-header {
            padding: 14px 18px;
        }
        .scanner-body {
            padding: 18px;
        }
        .btn-start {
            padding: 10px 20px;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>QR Scanner</h1>
    <p>Scan a book's QR code to borrow or return instantly</p>
</div>

{{-- Scanner Card --}}
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
            <h3>QR Scanner</h3>
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
                isProcessing = false;
                return;
            }
            let res = await fetch('/student/return', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ book_id: book.id })
            });
            if (res.status === 404) {
                res = await fetch('/student/borrow', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ book_id: book.id })
                });
            }
            const data = await res.json();
            if (!res.ok) {
                showResult('error', data.message || 'Failed to process.');
                isProcessing = false;
                return;
            }
            showResult('success', data.message);

            // Hide result after 3 seconds
            setTimeout(() => {
                const resultDiv = document.getElementById('result');
                resultDiv.style.display = 'none';
            }, 3000);

            if (data.recommendations && data.recommendations.length > 0) {
                showRecommendationModal(data.recommendations);
            } else {
                setTimeout(() => { window.location.href = '/student/dashboard'; }, 2000);
            }
        } catch (err) {
            console.error(err);
            showResult('error', err.message || 'Failed to process.');
            isProcessing = false;
        }
    }

    function showRecommendationModal(books) {
        let modalHtml = `
        <div id="aiModal">
            <div class="ai-modal-content">
                <div class="ai-modal-header">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
                        <path d="M12 2a10 10 0 0 1 10 10c0 5-3 8-6 8l-4 2-4-2c-3 0-6-3-6-8a10 10 0 0 1 10-10z"/>
                        <path d="M9 12h.01"/>
                        <path d="M15 12h.01"/>
                        <path d="M12 16c-1 0-1.5-.5-2-1"/>
                    </svg>
                    <h3>AI Recommends</h3>
                </div>
                <div class="ai-modal-body">
                    <p>Based on your activity, you might enjoy these books:</p>
                    <ul class="ai-book-list">`;
        books.forEach(book => {
            if (book.is_external) {
                modalHtml += `
                    <li class="ai-book-item">
                        <a href="${book.openlibrary_url}" target="_blank" class="ai-book-title">${escapeHtml(book.title)}</a>
                        <div class="ai-book-author">by ${escapeHtml(book.author || 'Unknown')}</div>
                        <div class="ai-book-reason">${escapeHtml(book.reason)}</div>
                    </li>`;
            } else {
                modalHtml += `
                    <li class="ai-book-item">
                        <a href="/student/books/${book.book_id}" target="_blank" class="ai-book-title">${escapeHtml(book.title)}</a>
                        <div class="ai-book-author">by ${escapeHtml(book.author || 'Unknown')}</div>
                        <div class="ai-book-reason">${escapeHtml(book.reason)}</div>
                    </li>`;
            }
        });
        modalHtml += `
                    </ul>
                </div>
                <div class="ai-modal-footer">
                    <button onclick="closeModalAndRedirect()" class="close-modal-btn">Continue to Dashboard</button>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    function closeModalAndRedirect() {
        const modal = document.getElementById('aiModal');
        if (modal) modal.remove();
        window.location.href = '/student/dashboard';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showResult(type, message) {
        const div = document.getElementById('result');
        div.className = `status-box status-${type}`;
        div.innerHTML = `
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                ${type === 'success' ? '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'}
            </svg>
            ${escapeHtml(message)}
        `;
        div.style.display = 'block';
    }

    document.getElementById('startScannerBtn').addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Starting camera...';
    try {
        const video = document.getElementById('video');
        const container = document.getElementById('video-container');
        container.style.display = 'block';
        btn.style.display = 'none';

        codeReader = new ZXing.BrowserMultiFormatReader();
        await codeReader.decodeFromVideoDevice(null, 'video', async (result, err) => {
            if (result && !isProcessing) {
                isProcessing = true;
                let scanned = result.getText();
                if (scanned.startsWith('BOOK:')) scanned = scanned.substring(5);
                await processIdentifier(scanned);
            }
            if (err && !err.message.includes('NotFoundException')) console.error(err);
        });
    } catch (err) {
        console.error('Camera error:', err);
        showResult('error', 'Cannot access camera. Please ensure you have granted camera permission and are using HTTPS.');
        btn.disabled = false;
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Start Camera';
        btn.style.display = 'flex';
        document.getElementById('video-container').style.display = 'none';
    }
});
</script>
@endsection