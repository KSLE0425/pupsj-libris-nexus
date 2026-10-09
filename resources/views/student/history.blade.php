{{-- resources/views/student/history.blade.php --}}
@extends('layouts.student')

@section('title', 'Borrowing History')

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

    /* ── HISTORY CARD ── */
    .history-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .history-header {
        padding: 16px 24px;
        background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .history-header svg {
        flex-shrink: 0;
    }
    .history-header h5 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
    }
    .history-header p {
        margin: 2px 0 0 0;
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.75);
    }

    /* ── HISTORY LIST ── */
    .history-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .history-item {
        padding: 20px 24px;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s;
    }
    .history-item:last-child {
        border-bottom: none;
    }
    .history-item:hover {
        background: rgba(128, 0, 0, 0.02);
    }

    .history-item-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }
    .book-info-section {
        flex: 1;
        min-width: 180px;
    }
    .book-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--maroon);
        margin-bottom: 6px;
        line-height: 1.4;
    }
    .book-author {
        font-size: 0.78rem;
        color: #888;
        margin-bottom: 8px;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .status-badge.borrowed {
        background: #FFF3E0;
        color: #E65100;
    }
    .status-badge.returned {
        background: #E8F5E9;
        color: #2E7D32;
    }

    /* Date Section */
    .date-section {
        margin-top: 12px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    .date-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.72rem;
        color: #777;
    }
    .date-item svg {
        flex-shrink: 0;
        opacity: 0.6;
    }
    .date-label {
        font-weight: 600;
        color: var(--maroon);
        margin-right: 4px;
    }

    /* ── PAGINATION ── */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        margin-bottom: 16px;
    }
    .pagination-wrapper .pagination {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .pagination-wrapper .page-item {
        display: inline-block;
    }
    .pagination-wrapper .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 12px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        color: #555;
        font-size: 0.8rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
    }
    .pagination-wrapper .page-link:hover {
        background: var(--yellow);
        border-color: var(--yellow);
        color: var(--maroon);
        transform: translateY(-1px);
    }
    .pagination-wrapper .active .page-link {
        background: var(--maroon);
        border-color: var(--maroon);
        color: white;
    }
    .pagination-wrapper .disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ── EMPTY STATE ── */
    .empty-card {
        background: #fff;
        border-radius: 20px;
        text-align: center;
        padding: 48px 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    }
    .empty-icon {
        width: 80px;
        height: 80px;
        background: rgba(128, 0, 0, 0.06);
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .empty-card h5 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--maroon);
        margin-bottom: 8px;
    }
    .empty-card p {
        font-size: 0.85rem;
        color: #999;
        margin-bottom: 24px;
    }
    .btn-browse {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--yellow);
        color: var(--maroon);
        font-family: 'Poppins', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 10px 28px;
        border-radius: 40px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-browse:hover {
        background: #e8d800;
        transform: translateY(-2px);
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
        .history-item-content {
            flex-direction: column;
            align-items: flex-start;
        }
        .status-badge {
            align-self: flex-start;
        }
        .date-section {
            flex-direction: column;
            gap: 10px;
        }
        .history-header {
            padding: 14px 18px;
        }
        .history-item {
            padding: 16px 18px;
        }
    }

    /* ── FILTER BAR ── */
    .hf-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        border-top: 3px solid var(--maroon);
        padding: 16px 18px;
        margin-bottom: 20px;
    }
    .hf-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr) auto;
        gap: 12px;
        align-items: end;
    }
    .hf-field { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
    .hf-field label {
        font-size: 0.68rem;
        font-weight: 700;
        color: var(--maroon);
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .hf-input-wrap { position: relative; }
    .hf-input-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa; pointer-events: none; }
    .hf-field input, .hf-field select {
        width: 100%;
        min-height: 42px;
        border: 1.5px solid #e6e1dc;
        border-radius: 10px;
        padding: 8px 12px;
        font-family: 'Poppins', sans-serif;
        font-size: 0.82rem;
        color: #333;
        background: #fdfcfa;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .hf-input-wrap input { padding-left: 36px; }
    .hf-field input:focus, .hf-field select:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(128,0,0,0.08); background: #fff; }
    .hf-actions { display: flex; gap: 8px; }
    .hf-btn {
        min-height: 42px;
        padding: 0 18px;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-size: 0.82rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
        white-space: nowrap;
    }
    .hf-btn-apply { background: var(--maroon); color: #fff; }
    .hf-btn-apply:hover { background: var(--maroon-dark); }
    .hf-btn-reset { background: #fff; color: var(--maroon); border: 1.5px solid rgba(128,0,0,0.25); }
    .hf-btn-reset:hover { background: var(--yellow-pale); color: var(--maroon); }
    .hf-error { margin-top: 8px; font-size: 0.75rem; color: #b91c1c; }
    .status-badge.overdue { background: #FEE2E2; color: #B91C1C; }
    .date-item.due-overdue, .date-item.due-overdue .date-label { color: #B91C1C; }

    @media (max-width: 991px) {
        .hf-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
        .hf-search { grid-column: 1 / -1; }
        .hf-actions { grid-column: 1 / -1; }
        .hf-actions .hf-btn { flex: 1; }
    }
    @media (max-width: 768px) {
        .hf-card { padding: 14px; border-radius: 14px; }
        .history-card { border-radius: 16px; }
        .book-title { font-size: 0.92rem; }
        .date-section { gap: 6px; }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Borrowing History</h1>
    <p>Track all the books you've borrowed and returned</p>
</div>

{{-- Search & Filters --}}
@php $hasFilters = collect($filters ?? [])->filter(fn($v) => filled($v))->isNotEmpty(); @endphp
<form method="GET" action="{{ route('student.history') }}" class="hf-card" role="search">
    <div class="hf-grid">
        <div class="hf-field hf-search">
            <label for="hf_q">Search</label>
            <div class="hf-input-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input id="hf_q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Book title or author">
            </div>
        </div>
        <div class="hf-field">
            <label for="hf_status">Status</label>
            <select id="hf_status" name="status">
                <option value="">All</option>
                <option value="borrowed" @selected(($filters['status'] ?? '') === 'borrowed')>Currently Borrowed</option>
                <option value="returned" @selected(($filters['status'] ?? '') === 'returned')>Returned</option>
                <option value="overdue" @selected(($filters['status'] ?? '') === 'overdue')>Overdue</option>
            </select>
        </div>
        <div class="hf-field">
            <label for="hf_from">From</label>
            <input id="hf_from" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="hf-field">
            <label for="hf_to">To</label>
            <input id="hf_to" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="hf-actions">
            <button type="submit" class="hf-btn hf-btn-apply">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Apply
            </button>
            @if($hasFilters)
                <a href="{{ route('student.history') }}" class="hf-btn hf-btn-reset">Reset</a>
            @endif
        </div>
    </div>
    @if($errors->any())
        <div class="hf-error">{{ $errors->first() }}</div>
    @endif
</form>

@if($history && $history->count() > 0)
<div class="history-card">
    <div class="history-header">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <h5>Your Borrowing Records</h5>
            <p>{{ $history->total() }} {{ $hasFilters ? 'matching' : 'total' }} transaction(s)</p>
        </div>
    </div>

    <ul class="history-list">
        @foreach($history as $record)
        <li class="history-item">
            <div class="history-item-content">
                <div class="book-info-section">
                    <div class="book-title">{{ $record->book->title ?? 'Unknown Book' }}</div>
                    <div class="book-author">{{ $record->book->author ?? 'Unknown Author' }}</div>
                </div>
                @if($record->is_overdue)
                    <span class="status-badge overdue">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        Overdue
                    </span>
                @elseif($record->status == 'active')
                    <span class="status-badge borrowed">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Currently Borrowed
                    </span>
                @else
                    <span class="status-badge returned">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Returned
                    </span>
                @endif
            </div>

            <div class="date-section">
                <div class="date-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span><span class="date-label">Borrowed:</span> {{ $record->time_in ? $record->time_in->format('M d, Y • h:i A') : 'N/A' }}</span>
                </div>
                @if($record->status === 'active' && $record->due_date)
                <div class="date-item {{ $record->is_overdue ? 'due-overdue' : '' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span><span class="date-label">Due:</span> {{ $record->due_date->format('M d, Y') }}</span>
                </div>
                @endif
                @if($record->time_out)
                <div class="date-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span><span class="date-label">Returned:</span> {{ $record->time_out->format('M d, Y • h:i A') }}</span>
                </div>
                @endif
            </div>
        </li>
        @endforeach
    </ul>
</div>

<div class="pagination-wrapper">
    {{ $history->links('pagination::bootstrap-4') }}
</div>

@elseif($hasFilters)
<div class="empty-card">
    <div class="empty-icon">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--maroon)" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    </div>
    <h5>No records match your filters</h5>
    <p>Try a different search term, status, or date range.</p>
    <a href="{{ route('student.history') }}" class="btn-browse">Clear filters</a>
</div>

@else
<div class="empty-card">
    <div class="empty-icon">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--maroon)" stroke-width="1.5">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
        </svg>
    </div>
    <h5>No borrowing history yet</h5>
    <p>You haven't borrowed any books from the library</p>
    <a href="{{ route('student.borrow') }}" class="btn-browse">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
        </svg>
        Browse Books
    </a>
</div>
@endif
@endsection