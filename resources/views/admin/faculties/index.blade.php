@extends('layouts.admin')

@section('content')
<div class="faculty-list-page">
    <h2>Faculty Management</h2>
    <p class="page-subtitle">View and manage faculty accounts.</p>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

 <div class="action-buttons">
    <a href="{{ route('admin.faculties.archived') }}" class="btn-outline">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="4" width="20" height="5" rx="1" ry="1"/>
            <path d="M4 9v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9"/>
            <path d="M10 13h4"/>
        </svg>
        View Archived Users
    </a>
</div>

    <!-- Faculty Table -->
    <div class="card">
        <div class="card-header">
            <h4>Faculty Records</h4>
            <span class="card-badge">{{ $faculties->count() }} total</span>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="facultyTable">
                <thead>
                    <tr>
                        <th class="col-id">Employee ID</th>
                        <th class="col-name">Name</th>
                        <th class="col-dept">Department</th>
                        <th class="col-email">Email</th>
                        <th class="col-status">Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($faculties as $faculty)
                    <tr>
                        <td class="col-id">{{ $faculty->employee_id }}</td>
                        <td class="col-name">{{ $faculty->first_name }} {{ $faculty->last_name }}</td>
                        <td class="col-dept">{{ $faculty->department ?? 'N/A' }}</td>
                        <td class="col-email">{{ $faculty->email }}</td>
                        <td class="col-status">
                            @if($faculty->status === 'active')
                                <span class="status-badge active">Active</span>
                            @elseif($faculty->status === 'pending')
                                <span class="status-badge pending">Pending</span>
                            @else
                                <span class="status-badge archived">Rejected</span>
                            @endif
                        </td>
                        <td class="col-actions">
                            <div class="actions-wrapper">
                                <form method="POST" action="{{ route('admin.faculties.destroy', $faculty->id) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete" onclick="return confirm('Archive this faculty?')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                        Archive
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($faculties->isEmpty())
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p>No faculty records found</p>
            <small>Faculty accounts will appear here</small>
        </div>
        @endif
    </div>
</div>

<style>
/* PUP Theme Variables - Matching Books Page */
.faculty-list-page {
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
.faculty-list-page h2 {
    margin: 0 0 8px 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
    letter-spacing: -0.025em;
    word-break: break-word;
}

.faculty-list-page .page-subtitle {
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

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.btn-outline {
    background: transparent;
    color: var(--pup-maroon);
    border: 2px solid var(--pup-maroon);
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    min-height: 44px;
}

.btn-outline:hover {
    background: var(--pup-maroon);
    color: white;
    transform: translateY(-2px);
}

.btn-outline svg {
    stroke: currentColor;
}

.pending-badge {
    background: var(--pup-maroon);
    color: white;
    border-radius: 20px;
    padding: 0.125rem 0.5rem;
    font-size: 0.7rem;
    margin-left: 0.5rem;
}

/* Card */
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
    min-width: 800px;
}

/* Column Widths */
.data-table .col-id {
    width: 10%;
}
.data-table .col-name {
    width: 15%;
}
.data-table .col-dept {
    width: 15%;
}
.data-table .col-email {
    width: 22%;
}
.data-table .col-status {
    width: 10%;
    text-align: center;
}
.data-table .col-actions {
    width: 13%;
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

.data-table th.col-status,
.data-table th.col-actions {
    text-align: center;
}

.data-table td {
    padding: 1rem 1rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.875rem;
    word-break: break-word;
}

.data-table td.col-actions {
    text-align: center;
}

.data-table tbody tr {
    transition: background 0.2s;
}

.data-table tbody tr:hover {
    background: #FEFCE8;
}

/* Specialty Tags */
.specialty-tag {
    display: inline-block;
    background: var(--bg-main);
    color: var(--text-muted);
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.7rem;
    margin-right: 0.25rem;
    margin-bottom: 0.25rem;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-align: center;
    min-width: 70px;
}

.status-badge.active {
    background: #ECFDF5;
    color: #065F46;
}

.status-badge.pending {
    background: #FEF3C7;
    color: #92400E;
}

.status-badge.archived {
    background: #F3F4F6;
    color: #6B7280;
}

/* Action Buttons in Table */
.actions-wrapper {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.875rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    min-width: 70px;
    min-height: 34px;
    text-decoration: none;
}

.action-btn.edit {
    background: #EEF2FF;
    color: #4F46E5;
}

.action-btn.edit:hover {
    background: #4F46E5;
    color: white;
    transform: translateY(-1px);
}

.action-btn.delete {
    background: #FEF2F2;
    color: #991B1B;
}

.action-btn.delete:hover {
    background: #EF4444;
    color: white;
    transform: translateY(-1px);
}

/* Empty State */
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
    margin: 0 0 0.25rem 0;
}

.empty-state small {
    font-size: 0.8125rem;
    opacity: 0.7;
}

.text-muted {
    color: var(--text-muted);
}

/* ============================================ */
/* MOBILE RESPONSIVENESS */
/* ============================================ */

@media (max-width: 1024px) {
    .faculty-list-page {
        padding: 0 0.75rem;
    }
}

@media (max-width: 768px) {
    .faculty-list-page {
        padding: 0 0.5rem;
    }
    
    .faculty-list-page h2 {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-outline {
        width: 100%;
        justify-content: center;
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
    
    .action-btn {
        padding: 0.25rem 0.625rem;
        min-width: 60px;
        font-size: 0.7rem;
    }
    
    .status-badge {
        min-width: 55px;
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
    }
    
    .empty-state {
        padding: 2rem 1rem;
    }
}

@media (max-width: 480px) {
    .faculty-list-page h2 {
        font-size: 1.3rem;
    }
    
    .btn-outline {
        padding: 0.6rem 1rem;
        font-size: 0.8rem;
        min-height: 42px;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.5rem 0.375rem;
        font-size: 0.7rem;
    }
    
    .action-btn {
        padding: 0.2rem 0.5rem;
        min-width: 50px;
        font-size: 0.65rem;
    }
    
    .specialty-tag {
        font-size: 0.6rem;
        padding: 0.15rem 0.4rem;
    }
}
</style>
@endsection