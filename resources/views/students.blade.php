@extends('layouts.admin')

@section('content')
<div class="students-page">
    <h2>Students Management</h2>
    <p class="page-subtitle">Manage your student records, track borrowing activity, and archive inactive students.</p>

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
                <h3>Search Students</h3>
                <p>Find students by name, student number, or email</p>
            </div>
            <form class="search-form" onsubmit="return false;">
                <input type="text" id="searchInput" placeholder="Enter student name, number, or email..." class="search-input">
                <button class="btn-primary" onclick="filterAndSort()">
                    <span>Search</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-group">
            <label>Program</label>
            <select id="courseFilter" class="filter-select">
                <option value="">All Programs</option>
                @php
                    $courses = App\Models\Student::distinct()->pluck('program');
                @endphp
                @foreach($courses as $course)
                    <option value="{{ $course }}">{{ $course }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Year Level</label>
            <select id="yearFilter" class="filter-select">
                <option value="">All Years</option>
                @for($i=1; $i<=4; $i++)
                    <option value="{{ $i }}">Year {{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select id="statusFilter" class="filter-select">
                <option value="all">All Students</option>
                <option value="active">Active</option>
                <option value="archived">Archived</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Sort By</label>
            <select id="sortBy" class="filter-select">
                <option value="id_asc">ID (Ascending)</option>
                <option value="id_desc">ID (Descending)</option>
                <option value="name_asc">Name (A-Z)</option>
                <option value="name_desc">Name (Z-A)</option>
            </select>
        </div>
        <div class="filter-actions">
            <button id="resetFilters" class="btn-reset">Reset Filters</button>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="{{ route('admin.users.archived') }}" class="btn-outline">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="4" width="20" height="5" rx="1" ry="1"/>
                <path d="M4 9v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9"/>
                <path d="M10 13h4"/>
            </svg>
            <span>View Archived Users</span>
        </a>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-header">
            <h4>Student Records</h4>
            <span class="card-badge" id="totalStudentsCount">{{ $students->count() }} total students</span>
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
                    @foreach($students as $student)
<tr data-id="{{ $student->id }}"
    data-name="{{ strtolower($student->first_name . ' ' . $student->last_name) }}"
    data-number="{{ strtolower($student->student_number) }}"
    data-email="{{ strtolower($student->email) }}"
    data-course="{{ ($programMap ?? [])[$student->program] ?? $student->program }}"
    data-year="{{ $student->year_level }}"
    data-archived="{{ $student->trashed() ? 'true' : 'false' }}"
    >

                        
                        <td class="col-id">{{ $student->id }}</td>
                        <td class="col-number">{{ $student->student_number }}</td>
                        <td class="col-name">
                            {{ $student->first_name }} {{ $student->last_name }}
                            @if($student->trashed())
                                <span class="status-badge archived">Archived</span>
                            @endif
                        </td>
                        <td class="col-program">{{ ($programMap ?? [])[$student->program] ?? $student->program }}</td>
                        <td class="col-year">{{ $student->year_level }}</td>
                        <td class="col-email">{{ $student->email }}</td>
                        <td class="col-actions">
                            <div class="actions-wrapper">
                                @if(!$student->trashed())
                                    <form method="POST" action="/students/archive/{{ $student->id }}" onsubmit="return confirm('Archive this student? They can be restored later.');" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="action-btn archive">Archive</button>
                                    </form>
                                @else
                                    <span class="text-muted">Archived</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
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
            <p>No students match your filters</p>
        </div>
    </div>
</div>

<style>
/* PUP Theme Variables */
.students-page {
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
.students-page h2 {
    margin: 0 0 8px 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
    letter-spacing: -0.025em;
    word-break: break-word;
}

.students-page .page-subtitle {
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
    min-width: 160px;
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

.pending-badge {
    background: var(--pup-maroon);
    color: white;
    border-radius: 20px;
    padding: 0.125rem 0.5rem;
    font-size: 0.7rem;
    margin-left: 0.5rem;
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
    width: 25%;
}
.data-table .col-actions {
    width: 18%;
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
    cursor: pointer;
    transition: background 0.2s;
}

.data-table tbody tr:hover {
    background: #FEFCE8;
}

/* Student Name with Badge */
.student-name {
    position: relative;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.5rem;
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
    padding: 0.375rem 0.875rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    min-width: 70px;
    min-height: 34px;
}

.action-btn.archive {
    background: #FEF3C7;
    color: #92400E;
}

.action-btn.archive:hover {
    background: #F59E0B;
    color: white;
    transform: translateY(-1px);
}

.text-muted {
    color: var(--text-muted);
    font-size: 0.75rem;
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
    .students-page {
        padding: 0 0.75rem;
    }
}

@media (max-width: 768px) {
    .students-page {
        padding: 0 0.5rem;
    }

    .students-page h2 {
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

    .action-buttons {
        flex-direction: column;
    }

    .action-buttons a {
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

    .status-badge {
        font-size: 0.65rem;
        padding: 0.15rem 0.5rem;
        margin-left: 0.25rem;
    }

    .action-btn {
        padding: 0.25rem 0.625rem;
        min-width: 60px;
        font-size: 0.7rem;
    }

    .pending-badge {
        font-size: 0.65rem;
        padding: 0.1rem 0.4rem;
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
}

@media (max-width: 480px) {
    .students-page h2 {
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

    .action-buttons {
        flex-direction: row;
    }

    .action-buttons a {
        width: auto;
        flex: 1;
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
    const totalStudentsSpan = document.getElementById('totalStudentsCount');

    if (!tableBody) return;

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageRows = allFilteredRows.slice(startIndex, endIndex);

    // Clear and repopulate table body
    tableBody.innerHTML = '';

    if (pageRows.length === 0 && allFilteredRows.length === 0) {
        noResultsDiv.style.display = 'block';
        studentsTable.style.display = 'none';
        paginationContainer.style.display = 'none';
        if (totalStudentsSpan) totalStudentsSpan.textContent = '0 total students';
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
        if (totalStudentsSpan) totalStudentsSpan.textContent = totalCount + ' total students';
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
            paginationInfo.textContent = `Showing ${startItem} to ${endItem} of ${allFilteredRows.length} students`;
        } else {
            paginationInfo.textContent = `Showing 0 students`;
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

function filterAndSort() {
    const searchInput = document.getElementById('searchInput');
    const courseFilter = document.getElementById('courseFilter');
    const yearFilter = document.getElementById('yearFilter');
    const statusFilter = document.getElementById('statusFilter');
    const sortBy = document.getElementById('sortBy');
    const allRows = Array.from(document.querySelectorAll('#studentsTable tbody tr'));

    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const course = courseFilter ? courseFilter.value : '';
    const year = yearFilter ? yearFilter.value : '';
    const status = statusFilter ? statusFilter.value : 'all';
    const sortValue = sortBy ? sortBy.value : 'id_asc';

    let filtered = allRows.filter(row => {
        const name = row.getAttribute('data-name');
        const number = row.getAttribute('data-number');
        const email = row.getAttribute('data-email');
        const rowCourse = row.getAttribute('data-course');
        const rowYear = row.getAttribute('data-year');
        const isArchived = row.getAttribute('data-archived') === 'true';
        const matchesSearch = !searchTerm || name.includes(searchTerm) || number.includes(searchTerm) || email.includes(searchTerm);
        const matchesCourse = !course || rowCourse === course;
        const matchesYear = !year || rowYear === year;
        const matchesStatus = status === 'all' || (status === 'active' && !isArchived) || (status === 'archived' && isArchived);
        return matchesSearch && matchesCourse && matchesYear && matchesStatus;
    });

    // Sorting
    filtered.sort((a, b) => {
        switch(sortValue) {
            case 'id_asc': return parseInt(a.getAttribute('data-id')) - parseInt(b.getAttribute('data-id'));
            case 'id_desc': return parseInt(b.getAttribute('data-id')) - parseInt(a.getAttribute('data-id'));
            case 'name_asc': return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
            case 'name_desc': return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
            default: return 0;
        }
    });

    // Store filtered rows globally
    allFilteredRows = filtered;

    // Reset to first page
    currentPage = 1;

    // Update display
    updateTableDisplay();
}

// Event delegation for row clicks
document.addEventListener('DOMContentLoaded', function() {
    // Store all original rows
    const allOriginalRows = Array.from(document.querySelectorAll('#studentsTable tbody tr'));
    allFilteredRows = [...allOriginalRows];

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

    // Row click delegation


    // Set up filter listeners
    const searchInput = document.getElementById('searchInput');
    const courseFilter = document.getElementById('courseFilter');
    const yearFilter = document.getElementById('yearFilter');
    const statusFilter = document.getElementById('statusFilter');
    const sortBy = document.getElementById('sortBy');
    const resetBtn = document.getElementById('resetFilters');

    if (searchInput) searchInput.addEventListener('input', filterAndSort);
    if (courseFilter) courseFilter.addEventListener('change', filterAndSort);
    if (yearFilter) yearFilter.addEventListener('change', filterAndSort);
    if (statusFilter) statusFilter.addEventListener('change', filterAndSort);
    if (sortBy) sortBy.addEventListener('change', filterAndSort);
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (courseFilter) courseFilter.value = '';
            if (yearFilter) yearFilter.value = '';
            if (statusFilter) statusFilter.value = 'all';
            if (sortBy) sortBy.value = 'id_asc';
            filterAndSort();
        });
    }

    // Initial filter and sort
    filterAndSort();
});
</script>
@endsection