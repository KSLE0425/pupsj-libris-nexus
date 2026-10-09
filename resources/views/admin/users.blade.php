@extends('layouts.admin')

@section('title', 'Users Management')

@php
    $totalStudents = $students->count();
    $totalFaculty = $faculties->count();
    $totalUsers = $totalStudents + $totalFaculty;

    $activeStudents = $students->where('status', 'active')->whereNull('deleted_at')->count();
    $activeFaculty = $faculties->where('status', 'active')->whereNull('deleted_at')->count();
    $totalActive = $activeStudents + $activeFaculty;

    $dormantStudents = $students->where('status', 'inactive')->count();
    $dormantFaculty = $faculties->where('status', 'inactive')->count();
    $totalDormant = $dormantStudents + $dormantFaculty;

    $rawPrograms = App\Models\Student::distinct()->whereNotNull('program')->pluck('program');
    $mappedPrograms = [];
    foreach ($rawPrograms as $rp) {
        $code = ($programMap ?? [])[$rp] ?? $rp;
        if (!isset($mappedPrograms[$code])) {
            $mappedPrograms[$code] = ($code !== $rp) ? "$rp ($code)" : $rp;
        }
    }
    asort($mappedPrograms);

    $departments = App\Models\Faculty::distinct()->whereNotNull('department')->pluck('department')->sort();
@endphp

@section('content')
<style>
/* ── Variables & Container ───────────────────────── */
.users-page {
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

/* ── Primary & Outline Buttons ───────────────────── */
.btn-primary {
    background: var(--pup-gold); color: var(--pup-maroon);
    border: none; padding: 0.625rem 1.25rem; border-radius: 8px;
    font-weight: 700; cursor: pointer; transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 0.5rem;
    text-decoration: none; font-family: inherit; font-size: 0.9rem; white-space: nowrap;
}
.btn-primary:hover { background: var(--pup-gold-dark); transform: translateY(-1px); }

.btn-secondary {
    background: white; color: var(--pup-maroon);
    border: 1px solid var(--pup-maroon); padding: 0.625rem 1.25rem; border-radius: 8px;
    font-weight: 600; cursor: pointer; transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 0.5rem;
    text-decoration: none; font-family: inherit; font-size: 0.9rem; white-space: nowrap;
}
.btn-secondary:hover { background: rgba(128,0,0,0.05); transform: translateY(-1px); }

/* ── Alerts ──────────────────────────────────────── */
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
.stat-mini.students  { border-left-color: #3B82F6; }
.stat-mini.faculty   { border-left-color: #8B5CF6; }
.stat-mini.active    { border-left-color: #10B981; }
.stat-mini.dormant   { border-left-color: #F59E0B; }
.stat-mini-icon {
    margin: 0 auto 8px;
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.stat-mini.total    .stat-mini-icon { background: rgba(128,0,0,0.1); color: var(--pup-maroon); }
.stat-mini.students .stat-mini-icon { background: #EFF6FF; color: #2563EB; }
.stat-mini.faculty  .stat-mini-icon { background: #F5F3FF; color: #7C3AED; }
.stat-mini.active   .stat-mini-icon { background: #ECFDF5; color: #059669; }
.stat-mini.dormant  .stat-mini-icon { background: #FEF3C7; color: #D97706; }
.stat-mini-num {
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1.1;
    color: var(--pup-maroon);
}
.stat-mini.students .stat-mini-num { color: #2563EB; }
.stat-mini.faculty  .stat-mini-num { color: #7C3AED; }
.stat-mini.active   .stat-mini-num { color: #059669; }
.stat-mini.dormant  .stat-mini-num { color: #D97706; }
.stat-mini-label { font-size: 0.78rem; color: var(--text-muted); margin-top: 3px; font-weight: 500; }

/* ── Type Tabs (Students & Faculty) ──────────────── */
.type-tabs-container {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}
.type-tab-btn {
    padding: 10px 20px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: white;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-muted);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.type-tab-btn:hover {
    background: #fdfdfd;
    color: var(--pup-maroon);
}
.type-tab-btn.active {
    background: var(--pup-maroon);
    border-color: var(--pup-maroon);
    color: white;
}
.type-tab-badge {
    font-size: 0.75rem;
    padding: 2px 7px;
    border-radius: 12px;
    background: rgba(0,0,0,0.08);
    font-weight: 700;
}
.type-tab-btn.active .type-tab-badge {
    background: var(--pup-gold);
    color: var(--pup-maroon);
}

/* ── Unified Filter Bar ──────────────────────────── */
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
    box-sizing: border-box;
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
    height: 40px;
}
.btn-reset:hover { background: var(--pup-maroon-dark); transform: translateY(-1px); }

/* ── Cards & Table ───────────────────────────────── */
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
    font-weight: 600;
}

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
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.status-badge.active   { background: #ECFDF5; color: #065F46; border-color: #A7F3D0; }
.status-badge.pending  { background: #FEF3C7; color: #92400E; border-color: #FDE68A; }
.status-badge.rejected { background: #FEF2F2; color: #991B1B; border-color: #FECACA; }
.status-badge.inactive { background: #F3F4F6; color: #4B5563; border-color: #E5E7EB; }
.status-badge.archived { background: rgba(128,0,0,0.07); color: #800000; border-color: rgba(128,0,0,0.2); }

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
.action-btn.archive { background: rgba(128,0,0,0.05); color: #800000; border-color: rgba(128,0,0,0.35); }
.action-btn.archive:hover { background: #800000; color: white; border-color: #800000; }
.action-btn.approve { background: #ECFDF5; color: #065F46; border-color: #10B981; }
.action-btn.approve:hover { background: #10B981; color: white; }
.action-btn.reject  { background: #FEF2F2; color: #991B1B; border-color: #EF4444; }
.action-btn.reject:hover  { background: #EF4444; color: white; }
.action-btn.delete  { background: #FEF2F2; color: #991B1B; border-color: #EF4444; }
.action-btn.delete:hover  { background: #EF4444; color: white; }

/* ── Pagination ──────────────────────────────────── */
.pagination-container {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border);
    background: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.pagination-info { font-size: 0.85rem; color: var(--text-muted); }
.pagination-controls { display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; }
.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 7px;
    color: var(--text);
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.pagination-btn:hover:not(:disabled) { background: var(--pup-gold); border-color: var(--pup-gold); color: var(--pup-maroon); }
.pagination-btn:disabled { opacity: 0.45; cursor: not-allowed; }
.page-numbers { display: flex; gap: 4px; flex-wrap: wrap; }
.page-number {
    min-width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 6px; background: white;
    border: 1px solid var(--border); border-radius: 7px;
    color: var(--text); font-size: 0.85rem; font-weight: 500;
    cursor: pointer; transition: all 0.2s; font-family: inherit;
}
.page-number:hover { background: var(--pup-gold); border-color: var(--pup-gold); color: var(--pup-maroon); }
.page-number.active { background: var(--pup-maroon); border-color: var(--pup-maroon); color: white; }

/* ── Empty State ─────────────────────────────────── */
.empty-state { text-align: center; padding: 3rem; color: var(--text-muted); }
.empty-state svg { margin-bottom: 1rem; opacity: 0.45; }
.empty-state p { margin: 0; font-size: 0.9375rem; }

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 768px) {
    .users-page { padding: 0 0.5rem; }
    .page-header h1 { font-size: 1.4rem; }
    .data-table th, .data-table td { padding: 8px; font-size: 0.75rem; }
    .filter-sort-bar { gap: 8px; padding: 12px; }
    .stats-row { grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
    .type-tabs-container { flex-direction: column; }
}
</style>

<div class="users-page">

    {{-- ── Header ──────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h1>Users Management</h1>
            <p>Manage all library accounts — students and faculty members.</p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('admin.users.archived') }}" class="btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="5" rx="1" ry="1"/>
                    <path d="M4 9v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9"/>
                    <path d="M10 13h4"/>
                </svg>
                View Archived Users
            </a>
        </div>
    </div>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- ── Stat Cards ───────────────────────────────────── --}}
    <div class="stats-row">
        <div class="stat-mini total">
            <div class="stat-mini-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-mini-num">{{ $totalUsers }}</div>
            <div class="stat-mini-label">Total Users</div>
        </div>
        <div class="stat-mini students">
            <div class="stat-mini-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            <div class="stat-mini-num">{{ $totalStudents }}</div>
            <div class="stat-mini-label">Students</div>
        </div>
        <div class="stat-mini faculty">
            <div class="stat-mini-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div class="stat-mini-num">{{ $totalFaculty }}</div>
            <div class="stat-mini-label">Faculty</div>
        </div>
        <div class="stat-mini active">
            <div class="stat-mini-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="stat-mini-num">{{ $totalActive }}</div>
            <div class="stat-mini-label">Active Users</div>
        </div>
        <div class="stat-mini dormant">
            <div class="stat-mini-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="stat-mini-num">{{ $totalDormant }}</div>
            <div class="stat-mini-label">Dormant</div>
        </div>
    </div>

    {{-- ── Type Switcher Tabs ───────────────────────────── --}}
    <div class="type-tabs-container">
        <button type="button" class="type-tab-btn active" id="tabBtnStudents" onclick="window.switchUserType('students')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            Students Catalog
            <span class="type-tab-badge" id="badgeStudentCount">{{ $totalStudents }}</span>
        </button>
        <button type="button" class="type-tab-btn" id="tabBtnFaculty" onclick="window.switchUserType('faculty')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Faculty Catalog
            <span class="type-tab-badge" id="badgeFacultyCount">{{ $totalFaculty }}</span>
        </button>
    </div>

    {{-- ── Filter Bar (Identical to Books Management) ───── --}}
    <div class="filter-sort-bar">
        <div class="filter-group" style="flex:2 1 220px;">
            <span class="filter-label">Search Users</span>
            <input type="text" id="userSearchInput" class="filter-input" placeholder="Search by name, ID number, email…">
        </div>

        {{-- Program Filter (for students) --}}
        <div class="filter-group" id="groupProgramFilter">
            <span class="filter-label">Program</span>
            <select id="userProgramFilter" class="filter-select">
                <option value="">All Programs</option>
                @foreach($mappedPrograms as $val => $label)
                    <option value="{{ strtolower($val) }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Department Filter (for faculty) --}}
        <div class="filter-group" id="groupDeptFilter" style="display:none;">
            <span class="filter-label">Department</span>
            <select id="userDeptFilter" class="filter-select">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ strtolower($dept) }}">{{ $dept }}</option>
                @endforeach
            </select>
        </div>

        {{-- Year Filter (for students) --}}
        <div class="filter-group" id="groupYearFilter">
            <span class="filter-label">Year Level</span>
            <select id="userYearFilter" class="filter-select">
                <option value="">All Years</option>
                @for($i=1; $i<=4; $i++)
                    <option value="{{ $i }}">Year {{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="filter-group">
            <span class="filter-label">Status</span>
            <select id="userStatusFilter" class="filter-select">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
                <option value="inactive">Dormant</option>
            </select>
        </div>

        {{-- Borrow Date Range Filters --}}
        <div class="filter-group" style="flex:1 1 140px;">
            <span class="filter-label">Borrow Date From</span>
            <input type="date" id="borrowDateFrom" class="filter-input" title="Filter by borrow date from">
        </div>

        <div class="filter-group" style="flex:1 1 140px;">
            <span class="filter-label">Borrow Date To</span>
            <input type="date" id="borrowDateTo" class="filter-input" title="Filter by borrow date to">
        </div>

        <div class="filter-group" style="flex:1 1 160px;">
            <span class="filter-label">Sort By</span>
            <select id="userSortSelect" class="filter-select">
                <option value="name_asc">Name A–Z</option>
                <option value="name_desc">Name Z–A</option>
                <option value="id_asc">ID (Ascending)</option>
                <option value="id_desc">ID (Descending)</option>
                <option value="borrow_desc">Last Borrow (Newest first)</option>
                <option value="borrow_asc">Last Borrow (Oldest first)</option>
            </select>
        </div>

        <button type="button" class="btn-reset" onclick="window.resetUserFilters()">Reset</button>
    </div>

    {{-- ── Students Table Section ──────────────────────── --}}
    <div class="card" id="studentsSection">
        <div class="card-header">
            <h3>Student Records</h3>
            <span class="card-badge" id="visibleStudentsCount">{{ $totalStudents }} students</span>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="studentsTable">
                <thead>
                    <tr>
                        <!-- <th style="width:60px;">ID</th> -->
                        <th>Student Number</th>
                        <th>Name</th>
                        <th>Program</th>
                        <th style="width:70px;">Year</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Last Borrowed</th>
                        <th style="text-align:center; width:130px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    @foreach($students as $student)
                    @php
                        $studentLastBorrow = $student->last_borrow_at ?? ($student->usages ? $student->usages->max('time_in') : null);
                        $studentLastBorrowStr = $studentLastBorrow ? \Carbon\Carbon::parse($studentLastBorrow)->format('Y-m-d H:i:s') : '';
                        $studentBorrowDates = $student->usages ? $student->usages->map(function($u) {
                            $t = $u->time_in ?? $u->created_at;
                            return $t ? \Carbon\Carbon::parse($t)->format('Y-m-d') : null;
                        })->filter()->unique()->implode(',') : '';
                        $studentStatus = $student->trashed() ? 'archived' : ($student->status ?? 'active');
                        $programCode = ($programMap ?? [])[$student->program] ?? $student->program;
                    @endphp
                    
                    <tr data-id="{{ $student->id }}"
                        data-name="{{ strtolower($student->first_name . ' ' . $student->last_name) }}"
                        data-number="{{ strtolower($student->student_number) }}"
                        data-email="{{ strtolower($student->email) }}"
                        data-course="{{ strtolower($programCode) }}"
                        data-year="{{ $student->year_level }}"
                        data-archived="{{ $student->trashed() ? 'true' : 'false' }}"
                        data-status="{{ $studentStatus }}"
                        data-last-borrow="{{ $studentLastBorrowStr }}"
                        data-borrow-dates="{{ $studentBorrowDates }}"
                    >
                        <!-- <td>{{ $student->id }}</td> -->
                        <td style="font-weight:600; color:var(--pup-maroon);">{{ $student->student_number }}</td>
                        <td>
                            <span style="font-weight:600; color:#1f2937;">{{ $student->first_name }} {{ $student->last_name }}</span>
                            @if(($student->damage_warning_count ?? 0) > 0)
                                <span style="display:inline-block; margin-left:4px; background:#FEF3C7; color:#92400E; font-size:0.68rem; font-weight:700; padding:2px 7px; border-radius:10px;">⚠ {{ $student->damage_warning_count }} warning(s)</span>
                            @endif
                        </td>
                        <td>{{ $programCode }}</td>
                        <td>{{ $student->year_level }}</td>
                        <td>{{ $student->email }}</td>
                        <td>
                            @if($student->trashed())
                                <span class="status-badge archived">Archived</span>
                            @elseif(($student->status ?? 'active') === 'pending')
                                <span class="status-badge pending">Pending</span>
                            @elseif(($student->status ?? 'active') === 'rejected')
                                <span class="status-badge rejected">Rejected</span>
                            @elseif(($student->status ?? 'active') === 'inactive')
                                <span class="status-badge inactive">Dormant</span>
                            @else
                                <span class="status-badge active">Active</span>
                            @endif
                        </td>
                        <td style="font-size:0.82rem; color:#6b7280;">
                            @php
                                if ($studentLastBorrow) {
                                    $d = \Carbon\Carbon::parse($studentLastBorrow)->diff(now());
                                    if ($d->y >= 1)      echo $d->y . ' yr' . ($d->y > 1 ? 's' : '') . ' ago';
                                    elseif ($d->m >= 1)  echo $d->m . ' mo ago';
                                    elseif ($d->d >= 1)  echo $d->d . ' day' . ($d->d > 1 ? 's' : '') . ' ago';
                                    elseif ($d->h >= 1)  echo $d->h . ' hr' . ($d->h > 1 ? 's' : '') . ' ago';
                                    else                 echo 'Just now';
                                } else {
                                    $age = \Carbon\Carbon::parse($student->created_at)->diff(now());
                                    $ageStr = $age->y >= 1 ? $age->y . ' yr' . ($age->y > 1 ? 's' : '')
                                            : ($age->m >= 1 ? $age->m . ' mo'
                                            : ($age->d >= 1 ? $age->d . ' day' . ($age->d > 1 ? 's' : '') : 'today'));
                                    echo 'Never <span style="color:#bbb;font-size:0.75rem;">· joined ' . $ageStr . ' ago</span>';
                                }
                            @endphp
                        </td>
                        <td style="text-align:center;">
                            <div style="display:inline-flex; gap:5px; flex-wrap:wrap; justify-content:center;">
                                @if(!$student->trashed())
                                    @if(($student->status ?? 'active') !== 'pending')
                                        <form method="POST" action="/students/archive/{{ $student->id }}" onsubmit="return confirm('Archive this student? They can be restored later.');" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="action-btn archive">Archive</button>
                                        </form>
                                    @endif
                                    @if(($student->status ?? 'active') === 'pending')
                                        <form method="POST" action="{{ route('admin.account.approve', $student->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="action-btn approve">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.account.reject', $student->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="action-btn reject" onclick="return confirm('Reject this student?')">Reject</button>
                                        </form>
                                    @endif
                                @else
                                    <span style="color:#9ca3af; font-size:0.8rem;">Archived</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="studentsEmptyState" class="empty-state" style="display:none;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p>No students match your filter or search criteria</p>
        </div>

        <div class="pagination-container" id="studentsPagination" style="display:none;">
            <div class="pagination-info" id="studentsPaginationInfo"></div>
            <div class="pagination-controls">
                <button type="button" class="pagination-btn prev-btn" id="studentsPrevBtn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    <span>Previous</span>
                </button>
                <div class="page-numbers" id="studentsPageNumbers"></div>
                <button type="button" class="pagination-btn next-btn" id="studentsNextBtn">
                    <span>Next</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Faculty Table Section ───────────────────────── --}}
    <div class="card" id="facultySection" style="display:none;">
        <div class="card-header">
            <h3>Faculty Records</h3>
            <span class="card-badge" id="visibleFacultyCount">{{ $totalFaculty }} faculty</span>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="facultyTable">
                <thead>
                    <tr>
                        <th style="width:70px;">Emp ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Last Borrowed</th>
                        <th style="text-align:center; width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="facultyTableBody">
                    @foreach($faculties as $faculty)
                    @php
                        $facultyLastBorrow = $faculty->last_borrow_at ?? ($faculty->bookUsages ? $faculty->bookUsages->max('time_in') : null);
                        $facultyLastBorrowStr = $facultyLastBorrow ? \Carbon\Carbon::parse($facultyLastBorrow)->format('Y-m-d H:i:s') : '';
                        $facultyBorrowDates = $faculty->bookUsages ? $faculty->bookUsages->map(function($u) {
                            $t = $u->time_in ?? $u->created_at;
                            return $t ? \Carbon\Carbon::parse($t)->format('Y-m-d') : null;
                        })->filter()->unique()->implode(',') : '';
                        $facultyStatus = $faculty->trashed() ? 'archived' : ($faculty->status ?? 'active');
                    @endphp
                    <tr data-id="{{ $faculty->id }}"
                        data-name="{{ strtolower($faculty->first_name . ' ' . $faculty->last_name) }}"
                        data-employee="{{ strtolower($faculty->employee_id) }}"
                        data-email="{{ strtolower($faculty->email) }}"
                        data-dept="{{ strtolower($faculty->department ?? '') }}"
                        data-status="{{ $facultyStatus }}"
                        data-archived="{{ $faculty->trashed() ? 'true' : 'false' }}"
                        data-last-borrow="{{ $facultyLastBorrowStr }}"
                        data-borrow-dates="{{ $facultyBorrowDates }}"
                    >
                        <td style="font-weight:600; color:var(--pup-maroon);">{{ $faculty->employee_id }}</td>
                        <td>
                            <span style="font-weight:600; color:#1f2937;">{{ $faculty->first_name }} {{ $faculty->last_name }}</span>
                            @if(($faculty->damage_warning_count ?? 0) > 0)
                                <span style="display:inline-block; margin-left:4px; background:#FEF3C7; color:#92400E; font-size:0.68rem; font-weight:700; padding:2px 7px; border-radius:10px;">⚠ {{ $faculty->damage_warning_count }} warning(s)</span>
                            @endif
                        </td>
                        <td>{{ $faculty->department ?? '—' }}</td>
                        <td>{{ $faculty->email }}</td>
                        <td>
                            @if($faculty->trashed())
                                <span class="status-badge archived">Archived</span>
                            @elseif($faculty->status === 'active')
                                <span class="status-badge active">Active</span>
                            @elseif($faculty->status === 'pending')
                                <span class="status-badge pending">Pending</span>
                            @elseif($faculty->status === 'inactive')
                                <span class="status-badge inactive">Dormant</span>
                            @else
                                <span class="status-badge rejected">Rejected</span>
                            @endif
                        </td>
                        <td style="font-size:0.82rem; color:#6b7280;">
                            @php
                                if ($facultyLastBorrow) {
                                    $d = \Carbon\Carbon::parse($facultyLastBorrow)->diff(now());
                                    if ($d->y >= 1)      echo $d->y . ' yr' . ($d->y > 1 ? 's' : '') . ' ago';
                                    elseif ($d->m >= 1)  echo $d->m . ' mo ago';
                                    elseif ($d->d >= 1)  echo $d->d . ' day' . ($d->d > 1 ? 's' : '') . ' ago';
                                    elseif ($d->h >= 1)  echo $d->h . ' hr' . ($d->h > 1 ? 's' : '') . ' ago';
                                    else                 echo 'Just now';
                                } else {
                                    $age = \Carbon\Carbon::parse($faculty->created_at)->diff(now());
                                    $ageStr = $age->y >= 1 ? $age->y . ' yr' . ($age->y > 1 ? 's' : '')
                                            : ($age->m >= 1 ? $age->m . ' mo'
                                            : ($age->d >= 1 ? $age->d . ' day' . ($age->d > 1 ? 's' : '') : 'today'));
                                    echo 'Never <span style="color:#bbb;font-size:0.75rem;">· joined ' . $ageStr . ' ago</span>';
                                }
                            @endphp
                        </td>
                        <td style="text-align:center;">
                            <div style="display:inline-flex; gap:5px; flex-wrap:wrap; justify-content:center;">
                                @if($faculty->status !== 'pending' && !$faculty->trashed())
                                    <form method="POST" action="{{ route('admin.faculties.destroy', $faculty->id) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" onclick="return confirm('Archive this faculty member?')">Archive</button>
                                    </form>
                                @elseif($faculty->trashed())
                                    <span style="color:#9ca3af; font-size:0.8rem;">Archived</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="facultyEmptyState" class="empty-state" style="display:none;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p>No faculty members match your filter or search criteria</p>
        </div>

        <div class="pagination-container" id="facultyPagination" style="display:none;">
            <div class="pagination-info" id="facultyPaginationInfo"></div>
            <div class="pagination-controls">
                <button type="button" class="pagination-btn prev-btn" id="facultyPrevBtn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    <span>Previous</span>
                </button>
                <div class="page-numbers" id="facultyPageNumbers"></div>
                <button type="button" class="pagination-btn next-btn" id="facultyNextBtn">
                    <span>Next</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
// SPA State container (avoids 'let' re-declaration errors on HTMX swap)
window.UsersState = window.UsersState || {
    activeTab: 'students',
    studentPage: 1,
    studentRowsPerPage: 15,
    facultyPage: 1,
    facultyRowsPerPage: 15
};

window.switchUserType = function(type) {
    window.UsersState.activeTab = type;

    var tabStudents = document.getElementById('tabBtnStudents');
    var tabFaculty = document.getElementById('tabBtnFaculty');
    var sectionStudents = document.getElementById('studentsSection');
    var sectionFaculty = document.getElementById('facultySection');
    var groupProgram = document.getElementById('groupProgramFilter');
    var groupDept = document.getElementById('groupDeptFilter');
    var groupYear = document.getElementById('groupYearFilter');

    if (type === 'students') {
        if (tabStudents) tabStudents.classList.add('active');
        if (tabFaculty) tabFaculty.classList.remove('active');
        if (sectionStudents) sectionStudents.style.display = 'block';
        if (sectionFaculty) sectionFaculty.style.display = 'none';
        if (groupProgram) groupProgram.style.display = 'flex';
        if (groupDept) groupDept.style.display = 'none';
        if (groupYear) groupYear.style.display = 'flex';
        window.applyUserFilters();
    } else {
        if (tabStudents) tabStudents.classList.remove('active');
        if (tabFaculty) tabFaculty.classList.add('active');
        if (sectionStudents) sectionStudents.style.display = 'none';
        if (sectionFaculty) sectionFaculty.style.display = 'block';
        if (groupProgram) groupProgram.style.display = 'none';
        if (groupDept) groupDept.style.display = 'flex';
        if (groupYear) groupYear.style.display = 'none';
        window.applyUserFilters();
    }
};

window.applyUserFilters = function() {
    var type = window.UsersState.activeTab || 'students';
    var search = (document.getElementById('userSearchInput')?.value || '').toLowerCase().trim();
    var status = (document.getElementById('userStatusFilter')?.value || '').toLowerCase().trim();
    var fromDate = (document.getElementById('borrowDateFrom')?.value || '').trim();
    var toDate = (document.getElementById('borrowDateTo')?.value || '').trim();
    var sort = document.getElementById('userSortSelect')?.value || 'name_asc';

    if (type === 'students') {
        var program = (document.getElementById('userProgramFilter')?.value || '').toLowerCase().trim();
        var year = (document.getElementById('userYearFilter')?.value || '').trim();
        var rows = Array.from(document.querySelectorAll('#studentsTableBody tr'));
        var visible = [];

        rows.forEach(function(row) {
            var name = (row.dataset.name || '').toLowerCase();
            var number = (row.dataset.number || '').toLowerCase();
            var email = (row.dataset.email || '').toLowerCase();
            var rowCourse = (row.dataset.course || '').toLowerCase().trim();
            var rowYear = (row.dataset.year || '').trim();
            var rowStatus = (row.dataset.status || '').toLowerCase().trim();
            var isArchived = row.dataset.archived === 'true';

            var matchSearch = !search || name.includes(search) || number.includes(search) || email.includes(search);
            var matchProgram = !program || rowCourse === program;
            var matchYear = !year || rowYear === year;
            var matchStatus = !status || rowStatus === status;

            // Borrow date range check
            var matchDateRange = true;
            if (fromDate || toDate) {
                var datesStr = row.dataset.borrowDates || '';
                if (!datesStr && row.dataset.lastBorrow) {
                    datesStr = row.dataset.lastBorrow.split(' ')[0];
                }
                if (!datesStr) {
                    matchDateRange = false;
                } else {
                    var dates = datesStr.split(',').map(function(d) { return d.trim(); }).filter(Boolean);
                    matchDateRange = dates.some(function(d) {
                        if (fromDate && d < fromDate) return false;
                        if (toDate && d > toDate) return false;
                        return true;
                    });
                }
            }

            if (matchSearch && matchProgram && matchYear && matchStatus && matchDateRange) {
                visible.push(row);
            }
        });

        // Sorting
        visible.sort(function(a, b) {
            var aName = a.dataset.name || '';
            var bName = b.dataset.name || '';
            var aId = parseInt(a.dataset.id) || 0;
            var bId = parseInt(b.dataset.id) || 0;
            var aDate = a.dataset.lastBorrow || '';
            var bDate = b.dataset.lastBorrow || '';

            switch(sort) {
                case 'name_desc': return bName.localeCompare(aName);
                case 'id_asc': return aId - bId;
                case 'id_desc': return bId - aId;
                case 'borrow_desc':
                    if (!aDate && !bDate) return 0;
                    if (!aDate) return 1;
                    if (!bDate) return -1;
                    return bDate.localeCompare(aDate);
                case 'borrow_asc':
                    if (!aDate && !bDate) return 0;
                    if (!aDate) return 1;
                    if (!bDate) return -1;
                    return aDate.localeCompare(bDate);
                case 'name_asc':
                default:
                    return aName.localeCompare(bName);
            }
        });

        // Pagination
        var perPage = window.UsersState.studentRowsPerPage;
        var totalPages = Math.ceil(visible.length / perPage) || 1;
        if (window.UsersState.studentPage > totalPages) window.UsersState.studentPage = totalPages;
        if (window.UsersState.studentPage < 1) window.UsersState.studentPage = 1;
        var start = (window.UsersState.studentPage - 1) * perPage;
        var end = start + perPage;

        var tbody = document.getElementById('studentsTableBody');
        if (tbody) {
            rows.forEach(function(r) { r.style.display = 'none'; });
            visible.slice(start, end).forEach(function(r) {
                r.style.display = '';
                tbody.appendChild(r);
            });
        }

        var badge = document.getElementById('visibleStudentsCount');
        if (badge) badge.textContent = visible.length + ' students';
        var empty = document.getElementById('studentsEmptyState');
        if (empty) empty.style.display = visible.length === 0 ? 'block' : 'none';

        var pagination = document.getElementById('studentsPagination');
        if (pagination) {
            if (totalPages > 1) {
                pagination.style.display = 'flex';
                var pageInfo = document.getElementById('studentsPaginationInfo');
                if (pageInfo) pageInfo.textContent = 'Showing ' + (start + 1) + '–' + Math.min(end, visible.length) + ' of ' + visible.length;
                window.renderUserPageNumbers('studentsPageNumbers', totalPages, window.UsersState.studentPage, function(p) {
                    window.UsersState.studentPage = p;
                    window.applyUserFilters();
                });
                var prevBtn = document.getElementById('studentsPrevBtn');
                if (prevBtn) prevBtn.disabled = window.UsersState.studentPage === 1;
                var nextBtn = document.getElementById('studentsNextBtn');
                if (nextBtn) nextBtn.disabled = window.UsersState.studentPage === totalPages;
            } else {
                pagination.style.display = 'none';
            }
        }
    } else {
        // Faculty filtering
        var dept = (document.getElementById('userDeptFilter')?.value || '').toLowerCase().trim();
        var rows = Array.from(document.querySelectorAll('#facultyTableBody tr'));
        var visible = [];

        rows.forEach(function(row) {
            var name = (row.dataset.name || '').toLowerCase();
            var employee = (row.dataset.employee || '').toLowerCase();
            var email = (row.dataset.email || '').toLowerCase();
            var rowDept = (row.dataset.dept || '').toLowerCase().trim();
            var rowStatus = (row.dataset.status || '').toLowerCase().trim();

            var matchSearch = !search || name.includes(search) || employee.includes(search) || email.includes(search);
            var matchDept = !dept || rowDept === dept;
            var matchStatus = !status || rowStatus === status;

            // Borrow date range check
            var matchDateRange = true;
            if (fromDate || toDate) {
                var datesStr = row.dataset.borrowDates || '';
                if (!datesStr && row.dataset.lastBorrow) {
                    datesStr = row.dataset.lastBorrow.split(' ')[0];
                }
                if (!datesStr) {
                    matchDateRange = false;
                } else {
                    var dates = datesStr.split(',').map(function(d) { return d.trim(); }).filter(Boolean);
                    matchDateRange = dates.some(function(d) {
                        if (fromDate && d < fromDate) return false;
                        if (toDate && d > toDate) return false;
                        return true;
                    });
                }
            }

            if (matchSearch && matchDept && matchStatus && matchDateRange) {
                visible.push(row);
            }
        });

        // Sorting
        visible.sort(function(a, b) {
            var aName = a.dataset.name || '';
            var bName = b.dataset.name || '';
            var aId = parseInt(a.dataset.id) || 0;
            var bId = parseInt(b.dataset.id) || 0;
            var aDate = a.dataset.lastBorrow || '';
            var bDate = b.dataset.lastBorrow || '';

            switch(sort) {
                case 'name_desc': return bName.localeCompare(aName);
                case 'id_asc': return aId - bId;
                case 'id_desc': return bId - aId;
                case 'borrow_desc':
                    if (!aDate && !bDate) return 0;
                    if (!aDate) return 1;
                    if (!bDate) return -1;
                    return bDate.localeCompare(aDate);
                case 'borrow_asc':
                    if (!aDate && !bDate) return 0;
                    if (!aDate) return 1;
                    if (!bDate) return -1;
                    return aDate.localeCompare(bDate);
                case 'name_asc':
                default:
                    return aName.localeCompare(bName);
            }
        });

        // Pagination
        var perPage = window.UsersState.facultyRowsPerPage;
        var totalPages = Math.ceil(visible.length / perPage) || 1;
        if (window.UsersState.facultyPage > totalPages) window.UsersState.facultyPage = totalPages;
        if (window.UsersState.facultyPage < 1) window.UsersState.facultyPage = 1;
        var start = (window.UsersState.facultyPage - 1) * perPage;
        var end = start + perPage;

        var tbody = document.getElementById('facultyTableBody');
        if (tbody) {
            rows.forEach(function(r) { r.style.display = 'none'; });
            visible.slice(start, end).forEach(function(r) {
                r.style.display = '';
                tbody.appendChild(r);
            });
        }

        var badge = document.getElementById('visibleFacultyCount');
        if (badge) badge.textContent = visible.length + ' faculty';
        var empty = document.getElementById('facultyEmptyState');
        if (empty) empty.style.display = visible.length === 0 ? 'block' : 'none';

        var pagination = document.getElementById('facultyPagination');
        if (pagination) {
            if (totalPages > 1) {
                pagination.style.display = 'flex';
                var pageInfo = document.getElementById('facultyPaginationInfo');
                if (pageInfo) pageInfo.textContent = 'Showing ' + (start + 1) + '–' + Math.min(end, visible.length) + ' of ' + visible.length;
                window.renderUserPageNumbers('facultyPageNumbers', totalPages, window.UsersState.facultyPage, function(p) {
                    window.UsersState.facultyPage = p;
                    window.applyUserFilters();
                });
                var prevBtn = document.getElementById('facultyPrevBtn');
                if (prevBtn) prevBtn.disabled = window.UsersState.facultyPage === 1;
                var nextBtn = document.getElementById('facultyNextBtn');
                if (nextBtn) nextBtn.disabled = window.UsersState.facultyPage === totalPages;
            } else {
                pagination.style.display = 'none';
            }
        }
    }
};

window.renderUserPageNumbers = function(containerId, total, current, onClick) {
    var container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';
    for (var i = 1; i <= total; i++) {
        (function(page) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'page-number' + (page === current ? ' active' : '');
            btn.textContent = page;
            btn.onclick = function() { onClick(page); };
            container.appendChild(btn);
        })(i);
    }
};

window.resetUserFilters = function() {
    ['userSearchInput', 'userProgramFilter', 'userDeptFilter', 'userYearFilter', 'userStatusFilter', 'borrowDateFrom', 'borrowDateTo'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    var sortEl = document.getElementById('userSortSelect');
    if (sortEl) sortEl.value = 'name_asc';

    window.UsersState.studentPage = 1;
    window.UsersState.facultyPage = 1;
    window.applyUserFilters();
};

window.initUsersPage = function() {
    var searchInput = document.getElementById('userSearchInput');
    if (!searchInput) return;

    // Instant search on input
    searchInput.oninput = function() {
        window.UsersState.studentPage = 1;
        window.UsersState.facultyPage = 1;
        window.applyUserFilters();
    };

    ['userProgramFilter', 'userDeptFilter', 'userYearFilter', 'userStatusFilter', 'borrowDateFrom', 'borrowDateTo', 'userSortSelect'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.onchange = function() {
                window.UsersState.studentPage = 1;
                window.UsersState.facultyPage = 1;
                window.applyUserFilters();
            };
        }
    });

    var sp = document.getElementById('studentsPrevBtn');
    if (sp) {
        sp.onclick = function() {
            if (window.UsersState.studentPage > 1) {
                window.UsersState.studentPage--;
                window.applyUserFilters();
            }
        };
    }
    var sn = document.getElementById('studentsNextBtn');
    if (sn) {
        sn.onclick = function() {
            window.UsersState.studentPage++;
            window.applyUserFilters();
        };
    }

    var fp = document.getElementById('facultyPrevBtn');
    if (fp) {
        fp.onclick = function() {
            if (window.UsersState.facultyPage > 1) {
                window.UsersState.facultyPage--;
                window.applyUserFilters();
            }
        };
    }
    var fn = document.getElementById('facultyNextBtn');
    if (fn) {
        fn.onclick = function() {
            window.UsersState.facultyPage++;
            window.applyUserFilters();
        };
    }

    // Apply filters immediately for current active tab
    window.switchUserType(window.UsersState.activeTab || 'students');
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initUsersPage);
} else {
    window.initUsersPage();
}
document.addEventListener('htmx:afterSwap', function() {
    if (typeof window.initUsersPage === 'function') {
        window.initUsersPage();
    }
});
</script>
@endsection
