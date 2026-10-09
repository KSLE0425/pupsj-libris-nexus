@extends('layouts.faculty')

@section('title', 'Book Catalog')

@push('styles')
<style>
/* Filter */
.filter-dropdowns .filter-select {
    padding: 8px 12px;
    border: 2px solid #e8e8e8;
    border-radius: 8px;
    font-size: 0.8rem;
    font-family: 'Poppins', sans-serif;
    background: white;
    min-width: 160px;
}

/* ── FILTER BUTTONS (Student-style) ── */
.filter-buttons {
    padding: 14px 18px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.filter-btn {
    display: inline-block;
    padding: 6px 14px;
    background: #f5f5f5;
    border: 1px solid #e8e8e8;
    border-radius: 30px;
    text-decoration: none;
    color: #555;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.2s;
}

.filter-btn:hover {
    background: #e8e8e8;
    transform: translateY(-1px);
}

.filter-btn.active {
    background: var(--maroon);
    border-color: var(--maroon);
    color: white;
}

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

/* ── SEARCH BAR CARD ── */
.search-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    margin-bottom: 20px;
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.search-card:hover {
    box-shadow: 0 8px 24px rgba(128, 0, 0, 0.1);
}
.search-header {
    padding: 14px 20px;
    background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
    display: flex;
    align-items: center;
    gap: 10px;
}
.search-header svg {
    flex-shrink: 0;
}
.search-header h5 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 600;
    color: #fff;
}
.search-body {
    padding: 20px;
}
.search-form {
    display: flex;
    gap: 12px;
}
.search-input {
    flex: 1;
    padding: 14px 18px;
    border: 2px solid #e8e8e8;
    border-radius: 12px;
    font-size: 0.92rem;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
    outline: none;
}
.search-input:focus {
    border-color: var(--maroon);
    box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
}
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--yellow);
    color: var(--maroon);
    font-family: 'Poppins', sans-serif;
    font-size: 0.92rem;
    font-weight: 700;
    padding: 0 30px;
    border-radius: 40px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-primary:hover {
    background: #e8d800;
    transform: translateY(-2px);
}

/* ── COLLAPSIBLE FILTER SIDEBAR ── */
.catalog-filter-wrap { margin-bottom: 20px; }
.filter-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--maroon);
    color: #fff;
    border: none;
    padding: 11px 22px;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 12px;
    transition: background 0.18s;
}
.filter-toggle-btn:hover { background: var(--maroon-dark); }
.filter-toggle-btn.active { background: var(--yellow); color: var(--maroon); }
.filter-sidebar-panel {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    padding: 18px 20px;
    display: none;
    margin-bottom: 16px;
}
.filter-sidebar-panel.open { display: block; }
.sidebar-panel-inner { display: flex; flex-direction: column; gap: 16px; }
.fs-heading {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--maroon);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.fs-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.fs-chip {
    display: inline-block;
    padding: 5px 12px;
    background: #f5f5f5;
    border: 1px solid #e8e8e8;
    border-radius: 20px;
    text-decoration: none;
    color: #555;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.18s;
}
.fs-chip:hover { background: #e8e8e8; color: #333; }
.fs-chip.active { background: var(--maroon); border-color: var(--maroon); color: #fff; }
.fs-select {
    width: 100%;
    padding: 8px 12px;
    border: 1.5px solid #e8e8e8;
    border-radius: 8px;
    font-size: 0.82rem;
    font-family: 'Poppins', sans-serif;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.15s;
}
.fs-select:focus { outline: none; border-color: var(--maroon); }
.fs-clear-btn {
    display: inline-block;
    text-align: center;
    background: #f5f5f5;
    color: #555;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.78rem;
    font-weight: 600;
    border: 1px solid #e8e8e8;
    transition: all 0.18s;
}
.fs-clear-btn:hover { background: var(--maroon); color: #fff; border-color: var(--maroon); }

/* ── BOOKS COUNT ── */
.books-count {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--maroon);
    margin-bottom: 16px;
    padding-left: 4px;
}

/* ── 3-COLUMN BOOK CARD REDESIGN ── */
.book-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
    padding: 20px;
    border: 1px solid #edf2f7;
    transition: all 0.2s;
}
.book-card:hover {
    border-color: rgba(128, 0, 0, 0.2);
    box-shadow: 0 8px 24px rgba(128, 0, 0, 0.08);
    transform: translateY(-2px);
}
.bcr-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding-bottom: 14px;
    margin-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
    flex-wrap: wrap;
}
.bcr-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 200px;
}
.bcr-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--maroon);
    margin: 0;
    line-height: 1.35;
}
.bcr-grid {
    display: grid;
    grid-template-columns: 140px 1.2fr 1.2fr;
    gap: 20px;
    align-items: flex-start;
}
@media (max-width: 768px) {
    .bcr-grid {
        grid-template-columns: 1fr;
    }
}
.bcr-col {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.bcr-col-cover {
    align-items: center;
    text-align: center;
}
.bcr-cover-wrap {
    width: 110px;
    height: 150px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.12);
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
}
.bcr-cover-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.bcr-cover-ph {
    width: 110px;
    height: 150px;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--maroon) 0%, #4a0000 100%);
    color: rgba(255,255,255,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.bcr-acc-badge {
    margin-top: 8px;
    font-size: 0.72rem;
    color: #475569;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    display: inline-block;
    max-width: 120px;
    word-break: break-all;
}
.bcr-acc-badge .acc-lbl {
    font-weight: 700;
    color: var(--maroon);
    margin-right: 2px;
}
.bcr-meta-row {
    display: flex;
    align-items: baseline;
    font-size: 0.8rem;
    line-height: 1.4;
    gap: 8px;
}
.bcr-lbl {
    color: #64748b;
    font-weight: 600;
    min-width: 78px;
    flex-shrink: 0;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.bcr-val {
    color: #1e293b;
    word-break: break-word;
}
.bcr-status-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.72rem;
    font-weight: 700;
}
.status-avail { background: #dcfce7; color: #166534; }
.status-borrowed { background: #fee2e2; color: #991b1b; }
.status-reserved { background: #fef9c3; color: #854d0e; }

/* TOC Accordion */
.bcr-toc-accordion {
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px dashed #e2e8f0;
}
.bcr-toc-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: var(--maroon);
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}
.bcr-toc-toggle:hover {
    background: #f1f5f9;
    border-color: var(--maroon);
}
.bcr-toc-chevron {
    transition: transform 0.2s;
}
.bcr-toc-toggle.active .bcr-toc-chevron {
    transform: rotate(180deg);
}
.bcr-toc-content {
    margin-top: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
.bcr-toc-gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}
.bcr-toc-img-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.bcr-toc-img-card img {
    width: 90px;
    height: 120px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    cursor: pointer;
    transition: transform 0.15s;
}
.bcr-toc-img-card img:hover {
    transform: scale(1.05);
    border-color: var(--maroon);
}
.bcr-toc-img-card small {
    font-size: 0.7rem;
    color: #64748b;
    font-weight: 600;
}

/* Image Preview Modal */
.image-preview-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.8);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.image-preview-modal.show {
    display: flex;
}
.image-preview-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    background: #fff;
    padding: 10px;
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}
.image-preview-content img {
    max-width: 85vw;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 6px;
    display: block;
}
.image-preview-close {
    position: absolute;
    top: -14px;
    right: -14px;
    width: 32px;
    height: 32px;
    background: var(--maroon);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: bold;
    font-size: 16px;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.new-badge {
    display: inline-block;
    background: linear-gradient(135deg, var(--yellow), var(--yellow-dark));
    color: var(--maroon);
    font-size: 0.6rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 20px;
    margin-left: 8px;
    vertical-align: middle;
    text-transform: uppercase;
}

.book-actions {
    flex-shrink: 0;
}
.btn-view {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    color: var(--maroon);
    border: 2px solid var(--maroon);
    padding: 10px 22px;
    border-radius: 40px;
    font-weight: 600;
    font-size: 0.82rem;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-view:hover {
    background: var(--maroon);
    color: white;
    transform: translateX(2px);
}

.btn-reserve {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--maroon);
    color: white;
    border: none;
    padding: 10px 22px;
    border-radius: 40px;
    font-weight: 700;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-left: 6px;
    font-family: inherit;
    text-decoration: none;
}
.btn-reserve:hover {
    background: var(--maroon-dark);
    transform: translateY(-1px);
    color: white;
}

/* ── PAGINATION ── */
.pagination-container {
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 20px;
    border-radius: 0 0 16px 16px;
}

.pagination-info {
    font-size: 0.875rem;
    color: #6b7280;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #1f2937;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    min-height: 40px;
}

.pagination-btn:hover:not(:disabled) {
    background: #FFC72C;
    border-color: #e6b328;
    color: #800000;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-numbers {
    display: flex;
    gap: 0.375rem;
    flex-wrap: wrap;
    justify-content: center;
}

.page-number {
    min-width: 2.25rem;
    height: 2.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 0.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #1f2937;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.page-number:hover {
    background: #FFC72C;
    border-color: #e6b328;
    color: #800000;
}

.page-number.active {
    background: #800000;
    border-color: #800000;
    color: white;
}

/* ── EMPTY STATE ── */
.empty-state {
    text-align: center;
    padding: 48px 24px;
    background: #fff;
    border-radius: 16px;
}
.empty-icon {
    width: 70px;
    height: 70px;
    background: rgba(128, 0, 0, 0.06);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}
.empty-state h5 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--maroon);
    margin-bottom: 6px;
}
.empty-state p {
    font-size: 0.8rem;
    color: #999;
    margin-bottom: 0;
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .search-form {
        flex-direction: column;
    }
    .btn-primary {
        justify-content: center;
        padding: 10px;
    }
    .book-card {
        flex-direction: column;
        align-items: flex-start;
    }
    .book-actions {
        width: 100%;
    }
    .btn-view, .btn-reserve {
        width: 100%;
        justify-content: center;
        margin-left: 0;
        margin-top: 8px;
    }
    .filter-buttons {
        justify-content: center;
    }
    .filter-dropdowns {
        flex-direction: column;
    }
    .filter-select {
        width: 100%;
    }
    .pagination-container {
        flex-direction: column;
        align-items: center;
        padding: 1rem;
    }
    .pagination-controls {
        justify-content: center;
    }
}

/* ── MOBILE BOOK CARD: title → cover/details → actions ── */
@media (max-width: 768px) {
    .book-card {
        display: flex;
        flex-direction: column;
        align-items: stretch !important;
        padding: 16px;
        border-radius: 16px;
        border-top: 3px solid var(--maroon);
    }
    .book-card:hover { transform: none; }
    .bcr-top { display: contents; }
    .bcr-title-row { order: 1; min-width: 0; padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; }
    .bcr-title { font-size: 1rem; overflow-wrap: anywhere; }
    .bcr-grid { order: 2; gap: 12px; }
    .bcr-col-cover { flex-direction: row; justify-content: center; align-items: center; gap: 12px; }
    .bcr-cover-wrap, .bcr-cover-ph { width: 96px; height: 130px; }
    .bcr-meta-row { flex-wrap: wrap; }
    .bcr-lbl { min-width: 72px; }
    .bcr-val { min-width: 0; overflow-wrap: anywhere; }
    .book-actions {
        order: 3;
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        width: 100%;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
    }
    .book-actions > :only-child { grid-column: 1 / -1; }
    .book-actions form { display: block !important; margin: 0; }
    .book-actions .btn-view, .book-actions .btn-reserve, .book-actions .btn-borrow {
        width: 100%;
        justify-content: center;
        margin: 0 !important;
        min-height: 42px;
        border-radius: 12px;
    }
    .book-card > :not(.bcr-top):not(.bcr-grid) { order: 4; }
    .fs-chips { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none; }
    .fs-chips::-webkit-scrollbar { display: none; }
    .fs-chips > * { flex-shrink: 0; }
}
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Browse Book Catalog</h1>
    <p>Search and borrow books from our library collection</p>
</div>

{{-- Search Card --}}
<div class="search-card">
    <div class="search-header">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
        </svg>
        <h5>Find Your Next Read</h5>
    </div>
    <div class="search-body">
        <form method="GET" action="{{ route('faculty.catalog') }}" class="search-form" id="facultySearchForm">
            <input type="text" name="search" id="facultySearchInput" class="search-input" 
                   placeholder="Search by title, author, ISBN, or subject..." 
                   value="{{ request('search') }}">
            <button type="submit" class="btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                Search
            </button>
        </form>
    </div>
</div>

{{-- ═══ COLLAPSIBLE FILTER SIDEBAR ═══ --}}
<div class="catalog-filter-wrap">

    <button type="button" class="filter-toggle-btn" id="filterToggleBtn" onclick="toggleFilterSidebar()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
        </svg>
        Filters
    </button>

    <aside class="filter-sidebar-panel" id="filterSidebarPanel">
        <div class="sidebar-panel-inner">

            {{-- Collection --}}
            <div class="fs-section">
                <h4 class="fs-heading">Collection</h4>
                <div class="fs-chips">
                    <a href="{{ route('faculty.catalog', array_filter(['search' => request('search'), 'new_acquisition' => request('new_acquisition')])) }}"
                       class="fs-chip {{ !request('collection') ? 'active' : '' }}">All</a>
                    @foreach($collectionTypes ?? [] as $ct)
                        @if($ct->name === 'Library of Congress') @continue @endif
                        <a href="{{ route('faculty.catalog', array_filter(['collection' => $ct->name, 'search' => request('search'), 'new_acquisition' => request('new_acquisition')])) }}"
                           class="fs-chip {{ request('collection') == $ct->name ? 'active' : '' }}">
                            {{ $ct->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- All filters in one form --}}
            <form method="GET" action="{{ route('faculty.catalog') }}" id="filterForm">
                @if(request('collection'))<input type="hidden" name="collection" value="{{ request('collection') }}">@endif
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif

                {{-- New Acquisitions toggle --}}
                <div class="fs-section">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.82rem;color:#444;font-weight:500;">
                        <input type="checkbox" name="new_acquisition" value="1"
                               {{ request('new_acquisition') ? 'checked' : '' }}
                               onchange="document.getElementById('filterForm').submit()"
                               style="accent-color:var(--maroon);width:15px;height:15px;">
                        New Acquisitions only
                    </label>
                </div>

                {{-- LoC Classification dropdown (Circulation / Filipiniana) --}}
                @if(in_array(request('collection'), ['Circulation', 'Library of Congress', 'Filipiniana']))
                <div class="fs-section">
                    <h4 class="fs-heading">Classification (LoC)</h4>
                    <select name="loc_class" class="fs-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Classifications</option>
                        @foreach($locClassifications ?? [] as $code => $name)
                            <option value="{{ $code }}" {{ request('loc_class') == $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Research & Innovation sub-filters --}}
                @if(str_contains(strtolower(request('collection', '')), 'research'))
                <div class="fs-section">
                    <h4 class="fs-heading">Research Type</h4>
                    <div class="fs-chips">
                        <a href="{{ route('faculty.catalog', array_filter(['collection' => request('collection'), 'search' => request('search'), 'new_acquisition' => request('new_acquisition'), 'department_id' => request('department_id')])) }}"
                           class="fs-chip {{ !request('research_type') ? 'active' : '' }}">All</a>
                        <a href="{{ route('faculty.catalog', array_filter(['collection' => request('collection'), 'research_type' => 'thesis', 'search' => request('search'), 'new_acquisition' => request('new_acquisition'), 'department_id' => request('department_id')])) }}"
                           class="fs-chip {{ request('research_type') == 'thesis' ? 'active' : '' }}">Thesis</a>
                        <a href="{{ route('faculty.catalog', array_filter(['collection' => request('collection'), 'research_type' => 'capstone', 'search' => request('search'), 'new_acquisition' => request('new_acquisition'), 'department_id' => request('department_id')])) }}"
                           class="fs-chip {{ request('research_type') == 'capstone' ? 'active' : '' }}">Capstone</a>
                    </div>
                </div>
                @else
                    @if(request('research_type'))<input type="hidden" name="research_type" value="{{ request('research_type') }}">@endif
                @endif
                @if(request('research_type') && str_contains(strtolower(request('collection', '')), 'research'))
                    <input type="hidden" name="research_type" value="{{ request('research_type') }}">
                @endif

                <div class="fs-section">
                    <h4 class="fs-heading">Department</h4>
                    <select name="department_id" class="fs-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Departments</option>
                        @foreach($specialties ?? [] as $spec)
                            <option value="{{ $spec->id }}" {{ request('department_id') == $spec->id ? 'selected' : '' }}>
                                {{ $spec->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="fs-section">
                    <h4 class="fs-heading">Author</h4>
                    <select name="author" class="fs-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Authors</option>
                        @foreach($authors ?? [] as $author)
                            <option value="{{ $author }}" {{ request('author') == $author ? 'selected' : '' }}>{{ $author }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="fs-section">
                    <h4 class="fs-heading">Subject</h4>
                    <select name="subject" class="fs-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Subjects</option>
                        @foreach($subjects ?? [] as $subject)
                            <option value="{{ $subject }}" {{ request('subject') == $subject ? 'selected' : '' }}>{{ $subject }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <a href="{{ route('faculty.catalog') }}" class="fs-clear-btn">Clear All Filters</a>
        </div>
    </aside>

</div>
{{-- ═══════════════════════════════════════ --}}

{{-- Books Listing --}}
@if($books && $books->count() > 0)
<div class="books-count">
    All Books ({{ $books->total() }})
</div>

@foreach($books as $book)
<div class="book-card">
    <div class="bcr-top">
        <div class="bcr-title-row">
            <h3 class="bcr-title">{{ $book->title }}</h3>
            @if($book->is_new_acquisition)
                <span class="new-badge">NEW</span>
            @endif
        </div>
        <div class="book-actions">
            <a href="{{ route('faculty.book.show', $book->id) }}" class="btn-view">
                View Details
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>
            @if($book->status === 'available' || $book->status === 'borrowed')
                <form method="POST" action="{{ route('faculty.reservations.store') }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                    <button type="submit" class="btn-reserve">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                        Reserve
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="bcr-grid">
        {{-- Column 1: Cover Image & Accession QR --}}
        <div class="bcr-col bcr-col-cover">
            @if($book->title_cover_image_path)
                <div class="bcr-cover-wrap">
                    <img src="{{ asset('storage/' . $book->title_cover_image_path) }}" alt="{{ $book->title }}" class="bcr-cover-img" onerror="this.parentElement.innerHTML='<div class=\\\'bcr-cover-ph\\\'><svg width=\\\'32\\\' height=\\\'32\\\' fill=\\\'none\\\' viewBox=\\\'0 0 24 24\\\' stroke=\\\'currentColor\\\' stroke-width=\\\'1.5\\\'><path d=\\\'M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z\\\'/><path d=\\\'M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z\\\'/></svg></div>'">
                </div>
            @else
                <div class="bcr-cover-ph">
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </div>
            @endif
            @if($book->accession_number)
                <div class="bcr-acc-badge">
                    <span class="acc-lbl">ACC:</span>
                    <strong>{{ $book->accession_number }}</strong>
                </div>
            @endif
        </div>

        {{-- Column 2: Author, ISBN, Barcode, Subject, Keywords --}}
        <div class="bcr-col bcr-col-details">
            <div class="bcr-meta-row">
                <span class="bcr-lbl">Author</span>
                <span class="bcr-val" style="font-weight:600;">{{ $book->author ?? 'Unknown Author' }}</span>
            </div>
            <div class="bcr-meta-row">
                <span class="bcr-lbl">ISBN</span>
                <span class="bcr-val">{{ $book->isbn ?? 'N/A' }}</span>
            </div>
            <div class="bcr-meta-row">
                <span class="bcr-lbl">Barcode</span>
                <span class="bcr-val">{{ $book->barcode ?? 'N/A' }}</span>
            </div>
            <div class="bcr-meta-row">
                <span class="bcr-lbl">Subject</span>
                <span class="bcr-val">{{ $book->subject ?? 'General' }}</span>
            </div>
            @if($book->keywords)
            <div class="bcr-meta-row">
                <span class="bcr-lbl">Keywords</span>
                <span class="bcr-val" style="color:#64748b;">{{ $book->keywords }}</span>
            </div>
            @endif
        </div>

        {{-- Column 3: Publisher, Year, Shelf Location, Collection, Status --}}
        <div class="bcr-col bcr-col-pub">
            <div class="bcr-meta-row">
                <span class="bcr-lbl">Publisher</span>
                <span class="bcr-val">{{ $book->publisher ?? 'N/A' }}</span>
            </div>
            <div class="bcr-meta-row">
                <span class="bcr-lbl">Pub Year</span>
                <span class="bcr-val">{{ $book->publication_year ?? 'N/A' }}</span>
            </div>
            <div class="bcr-meta-row">
                <span class="bcr-lbl">Shelf Loc</span>
                <span class="bcr-val">{{ $book->shelf_location ?? 'N/A' }}</span>
            </div>
            <div class="bcr-meta-row">
                <span class="bcr-lbl">Collection</span>
                <span class="bcr-val">{{ $book->collection ?? 'N/A' }}</span>
            </div>
            <div class="bcr-meta-row">
                <span class="bcr-lbl">Status</span>
                <span class="bcr-status-badge {{ $book->status === 'available' ? 'status-avail' : 'status-borrowed' }}">
                    {{ ucfirst($book->status ?? 'Available') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Below: Collapsible Table of Contents viewer --}}
    @php
        $hasToc = !empty($book->toc_image_path) || ($book->tocImages && $book->tocImages->count() > 0);
    @endphp
    @if($hasToc)
        <div class="bcr-toc-accordion">
            <button type="button" class="bcr-toc-toggle" onclick="toggleBookToc('toc-{{ $book->id }}', this)">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5v10"/></svg>
                <span>View Table of Contents</span>
                <svg class="bcr-toc-chevron" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="bcr-toc-content" id="toc-{{ $book->id }}" style="display:none;">
                <div class="bcr-toc-gallery">
                    @if($book->toc_image_path)
                        <div class="bcr-toc-img-card">
                            <img src="{{ asset('storage/' . $book->toc_image_path) }}" alt="TOC Page" onclick="openImageModal(this.src)">
                            <small>Main TOC</small>
                        </div>
                    @endif
                    @if($book->tocImages)
                        @foreach($book->tocImages as $idx => $tocImg)
                            <div class="bcr-toc-img-card">
                                <img src="{{ asset('storage/' . $tocImg->path) }}" alt="TOC Page {{ $idx + 1 }}" onclick="openImageModal(this.src)">
                                <small>Page {{ $idx + 1 }}</small>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endforeach

{{-- Pagination Controls --}}
<div class="pagination-container">
    <div class="pagination-info">
        Showing {{ $books->firstItem() }} to {{ $books->lastItem() }} of {{ $books->total() }} books
    </div>
    <div class="pagination-controls">
        {{-- Previous Page Link --}}
        @if ($books->onFirstPage())
            <button class="pagination-btn" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                <span>Previous</span>
            </button>
        @else
            <a href="{{ $books->previousPageUrl() }}" class="pagination-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                <span>Previous</span>
            </a>
        @endif

        {{-- Page Numbers --}}
        <div class="page-numbers">
            @php
                $currentPage = $books->currentPage();
                $lastPage = $books->lastPage();
                $start = max(1, $currentPage - 2);
                $end = min($lastPage, $currentPage + 2);
                
                if ($start > 1) {
                    echo '<a href="' . $books->url(1) . '" class="page-number">1</a>';
                    if ($start > 2) echo '<span class="ellipsis" style="padding: 0 4px;">...</span>';
                }
                
                for ($i = $start; $i <= $end; $i++) {
                    $activeClass = ($i == $currentPage) ? 'active' : '';
                    echo '<a href="' . $books->url($i) . '" class="page-number ' . $activeClass . '">' . $i . '</a>';
                }
                
                if ($end < $lastPage) {
                    if ($end < $lastPage - 1) echo '<span class="ellipsis" style="padding: 0 4px;">...</span>';
                    echo '<a href="' . $books->url($lastPage) . '" class="page-number">' . $lastPage . '</a>';
                }
            @endphp
        </div>

        {{-- Next Page Link --}}
        @if ($books instanceof \Illuminate\Pagination\LengthAwarePaginator && $books->hasMorePages())
            <a href="{{ $books->nextPageUrl() }}" class="pagination-btn">
                <span>Next</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>
        @else
            <button class="pagination-btn" disabled>
                <span>Next</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </button>
        @endif
    </div>
</div>

@else
<div class="empty-state">
    <div class="empty-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--maroon)" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
    </div>
    <h5>No books found</h5>
    <p>Try a different search term or filter</p>
</div>
@endif

@if(!empty($myReservations) && $myReservations->count())
<div style="margin-top:32px;">
    <h3 style="font-size:1.1rem;font-weight:700;color:#800000;margin-bottom:12px;display:flex;align-items:center;gap:7px;">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
        </svg>
        My Reservations
    </h3>
    @foreach($myReservations as $r)
    <div style="background:#fff;border-radius:12px;padding:14px 18px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;gap:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);border:1px solid #e5e7eb;flex-wrap:wrap;">
        <div>
            <strong style="font-size:0.9rem;color:#1f2937;">{{ $r->book->title ?? 'Book #'.$r->book_id }}</strong>
            <small style="display:block;color:#6b7280;font-size:0.75rem;">{{ $r->book->author ?? '' }}</small>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span style="padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:600;background:{{ $r->status==='pending' ? '#fef3c7' : '#d1fae5' }};color:{{ $r->status==='pending' ? '#92400e' : '#065f46' }};">{{ ucfirst($r->status) }}</span>
            @if($r->status === 'pending' && $r->expires_at)
                <span class="fac-countdown" data-expires="{{ $r->expires_at->toIso8601String() }}" id="frcd-{{ $r->id }}" style="display:inline-flex;align-items:center;gap:4px;font-size:0.77rem;font-weight:700;color:#d97706;background:#fef3c7;padding:3px 10px;border-radius:20px;border:1px solid #f59e0b;">…</span>
            @endif
            @if($r->status === 'pending')
            <form method="POST" action="{{ route('faculty.reservations.destroy', $r) }}" onsubmit="return confirm('Cancel this reservation?');" style="display:inline;">
                @csrf
                <button type="submit" style="background:#fee2e2;color:#dc2626;border:none;padding:5px 12px;border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;">Cancel</button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

@push('scripts')
<script>
function toggleFilterSidebar() {
    const panel = document.getElementById('filterSidebarPanel');
    const btn   = document.getElementById('filterToggleBtn');
    if (!panel || !btn) return;
    const isOpen = panel.classList.toggle('open');
    btn.classList.toggle('active', isOpen);
    localStorage.setItem('faculty-filter-open', isOpen ? '1' : '0');
}
document.addEventListener('DOMContentLoaded', function () {
    if (localStorage.getItem('faculty-filter-open') === '1' ||
        {{ request()->hasAny(['collection','loc_class','author','subject','department_id','search']) ? 'true' : 'false' }}) {
        const panel = document.getElementById('filterSidebarPanel');
        const btn   = document.getElementById('filterToggleBtn');
        if (panel) panel.classList.add('open');
        if (btn)   btn.classList.add('active');
    }
});

(function() {
    function updateFacCountdowns() {
        document.querySelectorAll('.fac-countdown[data-expires]').forEach(el => {
            const diff = Math.floor((new Date(el.dataset.expires) - Date.now()) / 1000);
            if (diff <= 0) {
                el.textContent = 'Expired';
                el.style.color = '#6b7280'; el.style.background = '#f3f4f6'; el.style.borderColor = '#d1d5db';
            } else {
                const m = Math.floor(diff / 60), s = diff % 60;
                el.textContent = `⏱ ${m}:${String(s).padStart(2,'0')}`;
            }
        });
    }
    updateFacCountdowns();
    setInterval(updateFacCountdowns, 1000);
})();

// Table of Contents Accordion Toggle
function toggleBookToc(id, btn) {
    const el = document.getElementById(id);
    if (!el) return;
    const isShown = el.style.display !== 'none';
    el.style.display = isShown ? 'none' : 'block';
    if (btn) btn.classList.toggle('active', !isShown);
}

// Lightbox modal for TOC images
function openImageModal(src) {
    const modal = document.getElementById('imagePreviewModal');
    const modalImg = document.getElementById('imagePreviewTarget');
    if (modal && modalImg) {
        modalImg.src = src;
        modal.classList.add('show');
    }
}
function closeImageModal() {
    const modal = document.getElementById('imagePreviewModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

// 300ms search debounce
(function() {
    const input = document.getElementById('facultySearchInput');
    const form = document.getElementById('facultySearchForm');
    if (input && form) {
        let timer = null;
        input.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(() => {
                form.submit();
            }, 300);
        });
    }
})();
</script>

{{-- Image Preview Modal --}}
<div class="image-preview-modal" id="imagePreviewModal" onclick="if(event.target===this) closeImageModal()">
    <div class="image-preview-content">
        <button type="button" class="image-preview-close" onclick="closeImageModal()">&times;</button>
        <img id="imagePreviewTarget" src="" alt="TOC Preview">
    </div>
</div>
@endpush

@endsection