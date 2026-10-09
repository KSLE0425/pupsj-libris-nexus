@extends('layouts.admin')

@section('content')
<div class="global-archive-page">
    {{-- Top Bar --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2>Batch Scan &amp; Archive</h2>
            <p class="page-subtitle mb-0">Universal scan-to-archive station for Books, Students, and Faculty.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.books') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="min-height:44px; border-radius:8px; font-weight:600; padding:0 18px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                <span>Back to Books</span>
            </a>
            <button type="button" onclick="openScannerOverlay()" class="btn btn-primary d-inline-flex align-items-center gap-2" style="background:var(--pup-maroon); color:white; min-height:44px; border-radius:8px; font-weight:600; padding:0 22px; border:none;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="18" rx="2"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="16" y2="13"/></svg>
                <span>Launch Scanner Mode</span>
            </button>
        </div>
    </div>

    {{-- Overview Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="border-left:4px solid #800000 !important; background:white;">
                <div class="text-muted small text-uppercase font-weight-bold">Archived Books</div>
                <div class="h3 font-weight-bold text-dark mb-0 mt-1">{{ \App\Models\Book::where('status', 'archived')->orWhereNotNull('deleted_at')->withTrashed()->count() }}</div>
                <small class="text-muted">In catalog archive</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="border-left:4px solid #3b82f6 !important; background:white;">
                <div class="text-muted small text-uppercase font-weight-bold">Archived Students</div>
                <div class="h3 font-weight-bold text-dark mb-0 mt-1">{{ \App\Models\Student::onlyTrashed()->count() }}</div>
                <small class="text-muted">Dormant / Archived patrons</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="border-left:4px solid #10b981 !important; background:white;">
                <div class="text-muted small text-uppercase font-weight-bold">Archived Faculty</div>
                <div class="h3 font-weight-bold text-dark mb-0 mt-1">{{ \App\Models\Faculty::onlyTrashed()->count() }}</div>
                <small class="text-muted">Inactive faculty accounts</small>
            </div>
        </div>
    </div>

    {{-- Inline Scanner Card --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
        <div class="card-body p-4" style="background:linear-gradient(135deg, #800000 0%, #4a0000 100%); color:white;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:54px; height:54px; background:rgba(255,199,44,0.2); border:2px solid #FFC72C; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#FFC72C;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                    </div>
                    <div>
                        <h4 class="mb-1 text-white font-weight-bold">Batch Scan &amp; Archive Station</h4>
                        <p class="mb-0 text-white-50 small">Point hardware scanner or launch kiosk-style scanner mode. Supports Book barcodes, Accession numbers, Student numbers, Employee IDs, and encrypted QR codes.</p>
                    </div>
                </div>
                <button type="button" onclick="openScannerOverlay()" class="btn d-inline-flex align-items-center gap-2" style="background:#FFC72C; color:#5a0000; font-weight:700; min-height:46px; padding:0 24px; border-radius:8px; border:none; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                    <span>Open Scanner</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Running Session List --}}
    <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h4 class="mb-0" style="font-size:1rem; font-weight:700; color:var(--pup-maroon);">Session Activity Log</h4>
                <span class="badge bg-light text-dark border px-2 py-1" id="sessionCountBadge">0 items processed</span>
            </div>

            {{-- Filter Bar (Requirement #3) --}}
            <div class="session-filter-bar">
                <div class="filter-group filter-search">
                    <label for="sessionSearchInput">Search</label>
                    <div class="input-with-icon">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#800000" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        <input type="text" id="sessionSearchInput" placeholder="Search item details or identifier..." oninput="filterSessionLog()" class="filter-control">
                    </div>
                </div>

                <div class="filter-group">
                    <label for="sessionTypeSelect">Type</label>
                    <select id="sessionTypeSelect" onchange="filterSessionLog()" class="filter-control">
                        <option value="">All Types</option>
                        <option value="Book">Book</option>
                        <option value="Student">Student</option>
                        <option value="Faculty">Faculty</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sessionActionSelect">Action</label>
                    <select id="sessionActionSelect" onchange="filterSessionLog()" class="filter-control">
                        <option value="">All Actions</option>
                        <option value="archived">Archived</option>
                        <option value="unarchived">Unarchived</option>
                    </select>
                </div>

                <div class="filter-group filter-actions">
                    <label>&nbsp;</label>
                    <button type="button" onclick="resetSessionFilters()" class="btn-reset-filters">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="sessionLogTable">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th style="font-size:0.75rem; text-transform:uppercase; color:#6b7280; width:90px;">Time</th>
                        <th style="font-size:0.75rem; text-transform:uppercase; color:#6b7280; width:100px;">Type</th>
                        <th style="font-size:0.75rem; text-transform:uppercase; color:#6b7280;">Item Details</th>
                        <th style="font-size:0.75rem; text-transform:uppercase; color:#6b7280;">Identifier</th>
                        <th style="font-size:0.75rem; text-transform:uppercase; color:#6b7280; width:120px;">Action Taken</th>
                    </tr>
                </thead>
                <tbody id="sessionLogTbody">
                    <tr id="emptySessionRow">
                        <td colspan="5" class="text-center py-4 text-muted small">
                            No items scanned in this session yet. Launch the scanner overlay or scan an item above.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- FULLSCREEN KIOSK-STYLE ARCHIVE OVERLAY (Matches Screenshot 2) --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="archiveScannerOverlay" class="archive-overlay-backdrop" style="display:none;">
    <div class="archive-overlay-container">
        {{-- Close Button --}}
        <button type="button" onclick="closeScannerOverlay()" class="overlay-close-btn" title="Close Batch Scan &amp; Archive (Esc)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        {{-- Centered Scanner Card --}}
        <div class="archive-overlay-card">
            {{-- Header --}}
            <div class="overlay-header">
                <div class="overlay-brand-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#FFC72C" stroke-width="2.2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                </div>
                <h3 class="overlay-title">Batch Scan &amp; Archive</h3>
                <p class="overlay-subtitle">Universal scan-to-archive station for Books, Students, and Faculty</p>
            </div>

            {{-- Status Banner --}}
            <div id="overlayStatusBanner" class="overlay-status-box" style="display:none;"></div>

            {{-- Scanning Area with Visual Radar Pulse --}}
            <div class="scanner-zone">
                <div class="scanner-radar">
                    <div class="radar-circle circle-1"></div>
                    <div class="radar-circle circle-2"></div>
                    <div class="scanner-laser"></div>
                    <div class="scanner-center-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#FFC72C" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h3v3H7zM14 7h3v3h-3zM7 14h3v3H7zM14 14h3v3h-3z"/></svg>
                    </div>
                </div>
                <div class="scanner-status-text">
                    <span class="pulse-dot"></span>
                    <strong>Ready to Scan</strong>
                </div>
                <p class="scanner-instructions">Use your barcode or QR scanner. The item will be toggled immediately.</p>
            </div>

            {{-- Input Form --}}
            <form id="overlayScanForm" onsubmit="handleScanSubmit(event, 'overlayScanInput');" class="overlay-input-group">
                <input type="text" id="overlayScanInput" placeholder="Enter barcode, accession #, student #, or employee ID..." class="overlay-input" autocomplete="off">
                <button type="submit" class="overlay-submit-btn" style="min-height:48px; min-width:48px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </form>

            {{-- Running Session List inside Overlay --}}
            <div class="overlay-session-section">
                <div class="overlay-session-header">
                    <span>Recent Scans in this Session (<span id="overlaySessionCount">0</span>)</span>
                    <button type="button" onclick="clearSessionLog()" class="clear-session-btn">Clear Log</button>
                </div>
                <div class="overlay-session-list" id="overlaySessionList">
                    <div class="overlay-empty-state" id="overlayEmptyState">No items scanned yet in this session.</div>
                </div>
            </div>

            {{-- Done / Finish Button --}}
            <div class="overlay-footer">
                <button type="button" onclick="closeScannerOverlay()" class="btn-done">
                    <span>Done &amp; Close Session</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Global Archive Styling */
.global-archive-page {
    max-width: 1400px;
    margin: 0 auto;
}

/* Fullscreen Overlay Backdrop */
.archive-overlay-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.88);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
    animation: fadeInOverlay 0.25s ease forwards;
}

@keyframes fadeInOverlay {
    from { opacity: 0; }
    to { opacity: 1; }
}

.archive-overlay-container {
    position: relative;
    width: 100%;
    max-width: 640px;
    margin: auto;
}

.overlay-close-btn {
    position: absolute;
    top: -14px;
    right: -14px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #800000;
    color: white;
    border: 2px solid #FFC72C;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: transform 0.15s, background 0.15s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}
.overlay-close-btn:hover {
    background: #a30000;
    transform: scale(1.08);
}

.archive-overlay-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 32px 28px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,0.2);
}

.overlay-header {
    text-align: center;
    margin-bottom: 20px;
}

.overlay-brand-icon {
    width: 64px;
    height: 64px;
    background: #800000;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    box-shadow: 0 4px 14px rgba(128,0,0,0.25);
}

.overlay-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #800000;
    margin: 0 0 4px;
}

.overlay-subtitle {
    font-size: 0.85rem;
    color: #6b7280;
    margin: 0;
}

/* Scanner Radar Zone */
.scanner-zone {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    padding: 24px 16px;
    text-align: center;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}

.scanner-radar {
    width: 90px;
    height: 90px;
    margin: 0 auto 12px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.radar-circle {
    position: absolute;
    border-radius: 50%;
    border: 2px solid rgba(128,0,0,0.2);
    animation: radarPulse 2s infinite cubic-bezier(0.215, 0.61, 0.355, 1);
}
.circle-1 { width: 60px; height: 60px; }
.circle-2 { width: 90px; height: 90px; animation-delay: 0.5s; }

@keyframes radarPulse {
    0% { transform: scale(0.6); opacity: 0.8; }
    50% { opacity: 0.4; }
    100% { transform: scale(1.2); opacity: 0; }
}

.scanner-laser {
    position: absolute;
    top: 0;
    left: 10%;
    width: 80%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #FFC72C, #ef4444, transparent);
    box-shadow: 0 0 8px #ef4444;
    animation: laserScan 2.2s ease-in-out infinite alternate;
}

@keyframes laserScan {
    0% { top: 15%; }
    100% { top: 85%; }
}

.scanner-center-icon {
    width: 52px;
    height: 52px;
    background: #800000;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}

.scanner-status-text {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.95rem;
    color: #1e293b;
    margin-bottom: 4px;
}

.pulse-dot {
    width: 10px;
    height: 10px;
    background: #10b981;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulseDot 1.5s infinite;
}

@keyframes pulseDot {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.scanner-instructions {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0;
}

/* Status Box */
.overlay-status-box {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 16px;
    font-size: 0.88rem;
    font-weight: 600;
    animation: slideDown 0.2s ease;
}
.overlay-status-box.success {
    background: #ecfdf5;
    color: #065f46;
    border-left: 4px solid #10b981;
}
.overlay-status-box.error {
    background: #fef2f2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Input Form */
.overlay-input-group {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
}

.overlay-input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.95rem;
    font-family: inherit;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    min-height: 48px;
}
.overlay-input:focus {
    border-color: #800000;
    box-shadow: 0 0 0 3px rgba(128,0,0,0.12);
}

.overlay-submit-btn {
    background: #800000;
    color: white;
    border: none;
    border-radius: 10px;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s;
}
.overlay-submit-btn:hover {
    background: #5a0000;
}

/* Running Session List */
.overlay-session-section {
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    margin-bottom: 20px;
}

.overlay-session-header {
    padding: 10px 14px;
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.78rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
}

.clear-session-btn {
    background: none;
    border: none;
    color: #ef4444;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
}
.clear-session-btn:hover { text-decoration: underline; }

.overlay-session-list {
    max-height: 180px;
    overflow-y: auto;
    padding: 8px 12px;
}

.overlay-session-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.82rem;
}
.overlay-session-item:last-child { border-bottom: none; }

.overlay-empty-state {
    text-align: center;
    padding: 20px;
    color: #94a3b8;
    font-size: 0.82rem;
}

.overlay-footer {
    text-align: center;
}

.btn-done {
    width: 100%;
    min-height: 48px;
    background: #800000;
    color: #FFC72C;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-done:hover {
    background: #5a0000;
}

/* Session Activity Log Filter Bar (Requirement #3) */
.session-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
    padding-top: 10px;
}
.filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 140px;
}
.filter-group.filter-search {
    flex: 1;
    min-width: 220px;
}
.filter-group label {
    font-size: 0.74rem;
    font-weight: 700;
    color: #800000;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin: 0;
}
.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}
.input-with-icon svg {
    position: absolute;
    left: 10px;
    pointer-events: none;
}
.input-with-icon .filter-control {
    padding-left: 32px;
}
.filter-control {
    height: 38px;
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.84rem;
    font-family: inherit;
    color: #1f2937;
    background: #fff;
    transition: border-color 0.15s, box-shadow 0.15s;
    width: 100%;
}
.filter-control:focus {
    outline: none;
    border-color: #800000;
    box-shadow: 0 0 0 3px rgba(255, 199, 44, 0.35);
}
.btn-reset-filters {
    height: 38px;
    padding: 6px 14px;
    background: #fff;
    border: 1px solid #800000;
    color: #800000;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-reset-filters:hover {
    background: #800000;
    color: #FFC72C;
}
</style>

<script>
let sessionScans = [];

function openScannerOverlay() {
    const overlay = document.getElementById('archiveScannerOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
        setTimeout(() => {
            const input = document.getElementById('overlayScanInput');
            if (input) input.focus();
        }, 100);
    }
}

function closeScannerOverlay() {
    const overlay = document.getElementById('archiveScannerOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Global Escape listener to close overlay
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeScannerOverlay();
    }
});

// Auto-focus manual input on overlay click
document.addEventListener('click', function(e) {
    const overlay = document.getElementById('archiveScannerOverlay');
    if (overlay && overlay.style.display === 'flex') {
        const input = document.getElementById('overlayScanInput');
        if (input && document.activeElement !== input && !e.target.closest('button, a, input')) {
            input.focus();
        }
    }
});

async function handleScanSubmit(event, inputId) {
    event.preventDefault();
    const input = document.getElementById(inputId);
    if (!input) return;
    const code = input.value.trim();
    if (!code) return;

    input.value = ''; // clear immediately for rapid scanning
    await executeScan(code);
}

async function executeScan(code) {
    showOverlayStatus('info', `Processing scan "${code}"...`);

    try {
        const res = await fetch('{{ route("admin.archive-scanner.scan") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ code: code })
        });

        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.message || 'Item not found or failed to process.');
        }

        // Add to session log
        sessionScans.unshift(data);
        filterSessionLog();

        showOverlayStatus('success', `${data.message}`);
    } catch (err) {
        showOverlayStatus('error', err.message || 'Error processing scan.');
    }
}

function showOverlayStatus(type, message) {
    const banner = document.getElementById('overlayStatusBanner');
    if (!banner) return;

    banner.className = `overlay-status-box ${type}`;
    banner.innerHTML = message;
    banner.style.display = 'block';

    if (type !== 'info') {
        setTimeout(() => {
            if (banner.className.includes(type)) {
                banner.style.display = 'none';
            }
        }, 5000);
    }
}

function filterSessionLog() {
    const search = (document.getElementById('sessionSearchInput')?.value || '').toLowerCase().trim();
    const type = (document.getElementById('sessionTypeSelect')?.value || '').toLowerCase().trim();
    const action = (document.getElementById('sessionActionSelect')?.value || '').toLowerCase().trim();

    const filtered = sessionScans.filter(item => {
        const itemType = (item.type || '').toLowerCase();
        const itemTitle = (item.title || '').toLowerCase();
        const itemSubtitle = (item.subtitle || '').toLowerCase();
        const itemIdentifier = (item.identifier || '').toLowerCase();
        const itemAction = (item.action || '').toLowerCase();

        // Search text matching item title, subtitle, or identifier
        if (search) {
            const matchSearch = itemTitle.includes(search) || 
                                itemSubtitle.includes(search) || 
                                itemIdentifier.includes(search);
            if (!matchSearch) return false;
        }

        // Type filter matching
        if (type && itemType !== type) {
            return false;
        }

        // Action filter matching: archived vs unarchived
        if (action) {
            if (action === 'archived' && itemAction !== 'archived') return false;
            if (action === 'unarchived' && itemAction === 'archived') return false;
        }

        return true;
    });

    renderSessionLog(filtered);
}

function resetSessionFilters() {
    const searchInput = document.getElementById('sessionSearchInput');
    const typeSelect = document.getElementById('sessionTypeSelect');
    const actionSelect = document.getElementById('sessionActionSelect');

    if (searchInput) searchInput.value = '';
    if (typeSelect) typeSelect.value = '';
    if (actionSelect) actionSelect.value = '';

    filterSessionLog();
}

function renderSessionLog(itemsToRender = sessionScans) {
    const list = document.getElementById('overlaySessionList');
    const tbody = document.getElementById('sessionLogTbody');
    const overlayCount = document.getElementById('overlaySessionCount');
    const badgeCount = document.getElementById('sessionCountBadge');

    if (overlayCount) overlayCount.textContent = sessionScans.length;
    if (badgeCount) {
        if (itemsToRender.length === sessionScans.length) {
            badgeCount.textContent = `${sessionScans.length} item(s) processed`;
        } else {
            badgeCount.textContent = `${itemsToRender.length} of ${sessionScans.length} matching`;
        }
    }

    // Render in overlay (always full running log)
    if (list) {
        if (sessionScans.length === 0) {
            list.innerHTML = `<div class="overlay-empty-state">No items scanned yet in this session.</div>`;
        } else {
            list.innerHTML = sessionScans.map(item => `
                <div class="overlay-session-item">
                    <div>
                        <span class="badge ${item.type === 'Book' ? 'bg-primary' : (item.type === 'Student' ? 'bg-success' : 'bg-warning text-dark')} me-1" style="font-size:0.7rem;">${item.type}</span>
                        <strong style="color:#1e293b;">${escapeHtml(item.title)}</strong>
                        <div style="font-size:0.75rem; color:#64748b;">${escapeHtml(item.subtitle || item.identifier)}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge ${item.action === 'archived' ? 'bg-danger' : 'bg-success'}" style="font-size:0.75rem;">
                            ${item.action === 'archived' ? 'Archived' : 'Restored'}
                        </span>
                        <div style="font-size:0.7rem; color:#94a3b8;">${item.timestamp}</div>
                    </div>
                </div>
            `).join('');
        }
    }

    // Render in page table (filtered items)
    if (tbody) {
        if (itemsToRender.length === 0) {
            if (sessionScans.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted small">No items scanned in this session yet. Launch the scanner overlay or scan an item above.</td></tr>`;
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted small">No session log entries matched your filter criteria. <a href="javascript:void(0)" onclick="resetSessionFilters()" style="color:#800000; font-weight:700;">Reset Filters</a></td></tr>`;
            }
            return;
        }

        tbody.innerHTML = itemsToRender.map(item => `
            <tr>
                <td style="font-size:0.8rem; color:#6b7280;">${item.timestamp}</td>
                <td><span class="badge ${item.type === 'Book' ? 'bg-primary' : (item.type === 'Student' ? 'bg-success' : 'bg-warning text-dark')}">${item.type}</span></td>
                <td>
                    <div class="font-weight-bold">${escapeHtml(item.title)}</div>
                    <small class="text-muted">${escapeHtml(item.subtitle || '')}</small>
                </td>
                <td style="font-family:monospace; color:#800000; font-weight:600;">${escapeHtml(item.identifier)}</td>
                <td>
                    <span class="badge ${item.action === 'archived' ? 'bg-danger' : 'bg-success'}">
                        ${item.action === 'archived' ? 'Archived' : 'Restored'}
                    </span>
                </td>
            </tr>
        `).join('');
    }
}

function clearSessionLog() {
    sessionScans = [];
    resetSessionFilters();
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
@endsection