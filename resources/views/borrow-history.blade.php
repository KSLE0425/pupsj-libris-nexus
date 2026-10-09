@extends('layouts.admin')

@section('content')
<div class="borrow-history-page">
    <!-- Back to Books Button -->
    <div class="back-button-container">
        <a href="{{ route('admin.books') }}" class="back-to-books-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            <span>Back to Books</span>
        </a>
    </div>

    <h2>Borrow History</h2>
    <p class="page-subtitle">Track all book borrowing and return activities in the library.</p>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <!-- Search Card -->
    <div class="card search-card">
        <div class="search-card-content">
            <div class="search-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
            </div>
            <div class="search-text">
                <h3>Search Borrow Records</h3>
                <p>Find borrow records by student name or book title</p>
            </div>
            <form class="search-form" onsubmit="return false;">
                <input type="text" id="searchInput" placeholder="Enter student name or book title..." class="search-input">
                <button class="btn-primary" onclick="filterRows()">
                    <span>Search</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-group">
            <label>Status</label>
            <select id="statusFilter" class="filter-select">
                <option value="">All Status</option>
                <option value="active">Borrowed</option>
                <option value="completed">Returned</option>
            </select>
        </div>
        <div class="filter-actions">
            <button id="resetFilters" class="btn-reset">Reset Filters</button>
        </div>
    </div>

    <!-- Borrow History Table -->
    <div class="card">
        <div class="card-header">
            <h4>Borrow & Return Records</h4>
            <span class="card-badge" id="totalRecordsCount">{{ $usages->count() }} total records</span>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="historyTable">
                <thead>
                    <tr>
                        <th class="col-student">Borrower</th>
                        <th class="col-book">Book Title</th>
                        <th class="col-author">Author</th>
                        <th class="col-year">Year</th>
                        <th class="col-callnum">Call Number</th>
                        <th class="col-borrowed">Borrowed At</th>
                        <th class="col-returned">Returned At</th>
                        <th class="col-status">Status</th>
                        <th class="col-remarks">Remarks</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody">
                    @forelse($usages as $usage)
                    @php
                        $borrowerName = $usage->student
                            ? ($usage->student->first_name . ' ' . $usage->student->last_name)
                            : ($usage->faculty ? ($usage->faculty->first_name . ' ' . $usage->faculty->last_name) : 'Unknown');
                        $borrowerLabel = $usage->faculty ? 'Faculty' : 'Student';
                    @endphp
                    <tr data-student="{{ strtolower($borrowerName) }}"
                        data-book="{{ strtolower($usage->book->title ?? '') }}"
                        data-status="{{ $usage->status }}">
                        <td class="col-student">
                            {{ $borrowerName }}
                            <small style="display:block; color:#6b7280; font-size:0.7rem;">{{ $borrowerLabel }}</small>
                        </td>
                        <td class="col-book">{{ $usage->book->title ?? 'Unknown Book' }}</td>
                        <td class="col-author">{{ $usage->book->author ?? '—' }}</td>
                        <td class="col-year">{{ $usage->book->publication_year ?? '—' }}</td>
                        <td class="col-callnum">{{ $usage->book->loc_number ?? '—' }}</td>
                        <td class="col-borrowed date-cell">{{ $usage->time_in ? \Carbon\Carbon::parse($usage->time_in)->format('Y-m-d H:i') : $usage->created_at->format('Y-m-d H:i') }}</td>
                        <td class="col-returned date-cell">
                            @if($usage->time_out)
                                {{ \Carbon\Carbon::parse($usage->time_out)->format('Y-m-d H:i') }}
                            @else
                                <span class="text-muted">Not returned</span>
                            @endif
                        </td>
                        <td class="col-status status-cell">
                            @if($usage->status === 'active')
                                <span class="status-badge borrowed">Borrowed</span>
                            @elseif($usage->status === 'completed')
                                <span class="status-badge returned">Returned</span>
                            @else
                                <span class="status-badge">{{ ucfirst($usage->status) }}</span>
                            @endif
                        </td>
                        <td class="col-remarks">
                            {{ $usage->remarks ?? '—' }}
                        </td>
                        <td class="col-actions" style="text-align:center;">
                            @if($usage->status === 'active')
                                @php $ackCount = (int)!!$usage->acknowledged_by_admin1 + (int)!!$usage->acknowledged_by_admin2 + (int)!!$usage->acknowledged_by_admin3; @endphp
                                @if($ackCount >= 3)
                                    <span class="status-badge returned" style="font-size:0.65rem;">✓ ×3</span>
                                @else
                                    <form method="POST" action="{{ route('admin.borrow-history.acknowledge', $usage->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="action-btn acknowledge" onclick="return confirm('Acknowledge this borrow?')">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            Ack ({{ $ackCount }}/3)
                                        </button>
                                    </form>
                                @endif
                            @else
                                @php
                                    $hasDamagePenalty = \App\Models\LibraryPenalty::where('book_usage_id', $usage->id)
                                        ->where('penalty_type', 'damage')->exists();
                                @endphp
                                @if(!$hasDamagePenalty)
                                    <button type="button" class="action-btn damage-btn"
                                        onclick="toggleDamageForm({{ $usage->id }})">
                                        ⚠ Record Damage
                                    </button>
                                    <div id="damage-form-{{ $usage->id }}" style="display:none; margin-top:8px; text-align:left;">
                                        <form method="POST" action="{{ route('admin.penalty.damage', $usage->id) }}">
                                            @csrf
                                            <div style="margin-bottom:6px;">
                                                <label for="damage-amount-{{ $usage->id }}" style="font-size:0.75rem; font-weight:600;">Amount (₱)</label>
                                                <input id="damage-amount-{{ $usage->id }}" type="number" name="damage_amount" min="0" step="0.01" required
                                                    style="width:100%; padding:5px 8px; border:1px solid #d1d5db; border-radius:6px; font-size:0.8rem;">
                                            </div>
                                            <div style="margin-bottom:6px;">
                                                <label for="damage-desc-{{ $usage->id }}" style="font-size:0.75rem; font-weight:600;">Description</label>
                                                <textarea id="damage-desc-{{ $usage->id }}" name="damage_description" required rows="2"
                                                    style="width:100%; padding:5px 8px; border:1px solid #d1d5db; border-radius:6px; font-size:0.8rem; resize:vertical;"></textarea>
                                            </div>
                                            <div style="display:flex; gap:6px;">
                                                <button type="submit" class="action-btn damage-save">Save</button>
                                                <button type="button" class="action-btn" onclick="toggleDamageForm({{ $usage->id }})" style="background:#f3f4f6; color:#374151;">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    @php
                                        $dp = \App\Models\LibraryPenalty::where('book_usage_id', $usage->id)
                                            ->where('penalty_type', 'damage')->first();
                                    @endphp
                                    @if($dp)
                                        <span class="status-badge" style="background:#FEF2F2; color:#991B1B; font-size:0.65rem;">
                                            Damage: ₱{{ number_format($dp->amount, 2) }}
                                        </span>
                                    @endif
                                @endif
                                @if($usage->acknowledged_by_admin1)
                                    <span class="status-badge returned" style="font-size:0.65rem; display:block; margin-top:2px;">✓ {{ $usage->acknowledged_by_admin1 }}</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="10" class="empty-state-table">
                            <div class="empty-state-content">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                <p>No borrow records found</p>
                                <small>Borrow history will appear here</small>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Controls -->
        <div class="pagination-container" id="paginationContainer" style="display: none;">
            <div class="pagination-info" id="paginationInfo"></div>
            <div class="pagination-controls">
                <button id="prevPageBtn" class="pagination-btn" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                    <span>Previous</span>
                </button>
                <div class="page-numbers" id="pageNumbers"></div>
                <button id="nextPageBtn" class="pagination-btn">
                    <span>Next</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div id="noResults" class="empty-state" style="display:none;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p>No records match your filters</p>
        </div>
    </div>
</div>

<style>
/* PUP Theme Variables */
.borrow-history-page {
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

/* Back Button Container */
.back-button-container {
    margin-bottom: 1rem;
}

.back-to-books-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: transparent;
    color: var(--pup-maroon);
    border: 2px solid var(--pup-maroon);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.2s;
    min-height: 44px;
}

.back-to-books-btn:hover {
    background: var(--pup-maroon);
    color: white;
    transform: translateX(-2px);
}

.back-to-books-btn svg {
    stroke: currentColor;
}

.back-to-books-btn span {
    display: inline;
}

/* Page Header */
.borrow-history-page h2 {
    margin: 0 0 8px 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
    letter-spacing: -0.025em;
    word-break: break-word;
}

.borrow-history-page .page-subtitle {
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

.card-badge {
    font-size: 0.75rem;
    color: var(--text-muted);
    background: var(--bg-main);
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    margin-top: -2px;
    display: inline-block;
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
    min-width: 280px;
}

.search-input {
    flex: 1;
    padding: 0.75rem 1.25rem;
    border: none;
    border-radius: 10px;
    font-size: 0.9375rem;
    outline: none;
    min-height: 48px;
}

.search-input:focus {
    box-shadow: 0 0 0 3px rgba(255,199,44,0.3);
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

/* Filter Bar */
.filter-bar {
    background: white;
    border-radius: var(--radius);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem;
    align-items: flex-end;
    border: 1px solid var(--border);
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
    letter-spacing: 0.5px;
}

.filter-select {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 0.875rem;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
    min-height: 44px;
}

.filter-select:focus {
    outline: none;
    border-color: var(--pup-maroon);
    box-shadow: 0 0 0 3px rgba(128,0,0,0.1);
}

.filter-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-reset {
    background: var(--pup-maroon);
    color: white;
    border: none;
    padding: 0.625rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    min-height: 44px;
}

.btn-reset:hover {
    background: var(--pup-maroon-dark);
    transform: translateY(-1px);
}

/* Table */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 0 -0.5rem;
    padding: 0 0.5rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 700px;
}

/* Column Widths */
.data-table .col-student {
    min-width: 180px;
}
.data-table .col-book {
    min-width: 200px;
}
.data-table .col-borrowed {
    min-width: 140px;
}
.data-table .col-returned {
    min-width: 140px;
}
.data-table .col-status {
    min-width: 100px;
    text-align: center;
}

.data-table th {
    background: var(--pup-maroon);
    color: white;
    padding: 1rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.8125rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table th.col-status {
    text-align: center;
}

.data-table td {
    padding: 1rem 1rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.875rem;
    word-break: break-word;
}

.data-table td.status-cell {
    text-align: center;
}

.data-table td.date-cell {
    font-family: monospace;
    font-size: 0.8125rem;
    white-space: nowrap;
}

.data-table tbody tr {
    transition: background 0.2s;
}

.data-table tbody tr:hover {
    background: #FEFCE8;
}

/* Empty Row */
.empty-row td {
    padding: 0 !important;
}

.empty-state-table {
    text-align: center;
    padding: 0;
}

.empty-state-content {
    padding: 3rem;
    text-align: center;
}

.empty-state-content svg {
    margin-bottom: 1rem;
    opacity: 0.5;
    stroke: var(--text-muted);
    max-width: 100%;
}

.empty-state-content p {
    margin: 0 0 0.5rem 0;
    color: var(--text-muted);
    font-size: 1rem;
}

.empty-state-content small {
    color: var(--text-muted);
    font-size: 0.8125rem;
}

/* Empty State for Filtered Results */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-muted);
    font-size: 0.9375rem;
}

.empty-state svg {
    margin-bottom: 1rem;
    opacity: 0.5;
    stroke: var(--text-muted);
    max-width: 100%;
}

.empty-state p {
    margin: 0;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge.borrowed {
    background: #FEF3C7;
    color: #92400E;
}

.status-badge.returned {
    background: #ECFDF5;
    color: #065F46;
}

.status-badge.archived {
    background: #F3F4F6;
    color: #6B7280;
}

.text-muted {
    color: var(--text-muted);
    font-size: 0.75rem;
}

/* Action Buttons in Table */
.action-btn {
    padding: 0.375rem 0.875rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    display: inline-block;
    min-width: 70px;
    min-height: 32px;
    margin: 1px;
}

.action-btn.acknowledge {
    background: #DBEAFE;
    color: #1E40AF;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.action-btn.acknowledge:hover {
    background: #3B82F6;
    color: white;
    transform: translateY(-1px);
}

.action-btn.damage-btn {
    background: #FEF3C7;
    color: #92400E;
}
.action-btn.damage-btn:hover { background: #F59E0B; color: white; }

.action-btn.damage-save {
    background: #800000;
    color: white;
}
.action-btn.damage-save:hover { background: #660000; }

/* Pagination Styles */
.pagination-container {
    padding: 1.25rem 1.5rem;
    border-top: 1px solid var(--border);
    background: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.pagination-info {
    font-size: 0.875rem;
    color: var(--text-muted);
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
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    min-height: 40px;
}

.pagination-btn:hover:not(:disabled) {
    background: var(--pup-gold);
    border-color: var(--pup-gold);
    color: var(--pup-maroon);
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
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    min-height: 40px;
}

.page-number:hover {
    background: var(--pup-gold);
    border-color: var(--pup-gold);
    color: var(--pup-maroon);
}

.page-number.active {
    background: var(--pup-maroon);
    border-color: var(--pup-maroon);
    color: white;
}

/* ============================================ */
/* ENHANCED MOBILE RESPONSIVENESS */
/* ============================================ */

@media (max-width: 1024px) {
    .borrow-history-page {
        padding: 0 0.75rem;
    }
}

@media (max-width: 768px) {
    .borrow-history-page {
        padding: 0 0.5rem;
    }
    
    .borrow-history-page h2 {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
    }
    
    .back-to-books-btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        min-height: 40px;
    }
    
    .back-to-books-btn svg {
        width: 14px;
        height: 14px;
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
        flex-direction: column;
        width: 100%;
        min-width: auto;
    }
    
    .search-input {
        width: 100%;
    }
    
    .search-form .btn-primary {
        width: 100%;
        justify-content: center;
    }
    
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .filter-actions {
        justify-content: stretch;
    }
    
    .btn-reset {
        width: 100%;
    }
    
    .card-header {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
        padding: 1rem;
    }
    
    .card-header h4 {
        font-size: 1rem;
    }
    
    .table-responsive {
        margin: 0 -0.5rem;
        padding: 0 0.5rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .data-table td.date-cell {
        font-size: 0.7rem;
        white-space: normal;
        word-break: break-word;
    }
    
    .status-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
    }
    
    .pagination-container {
        flex-direction: column;
        align-items: center;
        padding: 1rem;
    }
    
    .pagination-controls {
        justify-content: center;
    }
    
    .pagination-btn {
        padding: 0.375rem 0.75rem;
    }
    
    .pagination-btn span {
        display: inline;
    }
    
    .page-number {
        min-width: 2rem;
        min-height: 2rem;
        font-size: 0.75rem;
    }
    
    .empty-state {
        padding: 2rem 1rem;
    }
    
    .empty-state-content {
        padding: 2rem 1rem;
    }
    
    .empty-state-content p {
        font-size: 0.875rem;
    }
    
    .empty-state-content small {
        font-size: 0.75rem;
    }
}

@media (max-width: 480px) {
    .borrow-history-page h2 {
        font-size: 1.3rem;
    }
    
    .back-to-books-btn {
        padding: 0.35rem 0.7rem;
        font-size: 0.75rem;
        min-height: 36px;
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
    
    .search-input {
        padding: 0.6rem 1rem;
        font-size: 0.875rem;
        min-height: 42px;
    }
    
    .btn-primary {
        padding: 0.6rem 1rem;
        font-size: 0.8rem;
        min-height: 42px;
    }
    
    .filter-bar {
        padding: 1rem;
    }
    
    .filter-select {
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        min-height: 40px;
    }
    
    .btn-reset {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        min-height: 40px;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.5rem 0.375rem;
        font-size: 0.7rem;
    }
    
    .status-badge {
        font-size: 0.65rem;
        padding: 0.15rem 0.5rem;
    }
    
    .pagination-info {
        font-size: 0.75rem;
    }
    
    .pagination-btn {
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
        min-height: 36px;
    }
    
    .page-number {
        min-width: 1.75rem;
        min-height: 1.75rem;
        font-size: 0.7rem;
    }
}

/* Landscape mode optimization */
@media (max-width: 768px) and (orientation: landscape) {
    .search-card-content {
        flex-direction: row;
        flex-wrap: wrap;
        text-align: left;
    }
    
    .search-text {
        text-align: left;
    }
    
    .search-form {
        flex-direction: row;
        width: auto;
        flex: 2;
    }
    
    .search-input {
        width: auto;
    }
    
    .search-form .btn-primary {
        width: auto;
    }
    
    .filter-bar {
        flex-direction: row;
        flex-wrap: wrap;
    }
    
    .filter-group {
        flex: 2;
    }
    
    .filter-actions {
        flex: 1;
    }
    
    .btn-reset {
        width: auto;
    }
}
</style>

<script>
// Pagination variables
let currentPage = 1;
const itemsPerPage = 10;
let allFilteredRows = [];

// Function to update the table based on current page and filtered rows
function updateTableDisplay() {
    const tableBody = document.querySelector('#historyTable tbody');
    const noResultsDiv = document.getElementById('noResults');
    const historyTable = document.getElementById('historyTable');
    const paginationContainer = document.getElementById('paginationContainer');
    const totalRecordsSpan = document.getElementById('totalRecordsCount');
    const emptyRow = document.querySelector('.empty-row');
    
    if (!tableBody) return;
    
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageRows = allFilteredRows.slice(startIndex, endIndex);
    
    // Clear table body but keep thead
    const originalRows = Array.from(tableBody.querySelectorAll('tr:not(.empty-row)'));
    originalRows.forEach(row => row.remove());
    
    if (pageRows.length === 0 && allFilteredRows.length === 0) {
        // Show empty state within table
        if (emptyRow) {
            emptyRow.style.display = '';
        } else {
            // Add empty row if it doesn't exist
            const newEmptyRow = document.createElement('tr');
            newEmptyRow.className = 'empty-row';
            newEmptyRow.innerHTML = `
                <td colspan="5" class="empty-state-table">
                    <div class="empty-state-content">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <p>No borrow records found</p>
                        <small>Borrow history will appear here</small>
                    </div>
                </td>
            `;
            tableBody.appendChild(newEmptyRow);
        }
        noResultsDiv.style.display = 'none';
        paginationContainer.style.display = 'none';
        if (totalRecordsSpan) totalRecordsSpan.textContent = '0 total records';
    } else if (pageRows.length === 0 && allFilteredRows.length > 0) {
        // No results after filtering
        noResultsDiv.style.display = 'block';
        historyTable.style.display = 'none';
        paginationContainer.style.display = 'none';
        if (totalRecordsSpan) totalRecordsSpan.textContent = allFilteredRows.length + ' total records';
    } else {
        noResultsDiv.style.display = 'none';
        historyTable.style.display = 'table';
        
        // Append the rows for current page
        pageRows.forEach(row => tableBody.appendChild(row));
        
        // Update pagination UI
        updatePaginationUI();
        
        // Show/hide pagination container
        if (allFilteredRows.length > itemsPerPage) {
            paginationContainer.style.display = 'flex';
        } else {
            paginationContainer.style.display = 'none';
        }
        
        // Update total count display
        const totalCount = allFilteredRows.length;
        if (totalRecordsSpan) totalRecordsSpan.textContent = totalCount + ' total records';
    }
}

// Update pagination controls and info
function updatePaginationUI() {
    const totalPages = Math.ceil(allFilteredRows.length / itemsPerPage);
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, allFilteredRows.length);
    const paginationInfo = document.getElementById('paginationInfo');
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    const pageNumbersDiv = document.getElementById('pageNumbers');
    
    // Update info text
    if (paginationInfo) {
        if (allFilteredRows.length > 0) {
            paginationInfo.textContent = `Showing ${startItem} to ${endItem} of ${allFilteredRows.length} records`;
        } else {
            paginationInfo.textContent = `Showing 0 records`;
        }
    }
    
    // Update prev/next buttons
    if (prevBtn) {
        prevBtn.disabled = currentPage === 1;
    }
    if (nextBtn) {
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
    }
    
    // Generate page numbers
    if (pageNumbersDiv) {
        pageNumbersDiv.innerHTML = '';
        
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) {
                addPageNumber(i);
            }
        } else {
            // Show first page
            addPageNumber(1);
            
            if (currentPage > 3) {
                addEllipsis();
            }
            
            // Pages around current
            let start = Math.max(2, currentPage - 1);
            let end = Math.min(totalPages - 1, currentPage + 1);
            
            for (let i = start; i <= end; i++) {
                addPageNumber(i);
            }
            
            if (currentPage < totalPages - 2) {
                addEllipsis();
            }
            
            // Show last page
            addPageNumber(totalPages);
        }
    }
}

function addPageNumber(pageNum) {
    const pageBtn = document.createElement('button');
    pageBtn.textContent = pageNum;
    pageBtn.className = 'page-number' + (pageNum === currentPage ? ' active' : '');
    pageBtn.onclick = () => goToPage(pageNum);
    document.getElementById('pageNumbers').appendChild(pageBtn);
}

function addEllipsis() {
    const ellipsis = document.createElement('span');
    ellipsis.textContent = '...';
    ellipsis.style.padding = '0 0.25rem';
    ellipsis.style.color = 'var(--text-muted)';
    document.getElementById('pageNumbers').appendChild(ellipsis);
}

function goToPage(page) {
    const totalPages = Math.ceil(allFilteredRows.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    updateTableDisplay();
}

function nextPage() {
    const totalPages = Math.ceil(allFilteredRows.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        updateTableDisplay();
    }
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        updateTableDisplay();
    }
}

function toggleDamageForm(usageId) {
    const form = document.getElementById('damage-form-' + usageId);
    if (form) form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function filterRows() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const allRows = Array.from(document.querySelectorAll('#historyTable tbody tr:not(.empty-row)'));
    
    // Get all original rows from the initial page load stored in a data attribute
    // We need to get the original rows that were loaded by Laravel
    let originalRows = window.originalHistoryRows || [];
    
    // If original rows not stored yet, store them
    if (originalRows.length === 0 && allRows.length > 0) {
        originalRows = allRows;
        window.originalHistoryRows = originalRows;
    }
    
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const status = statusFilter ? statusFilter.value : '';
    
    let filtered = originalRows.filter(row => {
        const student = row.getAttribute('data-student') || '';
        const book = row.getAttribute('data-book') || '';
        const rowStatus = row.getAttribute('data-status') || '';
        
        const matchesSearch = searchTerm === '' || student.includes(searchTerm) || book.includes(searchTerm);
        const matchesStatus = status === '' || rowStatus === status;
        
        return matchesSearch && matchesStatus;
    });
    
    // Store filtered rows globally
    allFilteredRows = filtered;
    
    // Reset to first page
    currentPage = 1;
    
    // Update display
    updateTableDisplay();
}

// Reset filters
function resetFilters() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) searchInput.value = '';
    if (statusFilter) statusFilter.value = '';
    
    filterRows();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Store original rows
    const allRows = Array.from(document.querySelectorAll('#historyTable tbody tr:not(.empty-row)'));
    window.originalHistoryRows = allRows;
    allFilteredRows = [...allRows];
    
    // Initial pagination setup
    updateTableDisplay();
    
    // Set up pagination button listeners
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', previousPage);
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', nextPage);
    }
    
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const resetBtn = document.getElementById('resetFilters');
    
    if (searchInput) searchInput.addEventListener('input', filterRows);
    if (statusFilter) statusFilter.addEventListener('change', filterRows);
    if (resetBtn) resetBtn.addEventListener('click', resetFilters);
});
</script>
@endsection