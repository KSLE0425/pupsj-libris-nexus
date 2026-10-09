@extends('layouts.admin')

@section('content')
<div class="archived-students-page">
    <div class="content-container">
        <h2>Archived Students</h2>
        <p class="page-subtitle">Soft-deleted students. Restore to make them active again.</p>

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
                    <h3>Search Archived Students</h3>
                    <p>Find archived students by name, student number, or course</p>
                </div>
                <form class="search-form" onsubmit="return false;">
                    <input type="text" id="liveSearch" placeholder="Enter name, student number, or course..." class="search-input">
                    <button class="btn-primary" onclick="filterArchivedStudents()">
                        <span>Search</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="/users" class="btn-outline">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                <span>Back to Active Users</span>
            </a>
        </div>

        <!-- Archived Students Table -->
        <div class="card">
            <div class="card-header">
                <h4>Archived Student Records</h4>
                <span class="card-badge" id="totalRecordsCount">{{ $students->count() }} archived students</span>
            </div>
            <div class="table-responsive">
                <table class="data-table" id="studentsTable">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th class="col-number">Student Number</th>
                            <th class="col-name">Name</th>
                            <th class="col-program"<>Program</th>
                            <th class="col-year">Year</th>
                            <th class="col-email">Email</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        @forelse($students as $student)
                        <tr data-search="{{ strtolower($student->id . ' ' . $student->student_number . ' ' . $student->first_name . ' ' . $student->last_name . ' ' . $student->program . ' ' . $student->year_level . ' ' . $student->email) }}">
                            <td class="col-id">{{ $student->id }}</td>
                            <td class="col-number">{{ $student->student_number }}</td>
                            <td class="col-name">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </td>
                            <td class="col-program">{{ $student->program }}</td>
                            <td class="col-year">{{ $student->year_level }}</td>
                            <td class="col-email">{{ $student->email }}</td>
                            <td class="col-actions">
                                <div class="actions-wrapper">
                                    <form method="POST" action="/students/restore/{{ $student->id }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="action-btn restore" onclick="return confirm('Restore this student? They will become active again.');">Restore</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr class="empty-row">
                            <td colspan="7" class="empty-state-table">
                                <div class="empty-state-content">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    <p>No archived students found</p>
                                    <small>Archived students will appear here</small>
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
                <p>No archived students match your search</p>
            </div>
        </div>
    </div>
</div>

<style>
/* PUP Theme Variables */
.archived-students-page {
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
}

/* Content Container - Matches search card width */
.content-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0;
}

/* Page Header */
.archived-students-page h2 {
    margin: 0 0 8px 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
    letter-spacing: -0.025em;
    word-break: break-word;
}

.archived-students-page .page-subtitle {
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
    min-height: 48px;
}

.btn-outline:hover {
    background: var(--pup-maroon);
    color: white;
    transform: translateY(-2px);
}

.btn-outline svg {
    stroke: currentColor;
}

.btn-outline span {
    display: inline;
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
    width: 5%;
    text-align: center;
}
.data-table .col-number {
    width: 12%;
}
.data-table .col-name {
    width: 20%;
}
.data-table .col-program {
    width: 12%;
}
.data-table .col-year {
    width: 8%;
    text-align: center;
}
.data-table .col-email {
    width: 28%;
}
.data-table .col-actions {
    width: 15%;
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

.data-table th.col-id {
    text-align: center;
}
.data-table th.col-year {
    text-align: center;
}
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

.data-table td.col-id {
    text-align: center;
}
.data-table td.col-year {
    text-align: center;
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

.data-table tbody tr.hidden {
    display: none;
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
    margin: 0;
}

/* Action Buttons in Table */
.actions-wrapper {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-btn {
    padding: 0.375rem 0.875rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    min-width: 70px;
    min-height: 36px;
}

.action-btn.restore {
    background: #16a34a;
    color: white;
}

.action-btn.restore:hover {
    background: #15803d;
    transform: translateY(-1px);
}

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

.pagination-btn span {
    display: inline;
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
    .archived-students-page {
        padding: 0 0.75rem;
    }
}

@media (max-width: 768px) {
    .archived-students-page {
        padding: 0 0.5rem;
    }
    
    .archived-students-page h2 {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
    }
    
    .content-container {
        padding: 0;
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
    .archived-students-page h2 {
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
    
    .action-buttons {
        flex-direction: row;
    }
    
    .btn-outline {
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
    const tableBody = document.querySelector('#studentsTable tbody');
    const noResultsDiv = document.getElementById('noResults');
    const studentsTable = document.getElementById('studentsTable');
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
                <td colspan="7" class="empty-state-table">
                    <div class="empty-state-content">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <p>No archived students found</p>
                        <small>Archived students will appear here</small>
                    </div>
                </td>
            `;
            tableBody.appendChild(newEmptyRow);
        }
        noResultsDiv.style.display = 'none';
        paginationContainer.style.display = 'none';
        if (totalRecordsSpan) totalRecordsSpan.textContent = '0 archived students';
    } else if (pageRows.length === 0 && allFilteredRows.length > 0) {
        // No results after filtering
        noResultsDiv.style.display = 'block';
        studentsTable.style.display = 'none';
        paginationContainer.style.display = 'none';
        if (totalRecordsSpan) totalRecordsSpan.textContent = allFilteredRows.length + ' archived students';
    } else {
        noResultsDiv.style.display = 'none';
        studentsTable.style.display = 'table';
        
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
        if (totalRecordsSpan) totalRecordsSpan.textContent = totalCount + ' archived students';
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
            paginationInfo.textContent = `Showing ${startItem} to ${endItem} of ${allFilteredRows.length} archived students`;
        } else {
            paginationInfo.textContent = `Showing 0 archived students`;
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

function filterArchivedStudents() {
    const input = document.getElementById('liveSearch');
    const allRows = Array.from(document.querySelectorAll('#studentsTable tbody tr:not(.empty-row)'));
    
    // Get all original rows from the initial page load stored in a data attribute
    let originalRows = window.originalArchivedRows || [];
    
    // If original rows not stored yet, store them
    if (originalRows.length === 0 && allRows.length > 0) {
        originalRows = allRows;
        window.originalArchivedRows = originalRows;
    }
    
    const q = (input ? input.value : '').trim().toLowerCase();
    
    let filtered = originalRows.filter(row => {
        const text = (row.getAttribute('data-search') || '');
        return q === '' || text.indexOf(q) !== -1;
    });
    
    // Store filtered rows globally
    allFilteredRows = filtered;
    
    // Reset to first page
    currentPage = 1;
    
    // Update display
    updateTableDisplay();
}

// Auto-search as you type
document.addEventListener('DOMContentLoaded', function() {
    // Store original rows
    const allRows = Array.from(document.querySelectorAll('#studentsTable tbody tr:not(.empty-row)'));
    window.originalArchivedRows = allRows;
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
    
    const input = document.getElementById('liveSearch');
    if (input) {
        input.addEventListener('input', filterArchivedStudents);
    }
});
</script>
@endsection