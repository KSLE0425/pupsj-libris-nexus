@extends('layouts.admin')

@section('title', 'Books')

@php
    if (!isset($usages))  { $usages  = collect([]); }
    if (!isset($books))   { $books   = []; }

    $booksArr           = is_array($books) ? $books : $books->all();
    $totalBooks         = count($booksArr);
    $totalCopies        = collect($booksArr)->sum('copies');
    $activeBorrowCounts = $usages->where('status', 'active')->groupBy('book_id')->map->count();
    $availableCount     = collect($booksArr)->filter(fn($b) => !$b->is_condemned && ($b->status === 'available' || $b->status === 'active'))->count();
    $borrowedCount      = collect($booksArr)->where('status', 'borrowed')->count();
    $condemnedCount     = collect($booksArr)->filter(fn($b) => $b->is_condemned || $b->status === 'condemned')->count();
    $archivedCount      = collect($booksArr)->where('status', 'archived')->count();
@endphp
@section('content')
<style>
/* ── Variables ─────────────────────────────────────── */
.books-page {
    --pup-maroon: #800000;
    --pup-maroon-dark: #5a0000;
    --pup-gold: #FFC72C;
    --pup-gold-dark: #e6b328;
    --bg-main: #f5f5f5;
    --text: #1f2937;
    --text-muted: #6b7280;
    --border: #e5e7eb;
    --radius: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,0.08);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* ── Page Header ─────────────────────────────────── */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
    border-left: 4px solid var(--pup-gold);
    padding-left: 16px;
}
.page-header h1 {
    margin: 0 0 4px;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
    letter-spacing: -0.025em;
}
.page-header p { margin: 0; color: var(--text-muted); font-size: 0.9375rem; }

/* ── Alerts ─────────────────────────────────────── */
.alert { padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem; border-left: 4px solid; }
.alert-success { background: #ECFDF5; color: #065F46; border-left-color: #10B981; }
.alert-danger  { background: #FEF2F2; color: #991B1B; border-left-color: #EF4444; }

/* ── Stat Cards ──────────────────────────────────── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}
.stat-mini {
    background: white;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    border: 1px solid var(--border);
    border-left: 4px solid var(--border);
    box-shadow: var(--shadow);
    transition: transform 0.18s, box-shadow 0.18s;
}
.stat-mini:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-mini.total     { border-left-color: var(--pup-maroon); }
.stat-mini.available { border-left-color: #10B981; }
.stat-mini.borrowed  { border-left-color: #6366F1; }
.stat-mini.condemned { border-left-color: #EF4444; }
.stat-mini.archived  { border-left-color: #F59E0B; }
.stat-mini-icon {
    margin: 0 auto 8px;
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.stat-mini.total     .stat-mini-icon { background: rgba(128,0,0,0.1); color: var(--pup-maroon); }
.stat-mini.available .stat-mini-icon { background: #ECFDF5; color: #059669; }
.stat-mini.borrowed  .stat-mini-icon { background: #EEF2FF; color: #4F46E5; }
.stat-mini.condemned .stat-mini-icon { background: #FEF2F2; color: #DC2626; }
.stat-mini.archived  .stat-mini-icon { background: #FEF3C7; color: #D97706; }
.stat-mini-num {
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1.1;
    color: var(--pup-maroon);
}
.stat-mini.available .stat-mini-num { color: #059669; }
.stat-mini.borrowed  .stat-mini-num { color: #4F46E5; }
.stat-mini.condemned .stat-mini-num { color: #DC2626; }
.stat-mini.archived  .stat-mini-num { color: #D97706; }
.stat-mini-label { font-size: 0.78rem; color: var(--text-muted); margin-top: 3px; font-weight: 500; }


/* ── Filter Bar ──────────────────────────────────── */
.filter-sort-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
    background: white;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px 16px;
    align-items: flex-end;
    box-shadow: var(--shadow);
}
.filter-group { flex: 1 1 155px; display: flex; flex-direction: column; gap: 4px; }
.filter-label { font-size: 0.71rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; }
.filter-input, .filter-select {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 0.875rem;
    background: white;
    transition: all 0.2s;
    font-family: inherit;
}
.filter-input:focus, .filter-select:focus {
    outline: none;
    border-color: var(--pup-maroon);
    box-shadow: 0 0 0 3px rgba(128,0,0,0.1);
}
.btn-reset {
    background: var(--pup-maroon);
    color: white;
    border: none;
    padding: 9px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    align-self: flex-end;
    font-family: inherit;
}
.btn-reset:hover { background: var(--pup-maroon-dark); transform: translateY(-1px); }

/* ── Cards ───────────────────────────────────────── */
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
    padding: 1.125rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: white;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.card-header h3 { margin: 0; font-weight: 600; color: var(--pup-maroon); font-size: 1.05rem; }
.card-badge {
    font-size: 0.75rem;
    color: var(--text-muted);
    background: var(--bg-main);
    padding: 0.3rem 0.75rem;
    border-radius: 20px;
    display: inline-block;
}

/* ── Table ───────────────────────────────────────── */
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
    background: #6a0000;
    color: white;
    padding: 12px 14px;
    text-align: left;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.data-table td {
    padding: 11px 14px;
    border-bottom: 1px solid var(--border);
    font-size: 0.875rem;
    vertical-align: middle;
}
.data-table tbody tr:nth-child(even) { background: #fafafa; }
.data-table tbody tr:hover { background: #FEFCE8; }

/* ── Status Badges ───────────────────────────────── */
.badge-available, .badge-archived, .badge-condemned, .badge-borrowed, .badge-reserved {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-available { background: rgba(255,199,44,0.18); color: #5a2d00; border-color: rgba(255,199,44,0.5); }
.badge-archived  { background: rgba(128,0,0,0.07); color: #800000; border-color: rgba(128,0,0,0.2); }
.badge-condemned { background: #800000; color: white; border-color: #800000; }
.badge-borrowed  { background: rgba(128,0,0,0.1); color: #800000; border-color: rgba(128,0,0,0.28); }
.badge-reserved  { background: #fef9c3; color: #854d0e; border-color: #fde68a; }
.status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; border: 1px solid transparent; }
.status-badge.borrowed { background: rgba(128,0,0,0.1); color: #800000; border-color: rgba(128,0,0,0.28); }
.status-badge.returned { background: rgba(255,199,44,0.18); color: #5a2d00; border-color: rgba(255,199,44,0.5); }


/* ── Edition Group Row ───────────────────────────── */
.edition-group-row td {
    background: linear-gradient(90deg, rgba(128,0,0,0.06), transparent);
    padding: 8px 14px 8px 10px;
    border-bottom: 1px solid rgba(128,0,0,0.1);
    cursor: pointer;
    user-select: none;
}
.edition-group-row:hover td { background: linear-gradient(90deg, rgba(128,0,0,0.1), transparent); }
.edition-group-toggle {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 0.8rem; font-weight: 700; color: #800000;
}
.edition-chevron { transition: transform 0.2s; }

/* ── Action Buttons ──────────────────────────────── */
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 11px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s;
    border: 1.5px solid transparent;
    text-decoration: none;
    font-family: inherit;
    line-height: 1.4;
    white-space: nowrap;
}
.action-btn:hover { transform: translateY(-1px); }
.btn-edit      { background: #FFF7DC; color: #6b3d00; border-color: #FFC72C; }
.btn-edit:hover { background: #FFC72C; color: #5a0000; border-color: #FFC72C; }
.btn-archive   { background: rgba(128,0,0,0.05); color: #800000; border-color: rgba(128,0,0,0.35); }
.btn-archive:hover { background: #800000; color: white; border-color: #800000; }
.btn-unarchive { background: rgba(255,199,44,0.1); color: #5a3000; border-color: rgba(255,199,44,0.55); }
.btn-unarchive:hover { background: #FFC72C; color: #5a0000; border-color: #FFC72C; }
.btn-condemn   { background: #800000; color: white; border-color: #800000; }
.btn-condemn:hover { background: #5a0000; color: white; border-color: #5a0000; }
.btn-uncondemn { background: #FFC72C; color: #5a0000; border-color: #FFC72C; }
.btn-uncondemn:hover { background: #e6b328; color: #5a0000; border-color: #e6b328; }
.btn-print     { background: rgba(128,0,0,0.04); color: #800000; border-color: rgba(128,0,0,0.22); border-style: dashed; }
.btn-print:hover { background: rgba(128,0,0,0.1); border-style: solid; }
.actions-cell  { display: flex; gap: 4px; flex-wrap: wrap; align-items: flex-start; }


/* ── Empty State ─────────────────────────────────── */
.empty-state { text-align: center; padding: 3rem; color: var(--text-muted); }
.empty-state svg { margin-bottom: 1rem; opacity: 0.45; }
.empty-state p { margin: 0; font-size: 0.9375rem; }

/* ── Primary Button ──────────────────────────────── */
.btn-primary {
    background: var(--pup-gold); color: var(--pup-maroon);
    border: none; padding: 0.625rem 1.25rem; border-radius: 8px;
    font-weight: 700; cursor: pointer; transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 0.5rem;
    text-decoration: none; font-family: inherit; font-size: 0.9rem; white-space: nowrap;
}
.btn-primary:hover { background: var(--pup-gold-dark); transform: translateY(-1px); }


/* ── Pagination ──────────────────────────────────── */
.pagination-container {
    padding: 1rem 1.5rem; border-top: 1px solid var(--border);
    background: white; display: flex; justify-content: space-between;
    align-items: center; flex-wrap: wrap; gap: 0.75rem;
}
.pagination-info { font-size: 0.85rem; color: var(--text-muted); }
.pagination-controls { display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; }
.pagination-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 12px; background: white; border: 1px solid var(--border);
    border-radius: 7px; color: var(--text); font-size: 0.85rem;
    font-weight: 500; cursor: pointer; transition: all 0.2s; font-family: inherit;
}
.pagination-btn:hover:not(:disabled) { background: var(--pup-gold); border-color: var(--pup-gold); color: var(--pup-maroon); }
.pagination-btn:disabled { opacity: 0.45; cursor: not-allowed; }
.page-numbers { display: flex; gap: 4px; flex-wrap: wrap; }
.page-number {
    min-width: 34px; height: 34px; display: flex; align-items: center;
    justify-content: center; padding: 0 6px; background: white;
    border: 1px solid var(--border); border-radius: 7px; color: var(--text);
    font-size: 0.85rem; font-weight: 500; cursor: pointer;
    transition: all 0.2s; font-family: inherit;
}
.page-number:hover { background: var(--pup-gold); border-color: var(--pup-gold); color: var(--pup-maroon); }
.page-number.active { background: var(--pup-maroon); border-color: var(--pup-maroon); color: white; }

/* ── Condemn Modal Animation ─────────────────────── */
@keyframes condemnSlideIn {
    from { opacity: 0; transform: translateY(-28px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0)    scale(1); }
}
@keyframes condemnBackdropIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* ── Tab Navigation ───────────────────────────────── */
.books-tabs-nav {
    display: flex;
    gap: 10px;
    margin-bottom: 22px;
    border-bottom: 2px solid var(--border);
    padding-bottom: 10px;
    flex-wrap: wrap;
}
.tab-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    border: 1.5px solid var(--border);
    background: white;
    color: var(--text-muted);
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
    box-shadow: var(--shadow);
}
.tab-pill:hover {
    color: var(--pup-maroon);
    background: #FFFDF5;
    border-color: rgba(255,199,44,0.6);
    transform: translateY(-1px);
}
.tab-pill.active {
    background: var(--pup-maroon);
    color: white;
    border-color: var(--pup-maroon);
    box-shadow: 0 4px 14px rgba(128,0,0,0.25);
}
.tab-pill.active .pill-badge {
    background: var(--pup-gold);
    color: var(--pup-maroon);
}
.pill-badge {
    font-size: 0.72rem;
    padding: 3px 9px;
    border-radius: 20px;
    background: rgba(0,0,0,0.07);
    color: inherit;
    font-weight: 700;
}
.copy-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
}
.copy-pill.total     { background: rgba(128,0,0,0.09); color: var(--pup-maroon); }
.copy-pill.available { background: #ECFDF5; color: #059669; }
.copy-pill.borrowed  { background: #EEF2FF; color: #4F46E5; }
.copy-pill.empty     { background: #FEF2F2; color: #DC2626; }

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 768px) {
    .books-page { padding: 0 0.5rem; }
    .page-header h1 { font-size: 1.4rem; }
    .data-table th, .data-table td { padding: 8px; font-size: 0.75rem; }
    .filter-sort-bar { gap: 8px; padding: 12px; }
    .stats-row { grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
    .tab-pill { padding: 8px 14px; font-size: 0.82rem; }
}
</style>
<div class="books-page">

    {{-- ── Header ──────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h1>Books Management</h1>
            <p>Manage the library catalog — add, edit, archive, or condemn books.</p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('admin.books.create') }}" class="btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                Add New Book
            </a>
            <button type="button" onclick="openImportModal()" class="btn-primary" style="background:#5a0000; color:#FFC72C; border:1px solid #FFC72C;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Import Books (CSV / Excel)
            </button>
            <a href="/admin/archive-scanner" class="btn-primary" style="background:var(--pup-maroon); color:white;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h.01M11 7h.01M15 7h.01M7 11h10M7 15h7"/></svg>
                Batch Scan &amp; Archive
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- ── Stat Cards ───────────────────────────────────── --}}
    <div class="stats-row">
        <div class="stat-mini total">
            <div class="stat-mini-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
            <div class="stat-mini-num">{{ $totalBooks }}</div>
            <div class="stat-mini-label">Total Books</div>
        </div>
        <div class="stat-mini available">
            <div class="stat-mini-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="stat-mini-num">{{ $availableCount }}</div>
            <div class="stat-mini-label">Available</div>
        </div>
        <div class="stat-mini borrowed">
            <div class="stat-mini-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-mini-num">{{ $borrowedCount }}</div>
            <div class="stat-mini-label">Borrowed</div>
        </div>
        <div class="stat-mini condemned">
            <div class="stat-mini-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="stat-mini-num">{{ $condemnedCount }}</div>
            <div class="stat-mini-label">Condemned</div>
        </div>
        <div class="stat-mini archived">
            <div class="stat-mini-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
            </div>
            <div class="stat-mini-num">{{ $archivedCount }}</div>
            <div class="stat-mini-label">Archived</div>
        </div>
    </div>


    {{-- ── Tab Navigation ───────────────────────────────── --}}
    <div class="books-tabs-nav">
        <button type="button" class="tab-pill active" id="tabCatalogBtn" onclick="switchBookTab('catalog')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            All Books
            <span class="pill-badge">{{ $totalBooks }}</span>
        </button>
        <button type="button" class="tab-pill" id="tabCopiesBtn" onclick="switchBookTab('copies')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            Copies & Inventory
            <span class="pill-badge" style="background:#800000; color:#fff;">{{ $totalCopies }} copies</span>
        </button>
    </div>

    {{-- ── TAB 1: ALL BOOKS CATALOG ─────────────────────── --}}
    <div id="tab-catalog-view">
        {{-- Filter Bar --}}
        <div class="filter-sort-bar">
            <div class="filter-group" style="flex:2 1 220px;">
                <span class="filter-label">Search</span>
                <input type="text" id="bookSearch" class="filter-input" placeholder="Title, author, ISBN, Accession No, call no…">
            </div>
            <div class="filter-group">
                <span class="filter-label">Collection</span>
                <select id="collectionFilter" class="filter-select">
                    <option value="">All Collections</option>
                    @foreach($collectionTypes ?? [] as $ct)
                        <option value="{{ strtolower($ct->name) }}">{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Status</span>
                <select id="statusFilter" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="available">Available</option>
                    <option value="borrowed">Borrowed</option>
                    <option value="archived">Archived</option>
                    <option value="condemned">Condemned</option>
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Research Type</span>
                <select id="researchTypeFilter" class="filter-select">
                    <option value="">All</option>
                    <option value="capstone">Capstone</option>
                    <option value="thesis">Thesis</option>
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Program / Course</span>
                <select id="programFilter" class="filter-select">
                    <option value="">All Programs</option>
                    @foreach($courses ?? [] as $course)
                        <option value="{{ strtolower($course->name) }}">{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Sort By</span>
                <select id="sortSelect" class="filter-select">
                    <option value="title">Title A–Z</option>
                    <option value="author">Author A–Z</option>
                    <option value="year-desc">Newest First</option>
                    <option value="year-asc">Oldest First</option>
                </select>
            </div>
            <button class="btn-reset" onclick="resetBookFilters()">Reset</button>
        </div>

        {{-- Table Card --}}
        <div class="card">
            <div class="card-header">
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <h3 style="margin:0;">Books Catalog</h3>
                    <button type="button" id="btnPrintSelectedQr" class="btn-primary" style="display:none; background:#800000; color:#fff; padding:6px 14px; font-size:0.82rem; align-items:center; gap:6px; border:1px solid rgba(255,255,255,0.2);" onclick="printSelectedBookQrs()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Print Selected QR Codes (<span id="selectedBooksCount">0</span>)
                    </button>
                </div>
                <span class="card-badge" id="visibleCount">{{ count($books) }} books</span>
            </div>
            @if(empty($books) || count($books) === 0)
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    <p>No books in catalog yet. <a href="{{ route('admin.books.create') }}" style="color:var(--pup-maroon); font-weight:600;">Add your first book</a></p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:38px; text-align:center;">
                                    <input type="checkbox" id="selectAllBooks" onchange="toggleSelectAllBooks(this)" title="Select all visible books" style="cursor:pointer; width:16px; height:16px; accent-color:#FFC72C;">
                                </th>
                                <th style="white-space:nowrap;">Accession No.</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Collection</th>
                                <th>Status</th>
                                <th style="text-align:center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="booksTableBody">
                        @foreach($books as $book)
                        @php
                            $bookStatus = $book->is_condemned ? 'condemned' : ($book->status ?? 'available');
                        @endphp
                        <tr data-id="{{ $book->id }}"
                            data-title="{{ strtolower($book->title) }}"
                            data-author="{{ strtolower($book->author ?? '') }}"
                            data-isbn="{{ strtolower($book->isbn ?? '') }}"
                            data-accession="{{ strtolower($book->accession_number ?? '') }}"
                            data-callno="{{ strtolower($book->call_number ?? '') }}"
                            data-collection="{{ strtolower($book->collection ?? '') }}"
                            data-status="{{ $bookStatus }}"
                            data-year="{{ $book->publication_year ?? 0 }}"
                            data-research-type="{{ strtolower($book->research_type ?? '') }}"
                            data-course="{{ strtolower($book->course->name ?? '') }}"
                            @if($book->is_condemned || $book->status === 'condemned') style="background:rgba(239,68,68,0.04); border-left:3px solid #EF4444;" @endif>
                            <td style="text-align:center; vertical-align:middle;">
                                <input type="checkbox" class="book-select-chk" value="{{ $book->id }}"
                                    data-id="{{ $book->id }}"
                                    data-title="{{ addslashes($book->title) }}"
                                    data-author="{{ addslashes($book->author ?? '') }}"
                                    data-accession="{{ addslashes($book->accession_number ?? '') }}"
                                    data-isbn="{{ addslashes($book->isbn ?? '') }}"
                                    data-callno="{{ addslashes($book->call_number ?? '') }}"
                                    onchange="updateSelectedBooksCount()"
                                    style="cursor:pointer; width:16px; height:16px; accent-color:#800000;">
                            </td>
                            <td style="white-space:nowrap; vertical-align:middle;">
                                <span class="badge" style="background:rgba(128,0,0,0.08); color:var(--pup-maroon); padding:4px 8px; border-radius:6px; font-family:monospace; font-weight:700; font-size:0.8rem; border:1px solid rgba(128,0,0,0.18);">
                                    {{ $book->accession_number ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight:600; color:var(--pup-maroon);">{{ $book->title }}</span>
                                @if($book->isbn)
                                    <small style="display:block; color:var(--text-muted); font-size:0.72rem; margin-top:2px;">ISBN: {{ $book->isbn }}</small>
                                @endif
                            </td>
                            <td>{{ $book->author ?? '—' }}</td>
                            <td>{{ $book->collection ?? '—' }}</td>
                            <td>
                                @if($book->is_condemned || $book->status === 'condemned')
                                    <span class="badge-condemned">Condemned</span>
                                @elseif($book->status === 'archived')
                                    <span class="badge-archived">Archived</span>
                                @elseif(($book->pending_reservations_count ?? 0) > 0)
                                    <span class="badge-reserved">Reserved</span>
                                @elseif($book->status === 'active' || $book->status === 'available')
                                    <span class="badge-available">Available</span>
                                @else
                                    <span class="badge-borrowed">{{ ucfirst($book->status ?? 'Unknown') }}</span>
                                @endif
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <div class="actions-cell" style="justify-content:center;">
                                    {{-- Edit --}}
                                    <a href="{{ route('admin.books.edit', $book->id) }}" class="action-btn btn-edit">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </a>

                                    @if($book->is_condemned || $book->status === 'condemned')
                                        {{-- Uncondemn --}}
                                        <form method="POST" action="{{ route('admin.books.uncondemn', $book) }}" style="display:inline;" onsubmit="return confirm('Remove condemnation from this book?');">
                                            @csrf
                                            <button type="submit" class="action-btn btn-uncondemn">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                Uncondemn
                                            </button>
                                        </form>
                                    @elseif($book->status === 'archived')
                                        {{-- Unarchive --}}
                                        <form method="POST" action="{{ route('admin.books.unarchive', $book) }}" style="display:inline;" onsubmit="return confirm('Unarchive this book?');">
                                            @csrf
                                            <button type="submit" class="action-btn btn-unarchive">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                Unarchive
                                            </button>
                                        </form>
                                    @else
                                        {{-- Archive --}}
                                        <form method="POST" action="{{ route('admin.books.archive', $book) }}" style="display:inline;" onsubmit="return confirm('Archive this book? It will be removed from active circulation.');">
                                            @csrf
                                            <button type="submit" class="action-btn btn-archive">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                                                Archive
                                            </button>
                                        </form>
                                        {{-- Condemn --}}
                                        <button type="button" class="action-btn btn-condemn" onclick="openCondemnModal({{ $book->id }}, {{ json_encode($book->title) }}, '{{ route('admin.books.condemn', $book) }}')">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                            Condemn
                                        </button>
                                    @endif

                                    @if(!$book->is_condemned && $book->status !== 'condemned' && $book->status !== 'archived')
                                        {{-- Print QR Code --}}
                                        <button type="button" class="action-btn btn-print" onclick="printSingleBookQr({{ $book->id }}, '{{ addslashes($book->title) }}', '{{ addslashes($book->author ?? '') }}', '{{ addslashes($book->accession_number ?? '') }}', '{{ addslashes($book->call_number ?? '') }}')" title="Print QR Code Label">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                            Print QR
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ── TAB 2: COPIES INVENTORY ──────────────────────── --}}
    <div id="tab-copies-view" style="display:none;">
        {{-- Copies Filter Bar --}}
        <div class="filter-sort-bar">
            <div class="filter-group" style="flex:2 1 220px;">
                <span class="filter-label">Search Copies</span>
                <input type="text" id="copiesSearch" class="filter-input" placeholder="Title, author, ISBN, Accession No…">
            </div>
            <div class="filter-group">
                <span class="filter-label">Sort by Copies</span>
                <select id="copiesSortSelect" class="filter-select">
                    <option value="most-copies">Most Copies First</option>
                    <option value="least-copies">Least Copies First</option>
                    <option value="most-borrowed">Most Borrowed Copies</option>
                    <option value="title">Title A–Z</option>
                    <option value="author">Author A–Z</option>
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Copy Availability</span>
                <select id="copiesAvailabilityFilter" class="filter-select">
                    <option value="">All Copy Levels</option>
                    <option value="available">Available (> 0 copies)</option>
                    <option value="low-stock">Low Stock (≤ 1 copy)</option>
                    <option value="out-of-stock">Out of Stock (0 available)</option>
                    <option value="multiple">Multiple Copies (> 1)</option>
                    <option value="single">Single Copy (1 copy)</option>
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Collection</span>
                <select id="copiesCollectionFilter" class="filter-select">
                    <option value="">All Collections</option>
                    @foreach($collectionTypes ?? [] as $ct)
                        <option value="{{ strtolower($ct->name) }}">{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-reset" onclick="resetCopiesFilters()">Reset</button>
        </div>

        {{-- Copies Table Card --}}
        <div class="card">
            <div class="card-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <h3>Copies & Physical Inventory</h3>
                    <span class="card-badge" style="background:rgba(128,0,0,0.1); color:var(--pup-maroon); font-weight:700;">
                        {{ $totalCopies }} Total Copies across {{ $totalBooks }} Titles
                    </span>
                </div>
                <span class="card-badge" id="copiesVisibleCount">{{ count($books) }} items shown</span>
            </div>
            @if(empty($books) || count($books) === 0)
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    <p>No book inventory found.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="white-space:nowrap;">Accession No.</th>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Collection</th>
                                <th style="text-align:center;">Total Copies</th>
                                <th style="text-align:center;">Available</th>
                                <th style="text-align:center;">In-Use / Borrowed</th>
                                <th>Status</th>
                                <th style="text-align:center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="copiesTableBody">
                        @foreach($books as $book)
                        @php
                            $totalCopyNum = (int)($book->copies ?? 1);
                            $borrowedCopyNum = (int)($activeBorrowCounts[$book->id] ?? 0);
                            $availableCopyNum = max(0, $totalCopyNum - $borrowedCopyNum);
                            $bookStatus = $book->is_condemned ? 'condemned' : ($book->status ?? 'available');
                        @endphp
                        <tr data-title="{{ strtolower($book->title) }}"
                            data-author="{{ strtolower($book->author ?? '') }}"
                            data-isbn="{{ strtolower($book->isbn ?? '') }}"
                            data-accession="{{ strtolower($book->accession_number ?? '') }}"
                            data-collection="{{ strtolower($book->collection ?? '') }}"
                            data-total-copies="{{ $totalCopyNum }}"
                            data-available-copies="{{ $availableCopyNum }}"
                            data-borrowed-copies="{{ $borrowedCopyNum }}"
                            data-status="{{ $bookStatus }}">
                            <td style="white-space:nowrap; vertical-align:middle;">
                                <span class="badge" style="background:rgba(128,0,0,0.08); color:var(--pup-maroon); padding:4px 8px; border-radius:6px; font-family:monospace; font-weight:700; font-size:0.8rem; border:1px solid rgba(128,0,0,0.18);">
                                    {{ $book->accession_number ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight:600; color:var(--pup-maroon);">{{ $book->title }}</div>
                                @if($book->isbn)
                                    <small style="color:var(--text-muted); font-size:0.72rem;">ISBN: {{ $book->isbn }}</small>
                                @endif
                                @if($book->loc_number)
                                    <small style="color:var(--text-muted); font-size:0.72rem; margin-left:8px;">Call No: {{ $book->loc_number }}</small>
                                @endif
                            </td>
                            <td>{{ $book->author ?? '—' }}</td>
                            <td>{{ $book->collection ?? '—' }}</td>
                            <td style="text-align:center;">
                                <span class="copy-pill total">{{ $totalCopyNum }} {{ Str::plural('copy', $totalCopyNum) }}</span>
                            </td>
                            <td style="text-align:center;">
                                @if($availableCopyNum > 0)
                                    <span class="copy-pill available">{{ $availableCopyNum }} Available</span>
                                @else
                                    <span class="copy-pill empty">0 Available</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($borrowedCopyNum > 0)
                                    <span class="copy-pill borrowed">{{ $borrowedCopyNum }} In Use</span>
                                @else
                                    <span style="color:var(--text-muted); font-size:0.8rem;">0</span>
                                @endif
                            </td>
                            <td>
                                @if($book->is_condemned || $book->status === 'condemned')
                                    <span class="badge-condemned">Condemned</span>
                                @elseif($book->status === 'archived')
                                    <span class="badge-archived">Archived</span>
                                @elseif($availableCopyNum > 0)
                                    <span class="badge-available">In Circulation</span>
                                @else
                                    <span class="badge-borrowed">All Checked Out</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <div class="actions-cell" style="justify-content:center;">
                                    <a href="{{ route('admin.books.edit', $book->id) }}" class="action-btn btn-edit">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit Copies
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

<script>
// ── Tab Switcher ───────────────────────────────────────
function switchBookTab(tab) {
    const catalogView = document.getElementById('tab-catalog-view');
    const copiesView  = document.getElementById('tab-copies-view');
    const catalogBtn  = document.getElementById('tabCatalogBtn');
    const copiesBtn   = document.getElementById('tabCopiesBtn');

    if (tab === 'copies') {
        if (catalogView) catalogView.style.display = 'none';
        if (copiesView)  copiesView.style.display  = 'block';
        if (catalogBtn)  catalogBtn.classList.remove('active');
        if (copiesBtn)   copiesBtn.classList.add('active');
        if (typeof window.initCopiesPage === 'function') {
            window.initCopiesPage();
        }
    } else {
        if (catalogView) catalogView.style.display = 'block';
        if (copiesView)  copiesView.style.display  = 'none';
        if (catalogBtn)  catalogBtn.classList.add('active');
        if (copiesBtn)   copiesBtn.classList.remove('active');
        if (typeof window.initBooksPage === 'function') {
            window.initBooksPage();
        }
    }
}

// ── Batch Selection & QR Code Printing ──────────────────────────
function toggleSelectAllBooks(master) {
    const isChecked = master.checked;
    const visibleRows = Array.from(document.querySelectorAll('#booksTableBody tr')).filter(r => r.style.display !== 'none');
    visibleRows.forEach(row => {
        const chk = row.querySelector('.book-select-chk');
        if (chk) chk.checked = isChecked;
    });
    updateSelectedBooksCount();
}

function updateSelectedBooksCount() {
    const chks = Array.from(document.querySelectorAll('#booksTableBody .book-select-chk:checked'));
    const count = chks.length;
    const countEl = document.getElementById('selectedBooksCount');
    const btn = document.getElementById('btnPrintSelectedQr');
    if (countEl) countEl.textContent = count;
    if (btn) btn.style.display = count > 0 ? 'inline-flex' : 'none';

    const allChks = Array.from(document.querySelectorAll('#booksTableBody .book-select-chk'));
    const master = document.getElementById('selectAllBooks');
    if (master && allChks.length > 0) {
        master.checked = (chks.length === allChks.length);
        master.indeterminate = (chks.length > 0 && chks.length < allChks.length);
    }
}

function printSelectedBookQrs() {
    const checked = Array.from(document.querySelectorAll('#booksTableBody .book-select-chk:checked'));
    if (checked.length === 0) {
        alert('Please select at least one book to print QR codes.');
        return;
    }
    const items = checked.map(chk => ({
        id: chk.dataset.id || chk.value,
        title: chk.dataset.title || 'Untitled',
        author: chk.dataset.author || '',
        accession: chk.dataset.accession || '',
        isbn: chk.dataset.isbn || '',
        callno: chk.dataset.callno || ''
    }));
    openQrPrintWindow(items);
}

function printSingleBookQr(id, title, author, accession, callno) {
    openQrPrintWindow([{
        id: id,
        title: title || 'Untitled',
        author: author || '',
        accession: accession || '',
        callno: callno || ''
    }]);
}

function openQrPrintWindow(items) {
    const printWin = window.open('', '_blank', 'width=900,height=700');
    if (!printWin) {
        alert('Please allow popups to print QR code labels.');
        return;
    }
    printWin.document.open();
    printWin.document.write(generateQrPrintHtml(items));
    printWin.document.close();
}

function generateQrPrintHtml(items) {
    let cardsHtml = items.map(item => `
        <div class="qr-sticker">
            <div class="qr-sticker-header">
                <div class="qr-brand">PUPSJ LIBRIS NEXUS</div>
                <div class="qr-sub">Polytechnic University of the Philippines — San Juan</div>
            </div>
            <div class="qr-body">
                <div class="qr-code-box">
                    <img src="/api/qrcode/books/${item.id}" alt="QR Code" class="qr-img">
                </div>
                <div class="qr-meta">
                    <div class="qr-title">${escapeHtml(item.title)}</div>
                    ${item.author ? `<div class="qr-author">By: ${escapeHtml(item.author)}</div>` : ''}
                    <div class="qr-pill-wrap">
                        ${item.accession ? `<span class="qr-pill">ACC: <strong>${escapeHtml(item.accession)}</strong></span>` : ''}
                        ${item.callno ? `<span class="qr-pill">CALL: <strong>${escapeHtml(item.callno)}</strong></span>` : ''}
                    </div>
                </div>
            </div>
            <div class="qr-sticker-footer">Scan at Kiosk for instant borrow / return</div>
        </div>
    `).join('');

    return `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Print QR Code Labels</title>
<style>
@page { margin: 10mm; size: auto; }
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; margin: 0; padding: 16px; background: #f8fafc; color: #111; }
.qr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
.qr-sticker { border: 1.5px solid #800000; border-radius: 10px; padding: 10px 12px; page-break-inside: avoid; background: #fff; display: flex; flex-direction: column; justify-content: space-between; min-height: 160px; box-sizing: border-box; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.qr-sticker-header { border-bottom: 1px solid #fee2e2; padding-bottom: 4px; margin-bottom: 6px; text-align: center; }
.qr-brand { font-size: 0.72rem; font-weight: 800; color: #800000; letter-spacing: 0.5px; }
.qr-sub { font-size: 0.55rem; color: #6b7280; }
.qr-body { display: flex; align-items: center; gap: 10px; }
.qr-code-box { width: 84px; height: 84px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.qr-img { width: 84px; height: 84px; display: block; object-fit: contain; }
.qr-meta { flex: 1; min-width: 0; }
.qr-title { font-size: 0.78rem; font-weight: 700; color: #111; line-height: 1.25; margin-bottom: 3px; word-break: break-word; }
.qr-author { font-size: 0.68rem; color: #4b5563; margin-bottom: 6px; }
.qr-pill-wrap { display: flex; flex-direction: column; gap: 3px; }
.qr-pill { font-size: 0.62rem; font-family: monospace; background: #fef2f2; color: #800000; padding: 2px 5px; border-radius: 4px; border: 1px solid #fecaca; display: inline-block; }
.qr-sticker-footer { margin-top: 6px; padding-top: 4px; border-top: 1px dashed #e5e7eb; font-size: 0.55rem; color: #9ca3af; text-align: center; font-style: italic; }
@media print {
    body { padding: 0; background: #fff; }
    .no-print { display: none !important; }
    .qr-sticker { box-shadow: none; }
}
</style>
</head>
<body>
<div class="no-print" style="margin-bottom:16px; padding:12px 18px; background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <div>
        <strong style="color:#800000; font-size:0.95rem;">Print Preview (${items.length} label${items.length === 1 ? '' : 's'})</strong>
        <p style="margin:2px 0 0; font-size:0.78rem; color:#64748b;">Ready to print sticker barcode labels.</p>
    </div>
    <button onclick="window.print()" style="background:#800000; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-weight:700; cursor:pointer; font-size:0.85rem;">Print Labels Now</button>
</div>
<div class="qr-grid">
${cardsHtml}
</div>
<script>
window.onload = function() {
    setTimeout(function() { window.print(); }, 600);
};
<\/script>
</body>
</html>`;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// ── Catalog: filter + sort ─────────────────────────────
window.initBooksPage = function() {
    var searchInput   = document.getElementById('bookSearch');
    var collFilter    = document.getElementById('collectionFilter');
    var statusFilter  = document.getElementById('statusFilter');
    var sortSelect    = document.getElementById('sortSelect');
    var tbody         = document.getElementById('booksTableBody');
    var countBadge    = document.getElementById('visibleCount');
    if (!tbody) return;

    var researchFilter = document.getElementById('researchTypeFilter');
    var programFilter  = document.getElementById('programFilter');

    function applyFilters() {
        var q    = (searchInput ? searchInput.value : '').toLowerCase().trim();
        var coll = (collFilter ? collFilter.value : '').toLowerCase();
        var stat = (statusFilter ? statusFilter.value : '').toLowerCase();
        var rt   = (researchFilter ? researchFilter.value : '').toLowerCase();
        var prog = (programFilter ? programFilter.value : '').toLowerCase();
        var rows = Array.from(tbody.querySelectorAll('tr'));
        var visible = 0;
        rows.forEach(function(row) {
            var match = (!q || (row.dataset.title && row.dataset.title.includes(q))
                              || (row.dataset.author && row.dataset.author.includes(q))
                              || (row.dataset.isbn && row.dataset.isbn.includes(q))
                              || (row.dataset.accession && row.dataset.accession.includes(q))
                              || (row.dataset.callno && row.dataset.callno.includes(q)))
                       && (!coll || (row.dataset.collection && row.dataset.collection.includes(coll)))
                       && (!stat || row.dataset.status === stat)
                       && (!rt   || row.dataset.researchType === rt)
                       && (!prog || row.dataset.course === prog);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (countBadge) countBadge.textContent = visible + ' books';
        var sort = (sortSelect ? sortSelect.value : 'title') || 'title';
        var visibleRows = rows.filter(function(r) { return r.style.display !== 'none'; });
        visibleRows.sort(function(a, b) {
            if (sort === 'title')     return (a.dataset.title||'').localeCompare(b.dataset.title||'');
            if (sort === 'author')    return (a.dataset.author||'').localeCompare(b.dataset.author||'');
            if (sort === 'year-desc') return parseInt(b.dataset.year||0) - parseInt(a.dataset.year||0);
            if (sort === 'year-asc')  return parseInt(a.dataset.year||0) - parseInt(b.dataset.year||0);
            return 0;
        });
        visibleRows.forEach(function(r) { tbody.appendChild(r); });
        updateSelectedBooksCount();
    }

    let searchTimer = null;
    if (searchInput) {
        searchInput.oninput = function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(applyFilters, 300);
        };
    }
    if (collFilter)     collFilter.onchange = applyFilters;
    if (statusFilter)   statusFilter.onchange = applyFilters;
    if (sortSelect)     sortSelect.onchange = applyFilters;
    if (researchFilter) researchFilter.onchange = applyFilters;
    if (programFilter)  programFilter.onchange = applyFilters;
};

// ── Copies Inventory: filter + sort ────────────────────
window.initCopiesPage = function() {
    var searchInput = document.getElementById('copiesSearch');
    var sortSelect  = document.getElementById('copiesSortSelect');
    var availFilter = document.getElementById('copiesAvailabilityFilter');
    var collFilter  = document.getElementById('copiesCollectionFilter');
    var tbody       = document.getElementById('copiesTableBody');
    var countBadge  = document.getElementById('copiesVisibleCount');
    if (!tbody) return;

    function applyCopiesFilters() {
        var q     = (searchInput ? searchInput.value : '').toLowerCase().trim();
        var sort  = (sortSelect ? sortSelect.value : 'most-copies') || 'most-copies';
        var avail = (availFilter ? availFilter.value : '').toLowerCase();
        var coll  = (collFilter ? collFilter.value : '').toLowerCase();
        var rows  = Array.from(tbody.querySelectorAll('tr'));
        var visible = 0;

        rows.forEach(function(row) {
            var totalCopies     = parseInt(row.dataset.totalCopies || 0);
            var availableCopies = parseInt(row.dataset.availableCopies || 0);
            var borrowedCopies  = parseInt(row.dataset.borrowedCopies || 0);

            var matchQ = (!q || (row.dataset.title && row.dataset.title.includes(q))
                             || (row.dataset.author && row.dataset.author.includes(q))
                             || (row.dataset.isbn && row.dataset.isbn.includes(q))
                             || (row.dataset.accession && row.dataset.accession.includes(q)));
            var matchColl = (!coll || (row.dataset.collection && row.dataset.collection.includes(coll)));

            var matchAvail = true;
            if (avail === 'available') {
                matchAvail = availableCopies > 0;
            } else if (avail === 'low-stock') {
                matchAvail = availableCopies <= 1;
            } else if (avail === 'out-of-stock') {
                matchAvail = availableCopies === 0;
            } else if (avail === 'multiple') {
                matchAvail = totalCopies > 1;
            } else if (avail === 'single') {
                matchAvail = totalCopies === 1;
            }

            var match = matchQ && matchColl && matchAvail;
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (countBadge) countBadge.textContent = visible + ' items shown';

        var visibleRows = rows.filter(function(r) { return r.style.display !== 'none'; });
        visibleRows.sort(function(a, b) {
            var aTot = parseInt(a.dataset.totalCopies || 0);
            var bTot = parseInt(b.dataset.totalCopies || 0);
            var aBor = parseInt(a.dataset.borrowedCopies || 0);
            var bBor = parseInt(b.dataset.borrowedCopies || 0);

            if (sort === 'most-copies')   return bTot - aTot;
            if (sort === 'least-copies')  return aTot - bTot;
            if (sort === 'most-borrowed') return bBor - aBor;
            if (sort === 'title')         return (a.dataset.title||'').localeCompare(b.dataset.title||'');
            if (sort === 'author')        return (a.dataset.author||'').localeCompare(b.dataset.author||'');
            return 0;
        });
        visibleRows.forEach(function(r) { tbody.appendChild(r); });
    }

    let copiesSearchTimer = null;
    if (searchInput) {
        searchInput.oninput = function() {
            clearTimeout(copiesSearchTimer);
            copiesSearchTimer = setTimeout(applyCopiesFilters, 300);
        };
    }
    if (sortSelect)  sortSelect.onchange = applyCopiesFilters;
    if (availFilter) availFilter.onchange = applyCopiesFilters;
    if (collFilter)  collFilter.onchange = applyCopiesFilters;
    applyCopiesFilters();
};

function resetCopiesFilters() {
    ['copiesSearch','copiesSortSelect','copiesAvailabilityFilter','copiesCollectionFilter'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = el.tagName === 'SELECT' ? (el.options[0]?.value || '') : '';
    });
    if (typeof window.initCopiesPage === 'function') {
        window.initCopiesPage();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        window.initBooksPage();
        window.initCopiesPage();
    });
} else {
    window.initBooksPage();
    window.initCopiesPage();
}
document.addEventListener('htmx:afterSwap', function() {
    if (typeof window.initBooksPage === 'function') window.initBooksPage();
    if (typeof window.initCopiesPage === 'function') window.initCopiesPage();
});

function resetBookFilters() {
    ['bookSearch','collectionFilter','statusFilter','sortSelect','researchTypeFilter','programFilter'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.value = el.tagName === 'SELECT' ? (el.options[0]?.value || '') : '';
    });
    document.querySelectorAll('#booksTableBody tr').forEach(r => r.style.display = '');
    const cb = document.getElementById('visibleCount');
    const rows = document.querySelectorAll('#booksTableBody tr');
    if (cb) cb.textContent = rows.length + ' books';
}

function openCondemnModal(bookId, bookTitle, actionUrl) {
    document.getElementById('condemnForm').action = actionUrl;
    document.getElementById('condemn-book-title').textContent = bookTitle;
    document.querySelectorAll('input[name="condemn_reason_radio"]').forEach(function(r) {
        r.checked = false;
    });
    document.querySelectorAll('.cm-reason-label').forEach(function(l) {
        l.classList.remove('cm-reason-selected');
    });
    const othersText = document.getElementById('cm_others_text');
    if (othersText) { othersText.value = ''; othersText.style.display = 'none'; }
    document.getElementById('condemn-reason-hidden').value = '';
    const fileInput = document.querySelector('#condemnForm input[type="file"]');
    if (fileInput) fileInput.value = '';

    const modal = document.getElementById('condemnModal');
    modal.showModal();
    modal.addEventListener('click', function onBd(e) {
        if (e.target === modal) { modal.close(); modal.removeEventListener('click', onBd); }
    });
}

function closeCondemnModal() {
    document.getElementById('condemnModal').close();
}

function onCondemnReasonChange() {
    var val = document.querySelector('input[name="condemn_reason_radio"]:checked')?.value;
    document.querySelectorAll('.cm-reason-label').forEach(function(l) {
        l.classList.remove('cm-reason-selected');
    });
    if (val) {
        var lbl = document.getElementById('cm_label_' + val);
        if (lbl) lbl.classList.add('cm-reason-selected');
    }
    var othersText = document.getElementById('cm_others_text');
    if (othersText) othersText.style.display = (val === 'others') ? 'block' : 'none';
}

function prepareCondemnModalReason() {
    var val = document.querySelector('input[name="condemn_reason_radio"]:checked')?.value;
    if (!val) { alert('Please select a condemnation reason.'); return false; }
    var reason = val;
    if (val === 'others') {
        var txt = document.getElementById('cm_others_text')?.value.trim();
        reason = txt ? 'Others: ' + txt : 'Others';
    } else {
        var labels = {'damaged':'Damaged beyond repair','lost':'Lost','obsolete':'Obsolete'};
        reason = labels[val] || val;
    }
    document.getElementById('condemn-reason-hidden').value = reason;
    return true;
}

</script>

{{-- Condemn Modal --}}
<style>
.cm-reason-label {
    display:flex;align-items:center;gap:14px;
    padding:14px 18px;border:2px solid #e5e7eb;border-radius:12px;
    cursor:pointer;margin-bottom:10px;transition:border-color 0.15s,background 0.15s;background:#fafafa;
}
.cm-reason-label:hover { border-color:#800000; background:rgba(128,0,0,0.04); }
.cm-reason-label.cm-reason-selected { border-color:#800000; background:rgba(128,0,0,0.06); }
#condemnModal::backdrop { background:rgba(0,0,0,0.6); }
#condemnModal { animation: condemnSlideIn 0.24s cubic-bezier(0.34,1.45,0.64,1); }
</style>
<dialog id="condemnModal" aria-labelledby="condemn-modal-heading"
        style="border:none;border-radius:18px;width:calc(100% - 40px);max-width:560px;padding:0;box-shadow:0 24px 64px rgba(0,0,0,0.35);overflow:hidden;margin:auto;">
    <div class="condemn-card"
         style="background:#fff;border-radius:18px;overflow:hidden;">
        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#800000,#5a0000);padding:22px 28px;display:flex;align-items:center;justify-content:space-between;border-bottom:3px solid #FFC72C;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div style="width:48px;height:48px;background:rgba(255,255,255,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FFC72C" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
                <div>
                    <h3 id="condemn-modal-heading" style="margin:0;color:#fff;font-size:1.1rem;font-weight:700;">Condemn Book</h3>
                    <p id="condemn-book-title" style="margin:3px 0 0;color:rgba(255,255,255,0.75);font-size:0.85rem;max-width:380px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></p>
                </div>
            </div>
            <button onclick="closeCondemnModal()" aria-label="Close" style="background:rgba(255,255,255,0.15);border:none;color:#fff;border-radius:8px;width:34px;height:34px;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">×</button>
        </div>
        {{-- Body --}}
        <form id="condemnForm" method="POST" action="" enctype="multipart/form-data" onsubmit="return prepareCondemnModalReason()">
            @csrf
            <input type="hidden" name="reason" id="condemn-reason-hidden">
            <div style="padding:26px 28px 18px;">
                <p style="font-size:0.8rem;font-weight:700;color:#6b7280;margin:0 0 16px;text-transform:uppercase;letter-spacing:0.6px;">Select condemnation reason</p>
                @foreach(['damaged'=>'Damaged beyond repair','lost'=>'Lost','obsolete'=>'Obsolete','others'=>'Others'] as $val=>$label)
                <label id="cm_label_{{ $val }}" class="cm-reason-label" for="cm_{{ $val }}">
                    <input type="radio" name="condemn_reason_radio" id="cm_{{ $val }}" value="{{ $val }}"
                           style="width:20px;height:20px;accent-color:#800000;cursor:pointer;flex-shrink:0;"
                           onchange="onCondemnReasonChange()">
                    <span style="font-size:0.95rem;font-weight:600;color:#1f2937;">{{ $label }}</span>
                </label>
                @endforeach
                <input type="text" id="cm_others_text" placeholder="Describe the reason…" aria-label="Other reason description"
                       style="display:none;width:100%;margin-top:4px;margin-bottom:4px;padding:11px 14px;border:1.5px solid #d1d5db;border-radius:10px;font-size:0.9rem;font-family:inherit;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#800000'" onblur="this.style.borderColor='#d1d5db'">
                <div style="margin-top:18px;padding-top:18px;border-top:1px solid #f0f0f0;">
                    <label for="condemn-proof-image" style="display:block;font-size:0.8rem;font-weight:600;color:#6b7280;margin-bottom:7px;">Proof Image <span style="font-weight:400;">(optional)</span></label>
                    <input type="file" id="condemn-proof-image" name="proof_image" accept="image/*" style="font-size:0.85rem;width:100%;">
                </div>
            </div>
            <div style="padding:14px 28px 24px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid #f0f0f0;">
                <button type="button" onclick="closeCondemnModal()" style="background:#f3f4f6;color:#374151;border:none;padding:11px 24px;border-radius:10px;font-weight:600;font-size:0.9rem;cursor:pointer;font-family:inherit;">Cancel</button>
                <button type="submit" style="background:linear-gradient(135deg,#800000,#5a0000);color:#fff;border:none;padding:11px 28px;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px;box-shadow:0 4px 14px rgba(128,0,0,0.3);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Confirm Condemn
                </button>
            </div>
        </form>
    </div>
</dialog>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- IMPORT BOOKS MODAL (CSV & EXCEL .XLSX) --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<dialog id="importBooksModal" style="border:none;border-radius:18px;width:calc(100% - 40px);max-width:720px;padding:0;box-shadow:0 24px 64px rgba(0,0,0,0.35);overflow:hidden;margin:auto;">
    <div style="background:#fff;border-radius:18px;overflow:hidden;">
        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#800000,#5a0000);padding:22px 28px;display:flex;align-items:center;justify-content:space-between;border-bottom:3px solid #FFC72C;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div style="width:48px;height:48px;background:rgba(255,255,255,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FFC72C" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                </div>
                <div>
                    <h3 style="margin:0;color:#fff;font-size:1.15rem;font-weight:700;">Import Books Catalog</h3>
                    <p style="margin:3px 0 0;color:rgba(255,255,255,0.8);font-size:0.85rem;">Upload CSV or Excel (.xlsx) file to batch add library books.</p>
                </div>
            </div>
            <button onclick="closeImportModal()" aria-label="Close" style="background:rgba(255,255,255,0.15);border:none;color:#fff;border-radius:8px;width:34px;height:34px;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">×</button>
        </div>

        {{-- Step 1: File Upload Section --}}
        <div style="padding:24px 28px;">
            <div id="importUploadSection">
                <div style="background:#f8fafc; border:2px dashed #cbd5e1; border-radius:14px; padding:28px 20px; text-align:center; cursor:pointer;" onclick="document.getElementById('importFileInput').click()">
                    <input type="file" id="importFileInput" accept=".csv,.xlsx,.txt" style="display:none;" onchange="handleImportFileSelected(event)">
                    <div style="width:50px; height:50px; background:rgba(128,0,0,0.1); color:#800000; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                    </div>
                    <h4 style="margin:0 0 6px; font-size:1rem; color:#1e293b; font-weight:700;">Click or drag file to upload</h4>
                    <p style="margin:0 0 12px; font-size:0.85rem; color:#64748b;">Supports <strong>CSV (.csv)</strong> and <strong>Excel (.xlsx)</strong></p>
                    <span id="selectedFileName" style="display:inline-block; font-size:0.8rem; font-weight:600; color:#800000; background:#fef2f2; padding:4px 12px; border-radius:20px; display:none;"></span>
                </div>

                {{-- Template Download Links --}}
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; padding:12px 16px; background:#f1f5f9; border-radius:10px; font-size:0.82rem;">
                    <span style="color:#475569; font-weight:600;">Need a starter template?</span>
                    <div style="display:flex; gap:8px;">
                        <a href="data:text/csv;charset=utf-8,title%2Cauthor%2Cisbn%2Caccession_number%2Ccollection%2Cshelf_location%2Cpublisher%2Cpublication_year%2Ccopies%2Csubject%0AIntroduction%20to%20Algorithms%2CThomas%20Cormen%2C9780262033848%2CACC-8001%2CLibrary%20of%20Congress%2CLevel%202%20Shelf%20B%2CMIT%20Press%2C2022%2C1%2CComputer%20Science%0AClean%20Code%2CRobert%20Martin%2C9780132350884%2CACC-8002%2CSpecial%20Collections%2CLevel%201%20Shelf%20A%2CPrentice%20Hall%2C2008%2C1%2CSoftware%20Engineering" download="books_import_template.csv" style="color:#800000; text-decoration:none; font-weight:700;">Download CSV Template</a>
                    </div>
                </div>

                <div id="importErrorAlert" style="display:none; margin-top:16px; padding:14px 18px; border-radius:10px; background:#fef2f2; border-left:4px solid #ef4444; color:#991b1b; font-size:0.85rem; max-height:220px; overflow-y:auto;">
                    <strong style="display:block; margin-bottom:6px; font-size:0.9rem;">Validation Errors Found (File Rejected):</strong>
                    <ul id="importErrorList" style="margin:0; padding-left:20px;"></ul>
                </div>
            </div>

            {{-- Step 2: Preview & Validation Table --}}
            <div id="importPreviewSection" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <div>
                        <span style="background:#dcfce7; color:#166534; font-weight:700; padding:4px 12px; border-radius:20px; font-size:0.8rem;">✓ File Validated Successfully</span>
                    </div>
                    <div style="font-size:0.85rem; color:#475569;">
                        <strong><span id="previewUniqueTitles">0</span></strong> titles (<strong id="previewTotalCopies">0</strong> total copies)
                    </div>
                </div>

                <div style="max-height:260px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:10px; margin-bottom:16px;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.8rem;">
                        <thead style="background:#f8fafc; position:sticky; top:0;">
                            <tr>
                                <th style="padding:8px 10px; text-align:left; border-bottom:1px solid #e2e8f0; color:#475569;">Title</th>
                                <th style="padding:8px 10px; text-align:left; border-bottom:1px solid #e2e8f0; color:#475569;">Author</th>
                                <th style="padding:8px 10px; text-align:left; border-bottom:1px solid #e2e8f0; color:#475569;">Accession / Barcode</th>
                                <th style="padding:8px 10px; text-align:left; border-bottom:1px solid #e2e8f0; color:#475569;">Collection</th>
                                <th style="padding:8px 10px; text-align:right; border-bottom:1px solid #e2e8f0; color:#475569;">Copies</th>
                            </tr>
                        </thead>
                        <tbody id="importPreviewTbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div style="padding:14px 28px 24px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid #f0f0f0;">
            <button type="button" onclick="closeImportModal()" style="background:#f3f4f6;color:#374151;border:none;padding:11px 24px;border-radius:10px;font-weight:600;font-size:0.9rem;cursor:pointer;">Cancel</button>
            <button type="button" id="btnPreviewImport" onclick="uploadAndPreview()" style="background:#800000;color:#FFC72C;border:none;padding:11px 28px;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;display:inline-flex;align-items:center;gap:7px;">
                <span>Verify &amp; Preview</span>
            </button>
            <button type="button" id="btnProcessImport" onclick="confirmImportExecution()" style="display:none;background:linear-gradient(135deg,#800000,#5a0000);color:#fff;border:none;padding:11px 28px;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;align-items:center;gap:7px;">
                <span>Confirm &amp; Import Books</span>
            </button>
        </div>
    </div>
</dialog>

<script>
var parsedGroupedBooks = window.parsedGroupedBooks || [];

function openImportModal() {
    const m = document.getElementById('importBooksModal');
    if (m) {
        resetImportState();
        m.showModal();
    }
}

function closeImportModal() {
    const m = document.getElementById('importBooksModal');
    if (m) m.close();
}

function resetImportState() {
    parsedGroupedBooks = [];
    document.getElementById('importFileInput').value = '';
    document.getElementById('selectedFileName').style.display = 'none';
    document.getElementById('importErrorAlert').style.display = 'none';
    document.getElementById('importPreviewSection').style.display = 'none';
    document.getElementById('importUploadSection').style.display = 'block';
    document.getElementById('btnPreviewImport').style.display = 'inline-flex';
    document.getElementById('btnProcessImport').style.display = 'none';
}

function handleImportFileSelected(e) {
    const file = e.target.files[0];
    if (file) {
        const span = document.getElementById('selectedFileName');
        span.textContent = `Selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
        span.style.display = 'inline-block';
        document.getElementById('importErrorAlert').style.display = 'none';
    }
}

async function uploadAndPreview() {
    const fileInput = document.getElementById('importFileInput');
    if (!fileInput.files || fileInput.files.length === 0) {
        alert('Please choose a CSV or Excel file to import.');
        return;
    }

    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', '{{ csrf_token() }}');

    const btn = document.getElementById('btnPreviewImport');
    btn.disabled = true;
    btn.textContent = 'Analyzing file...';

    try {
        const res = await fetch('{{ route("admin.books.import.preview") }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        });

        const data = await res.json();
        btn.disabled = false;
        btn.innerHTML = '<span>Verify &amp; Preview</span>';

        if (!res.ok || !data.valid) {
            // Show errors and reject file
            const alertBox = document.getElementById('importErrorAlert');
            const list = document.getElementById('importErrorList');
            list.innerHTML = (data.errors || [{ message: 'Validation failed.' }]).map(err => `
                <li style="margin-bottom:4px;">
                    ${err.row ? `<strong>Row ${err.row}:</strong> ` : ''}
                    ${err.field ? `<em>[${err.field}]</em> ` : ''}
                    ${err.message}
                </li>
            `).join('');
            alertBox.style.display = 'block';
            return;
        }

        // Success preview
        parsedGroupedBooks = data.grouped_books || [];
        document.getElementById('previewUniqueTitles').textContent = data.unique_titles_count || parsedGroupedBooks.length;
        document.getElementById('previewTotalCopies').textContent = data.total_copies_count || parsedGroupedBooks.length;

        const tbody = document.getElementById('importPreviewTbody');
        tbody.innerHTML = (data.preview_sample || []).map(b => `
            <tr>
                <td style="padding:8px 10px; font-weight:600; color:#111;">${b.title}</td>
                <td style="padding:8px 10px; color:#64748b;">${b.author || '—'}</td>
                <td style="padding:8px 10px; font-family:monospace; color:#800000; font-weight:600;">${b.accession_number || b.barcode || 'Auto'}</td>
                <td style="padding:8px 10px; color:#475569;">${b.collection || 'General'}</td>
                <td style="padding:8px 10px; text-align:right; font-weight:700; color:#166534;">${b.copies}</td>
            </tr>
        `).join('');

        document.getElementById('importPreviewSection').style.display = 'block';
        btn.style.display = 'none';
        document.getElementById('btnProcessImport').style.display = 'inline-flex';
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = '<span>Verify &amp; Preview</span>';
        alert('Failed to process file: ' + e.message);
    }
}

async function confirmImportExecution() {
    if (!parsedGroupedBooks || parsedGroupedBooks.length === 0) {
        alert('No validated books found to import.');
        return;
    }

    const btn = document.getElementById('btnProcessImport');
    btn.disabled = true;
    btn.textContent = 'Importing books...';

    try {
        const res = await fetch('{{ route("admin.books.import.process") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ books: parsedGroupedBooks })
        });

        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Import failed.');
        }

        alert(data.message || 'Books imported successfully!');
        window.location.reload();
    } catch (e) {
        btn.disabled = false;
        btn.textContent = 'Confirm & Import Books';
        alert('Import error: ' + e.message);
    }
}
</script>

@endsection
