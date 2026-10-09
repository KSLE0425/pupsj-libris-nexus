@extends('layouts.admin')

@section('content')
<div class="ai-suggestions-page">
    <h2>Book Recommendations</h2>
    <p class="page-subtitle">Smart book suggestions based on borrowing patterns.</p>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <!-- Search Card -->
    <div class="card search-card">
        <div class="search-card-content">
            <div class="search-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M12 2a10 10 0 0 1 10 10c0 5-3 8-6 8l-4 2-4-2c-3 0-6-3-6-8a10 10 0 0 1 10-10z"/>
                    <path d="M9 12h.01"/>
                    <path d="M15 12h.01"/>
                    <path d="M12 16c-1 0-1.5-.5-2-1"/>
                </svg>
            </div>
            <div class="search-text">
                <h3>Book Suggestions</h3>
                <p>Smart book suggestions based on borrowing patterns</p>
            </div>
            <div class="search-form">
                <button id="refreshSuggestions" class="btn-primary" onclick="refreshSuggestions()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 4v6h-6"/>
                        <path d="M1 20v-6h6"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"/>
                        <path d="M20.49 15a9 9 0 0 1-14.85 3.36L1 14"/>
                    </svg>
                    <span>Refresh Suggestions</span>
                </button>
            </div>
        </div>
    </div>

    <!-- User Requisitions Section -->
    <div class="card" style="margin-bottom:28px;">
        <div class="card-header">
            <h4>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:6px;"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                User Requested Titles
            </h4>
            <span class="card-badge">{{ ($requisitions ?? collect())->count() }} {{ Str::plural('request', ($requisitions ?? collect())->count()) }}</span>
        </div>

        @if(($requisitions ?? collect())->isEmpty())
            <div style="text-align:center;padding:48px 24px;color:#9ca3af;">
                <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" style="margin:0 auto 14px;display:block;opacity:0.3;"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                <p style="font-size:0.9rem;margin:0;">No book requisitions submitted yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="req-table req-table-lg">
                    <thead>
                        <tr>
                            <th>Title Requested</th>
                            <th>Requested By</th>
                            <th>Justification</th>
                            <th>Current Status</th>
                            <th>Update Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requisitions as $req)
                        @php
                            $statusMap = [
                                'submitted' => ['bg'=>'rgba(255,199,44,0.18)','color'=>'#7a4700','label'=>'Pending'],
                                'approved'  => ['bg'=>'#dcfce7','color'=>'#15803d','label'=>'Approved'],
                                'rejected'  => ['bg'=>'rgba(128,0,0,0.1)','color'=>'#800000','label'=>'Rejected'],
                                'ordered'   => ['bg'=>'#dbeafe','color'=>'#1e40af','label'=>'Ordered'],
                                'received'  => ['bg'=>'#ede9fe','color'=>'#5b21b6','label'=>'Received'],
                            ];
                            $st = $statusMap[$req->status] ?? ['bg'=>'#f3f4f6','color'=>'#374151','label'=>ucfirst($req->status)];
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:700;font-size:0.95rem;color:#800000;line-height:1.3;">{{ $req->title ?? '—' }}</div>
                                @if($req->author ?? false)
                                    <div style="font-size:0.78rem;color:#888;margin-top:2px;">{{ $req->author }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight:600;font-size:0.88rem;color:#1a1a1a;">{{ $req->requester_name }}</div>
                                @if(isset($req->student_id) && $req->student_id)
                                    <div style="font-size:0.73rem;color:#888;margin-top:1px;">Student</div>
                                @elseif(isset($req->faculty_id) && $req->faculty_id)
                                    <div style="font-size:0.73rem;color:#888;margin-top:1px;">Faculty</div>
                                @endif
                            </td>
                            <td style="max-width:220px;">
                                <div style="font-size:0.84rem;color:#555;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;" title="{{ $req->justification ?? '' }}">
                                    {{ $req->justification ?: '—' }}
                                </div>
                            </td>
                            <td>
                                <span style="display:inline-block;background:{{ $st['bg'] }};color:{{ $st['color'] }};padding:5px 14px;border-radius:20px;font-size:0.78rem;font-weight:700;">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.book.suggestions.requisition.status', $req) }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                                    @csrf
                                    <select name="status" class="req-select-lg">
                                        <option value="approved"  {{ $req->status==='approved'  ?'selected':'' }}>Approved</option>
                                        <option value="ordered"   {{ $req->status==='ordered'   ?'selected':'' }}>Ordered</option>
                                        <option value="received"  {{ $req->status==='received'  ?'selected':'' }}>Received</option>
                                        <option value="rejected"  {{ $req->status==='rejected'  ?'selected':'' }}>Rejected</option>
                                        <option value="submitted" {{ $req->status==='submitted' ?'selected':'' }}>Pending</option>
                                    </select>
                                    <input type="text" name="admin_notes" class="req-notes-lg" placeholder="Admin notes…" value="{{ $req->admin_notes ?? '' }}">
                                    <button type="submit" class="req-save-btn">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- AI Suggestions Container -->
    <div class="card" style="margin-bottom:0; overflow:visible;">
        <div class="card-header">
            <h4>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-3px; margin-right:6px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                AI-Suggested Books
            </h4>
            <button id="refreshSuggestions" class="action-btn btn-save-req" onclick="refreshSuggestions()" style="padding:6px 14px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"/><path d="M20.49 15a9 9 0 0 1-14.85 3.36L1 14"/></svg>
                Refresh
            </button>
        </div>
        <div id="suggestions-container" style="padding:20px;">
            <div class="loading-state">
                <div class="spinner"></div>
                <p>Discovering new books that your students would love...</p>
                <small>This may take a few minutes</small>
            </div>
        </div>
    </div>
</div>

<style>
/* PUP Theme Variables */
.ai-suggestions-page {
    --pup-maroon: #800000;
    --pup-maroon-dark: #5a0000;
    --pup-gold: #FFC72C;
    --pup-gold-dark: #e6b328;
    --bg-main: #f5f5f5;
    --text: #1f2937;
    --text-muted: #6b7280;
    --border: #e5e7eb;
    --radius: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Page Header */
.ai-suggestions-page h2 {
    margin: 0 0 8px 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
    letter-spacing: -0.025em;
    word-break: break-word;
}

.ai-suggestions-page .page-subtitle {
    color: var(--text-muted);
    font-size: 0.9375rem;
    margin-bottom: 1.75rem;
}

/* Alert */
.alert-success {
    background: #ECFDF5;
    color: #065F46;
    padding: 1rem 1.25rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    border-left: 4px solid #10B981;
}

/* Requisitions section header */
.req-section-card { border-top: 4px solid #800000 !important; }
.req-section-head {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
    padding: 20px 24px;
    background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
    border-bottom: 3px solid #FFC72C;
}

/* Requisitions table */
.req-table { width:100%; border-collapse:collapse; font-size:0.875rem; }
.req-table th { background:#6a0000; color:white; padding:12px 18px; text-align:left; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; }
.req-table td { padding:15px 18px; border-bottom:1px solid var(--border); vertical-align:middle; }
.req-table tbody tr:last-child td { border-bottom: none; }
.req-table tbody tr:hover td { background:#fffbf0; }

/* Larger Update Status controls */
.req-select-lg {
    padding: 7px 10px; border: 1.5px solid #d1d5db; border-radius: 8px;
    font-size: 0.84rem; font-family: inherit; background: #fff; color: #333;
    cursor: pointer; min-width: 110px;
}
.req-select-lg:focus { outline: none; border-color: #800000; }
.req-notes-lg {
    padding: 7px 10px; border: 1.5px solid #d1d5db; border-radius: 8px;
    font-size: 0.84rem; font-family: inherit; min-width: 150px; flex: 1;
}
.req-notes-lg:focus { outline: none; border-color: #800000; }
.req-save-btn {
    display: inline-flex; align-items: center; gap: 5px;
    background: #FFC72C; color: #800000;
    border: none; border-radius: 8px;
    padding: 7px 16px; font-family: inherit; font-size: 0.84rem; font-weight: 700;
    cursor: pointer; white-space: nowrap; transition: all 0.18s;
}
.req-save-btn:hover { background: #e8b800; transform: translateY(-1px); }

/* Legacy small controls (kept for other tables) */
.req-select { padding:4px 8px; border:1px solid var(--border); border-radius:6px; font-size:0.78rem; font-family:inherit; }
.req-notes  { padding:4px 8px; border:1px solid var(--border); border-radius:6px; font-size:0.78rem; font-family:inherit; min-width:120px; }
.action-btn { display:inline-flex; align-items:center; gap:4px; padding:5px 11px; border-radius:6px; font-size:0.75rem; font-weight:600; cursor:pointer; transition:all 0.18s; border:1.5px solid transparent; font-family:inherit; line-height:1.4; white-space:nowrap; }
.action-btn:hover { transform:translateY(-1px); }
.btn-save-req { background:var(--pup-gold); color:var(--pup-maroon); border-color:var(--pup-gold); }
.btn-save-req:hover { background:var(--pup-gold-dark); }
.table-responsive { overflow-x:auto; }

/* Cards */
.card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
    margin-bottom: 1.75rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: white;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.card-header h4 {
    margin: 0;
    font-weight: 600;
    color: var(--pup-maroon);
    font-size: 1.125rem;
}

/* Search Card */
.search-card {
    background: linear-gradient(135deg, var(--pup-maroon) 0%, var(--pup-maroon-dark) 100%);
    border: none;
    margin-bottom: 1.75rem;
}

.search-card-content {
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.search-icon {
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.search-text {
    flex: 1;
    min-width: 180px;
}

.search-text h3 {
    margin: 0 0 0.375rem;
    color: white;
    font-size: 1.25rem;
}

.search-text p {
    margin: 0;
    color: rgba(255,255,255,0.85);
    font-size: 0.875rem;
}

.search-form {
    flex: 1;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    min-width: 200px;
}

.btn-primary {
    background: var(--pup-gold);
    color: var(--pup-maroon);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 48px;
}

.btn-primary:hover {
    background: var(--pup-gold-dark);
    transform: translateY(-2px);
}

.btn-primary span {
    display: inline;
}

/* Loading State */
.loading-state {
    background: white;
    border-radius: var(--radius);
    padding: 3rem;
    text-align: center;
    border: 1px solid var(--border);
}

.spinner {
    width: 50px;
    height: 50px;
    border: 3px solid var(--border);
    border-top-color: var(--pup-maroon);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1.5rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-state p {
    margin: 0 0 0.5rem;
    color: var(--text);
    font-size: 1rem;
}

.loading-state small {
    color: var(--text-muted);
    font-size: 0.8125rem;
}

/* Suggestion Card */
.suggestion-card {
    background: white;
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border);
    transition: all 0.2s;
}

.suggestion-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.suggestion-content {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.book-cover {
    width: 100px;
    height: 130px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
}

.book-cover-placeholder {
    width: 100px;
    height: 130px;
    background: var(--bg-main);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.book-cover-placeholder svg {
    stroke: var(--text-muted);
}

.suggestion-details {
    flex: 1;
    min-width: 200px;
}

.suggestion-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

.suggestion-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--pup-maroon);
    word-break: break-word;
}

.new-badge {
    display: inline-block;
    background: var(--pup-gold);
    color: var(--pup-maroon);
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    margin-left: 0.75rem;
    vertical-align: middle;
}

.suggestion-meta {
    color: var(--text-muted);
    font-size: 0.875rem;
    margin-bottom: 1rem;
    word-break: break-word;
}

.badge-subject {
    display: inline-block;
    background: #F3F4F6;
    color: #6B7280;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    margin-right: 0.5rem;
    margin-bottom: 0.25rem;
}

.badge-year {
    display: inline-block;
    background: #EEF2FF;
    color: #4F46E5;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
}

.rank-badge {
    background: var(--pup-gold);
    color: var(--pup-maroon);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.reason-box {
    background: var(--bg-main);
    padding: 1rem;
    border-radius: 10px;
    margin: 1rem 0;
    border-left: 4px solid var(--pup-maroon);
}

.reason-box strong {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--pup-maroon);
    font-size: 0.875rem;
}

.reason-box p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--text);
    line-height: 1.5;
}

.store-buttons {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.store-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.75rem;
    font-weight: 600;
    transition: all 0.2s;
    min-height: 36px;
}

.store-btn:hover {
    transform: translateY(-1px);
}

.store-btn.fullybooked {
    background: #2c3e50;
    color: white;
}

.store-btn.nbs {
    background: #e67e22;
    color: white;
}

.store-btn.amazon {
    background: #ff9900;
    color: #232f3e;
}

.store-btn.shopee {
    background: #ee4d2d;
    color: white;
}

.store-btn.default {
    background: var(--pup-maroon);
    color: white;
}

.store-btn span {
    display: inline;
}

/* Alert Boxes */
.alert-info {
    background: #EEF2FF;
    color: #4F46E5;
    padding: 1rem 1.25rem;
    border-radius: 10px;
    border-left: 4px solid #4F46E5;
}

.alert-danger {
    background: #FEF2F2;
    color: #991B1B;
    padding: 1rem 1.25rem;
    border-radius: 10px;
    border-left: 4px solid #EF4444;
}

/* ============================================ */
/* ENHANCED MOBILE RESPONSIVENESS */
/* ============================================ */

@media (max-width: 1024px) {
    .ai-suggestions-page {
        padding: 0 0.75rem;
    }
}

@media (max-width: 768px) {
    .ai-suggestions-page {
        padding: 0 0.5rem;
    }
    
    .ai-suggestions-page h2 {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
    }
    
    .search-card-content {
        flex-direction: column;
        text-align: center;
    }
    
    .search-icon {
        margin: 0 auto;
    }
    
    .search-text {
        text-align: center;
    }
    
    .search-form {
        justify-content: center;
        width: 100%;
    }
    
    .btn-primary {
        width: 100%;
        justify-content: center;
    }
    
    .suggestion-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .book-cover, .book-cover-placeholder {
        margin: 0 auto;
    }
    
    .suggestion-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .suggestion-title {
        text-align: center;
    }
    
    .new-badge {
        display: inline-block;
        margin-top: 0.25rem;
        margin-left: 0;
    }
    
    .store-buttons {
        justify-content: center;
    }
    
    .reason-box {
        text-align: left;
    }
    
    .loading-state {
        padding: 2rem 1rem;
    }
    
    .spinner {
        width: 40px;
        height: 40px;
    }
    
    .loading-state p {
        font-size: 0.875rem;
    }
    
    .alert-info, .alert-danger {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
}

@media (max-width: 480px) {
    .ai-suggestions-page h2 {
        font-size: 1.3rem;
    }
    
    .search-card-content {
        padding: 1rem;
    }
    
    .search-icon {
        padding: 0.75rem;
    }
    
    .search-icon svg {
        width: 20px;
        height: 20px;
    }
    
    .search-text h3 {
        font-size: 1rem;
    }
    
    .search-text p {
        font-size: 0.75rem;
    }
    
    .btn-primary {
        padding: 0.6rem 1rem;
        font-size: 0.8rem;
        min-height: 42px;
    }
    
    .btn-primary svg {
        width: 16px;
        height: 16px;
    }
    
    .suggestion-card {
        padding: 1rem;
    }
    
    .suggestion-title {
        font-size: 1rem;
    }
    
    .new-badge {
        font-size: 0.6rem;
        padding: 0.2rem 0.5rem;
    }
    
    .suggestion-meta {
        font-size: 0.75rem;
    }
    
    .badge-subject, .badge-year, .rank-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.6rem;
    }
    
    .reason-box {
        padding: 0.75rem;
    }
    
    .reason-box strong {
        font-size: 0.8rem;
    }
    
    .reason-box p {
        font-size: 0.75rem;
    }
    
    .store-btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.7rem;
        min-height: 32px;
    }
    
    .store-btn svg {
        width: 12px;
        height: 12px;
    }
}

/* Landscape mode optimization */
@media (max-width: 768px) and (orientation: landscape) {
    .search-card-content {
        flex-direction: row;
        text-align: left;
    }
    
    .search-icon {
        margin: 0;
    }
    
    .search-text {
        text-align: left;
    }
    
    .search-form {
        width: auto;
    }
    
    .btn-primary {
        width: auto;
    }
    
    .suggestion-content {
        flex-direction: row;
        text-align: left;
        align-items: flex-start;
    }
    
    .suggestion-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        text-align: left;
    }
    
    .suggestion-title {
        text-align: left;
    }
    
    .store-buttons {
        justify-content: flex-start;
    }
}
</style>

<script>
function refreshSuggestions() {
    document.getElementById('suggestions-container').innerHTML = `
        <div class="loading-state">
            <div class="spinner"></div>
            <p>Discovering new books that your students would love...</p>
            <small>This may take a few minutes</small>
        </div>
    `;
    fetchSuggestions();
}

function fetchSuggestions() {
    fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        renderSuggestions(data.suggestions);
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('suggestions-container').innerHTML = `
            <div class="alert-danger" style="padding: 1rem 1.25rem; border-radius: 10px; border-left: 4px solid #EF4444;">
                <strong>⚠️ Error loading suggestions</strong><br>
                Please try again later.
            </div>
        `;
    });
}

function renderSuggestions(suggestions) {
    if (!suggestions || suggestions.length === 0) {
        document.getElementById('suggestions-container').innerHTML = `
            <div class="alert-info" style="padding: 1rem 1.25rem; border-radius: 10px; border-left: 4px solid #4F46E5;">
                <strong>No new suggestions available</strong><br>
                Add more borrowing data to get better recommendations.
            </div>
        `;
        return;
    }
    
    let html = '';
    suggestions.forEach((suggestion, index) => {
        html += `
            <div class="suggestion-card">
                <div class="suggestion-content">
                    ${suggestion.cover_url ? `
                        <img src="${suggestion.cover_url}" class="book-cover" alt="${escapeHtml(suggestion.title)}">
                    ` : `
                        <div class="book-cover-placeholder">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                            </svg>
                        </div>
                    `}
                    <div class="suggestion-details">
                        <div class="suggestion-header">
                            <div>
                                <h4 class="suggestion-title">
                                    ${escapeHtml(suggestion.title)}
                                    <span class="new-badge">NEW SUGGESTION</span>
                                </h4>
                                <div class="suggestion-meta">
                                    by ${escapeHtml(suggestion.author || 'Unknown Author')}
                                    ${suggestion.subject ? `<span class="badge-subject">${escapeHtml(suggestion.subject)}</span>` : ''}
                                    ${suggestion.year ? `<span class="badge-year">${suggestion.year}</span>` : ''}
                                </div>
                            </div>
                            <span class="rank-badge">#${index + 1}</span>
                        </div>
                        
                        <div class="reason-box">
                            <strong>Why add this book?</strong>
                            <p>${escapeHtml(suggestion.reason)}</p>
                        </div>
                        
                        
                    </div>
                </div>
            </div>
        `;
    });
    
    document.getElementById('suggestions-container').innerHTML = html;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    fetchSuggestions();
});
</script>
@endsection
