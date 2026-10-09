@extends('layouts.admin')

@section('content')
<div class="users-page">
    <h2>Archived Users</h2>
    <p class="page-subtitle">Soft‑deleted students and faculty. Restore to make them active again.</p>

    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="action-buttons" style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.users') }}" class="btn-outline" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back to Active Users
        </a>
    </div>

    {{-- Archived Students --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="card-header" style="background: var(--pup-maroon); color: white; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: none;">
            <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Archived Students</h4>
            <span class="card-badge" style="background: var(--pup-gold); color: var(--pup-maroon); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">{{ $archivedStudents->count() }} archived</span>
        </div>
        @if($archivedStudents->isEmpty())
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1.5" style="margin-bottom: 1rem;">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <p style="color: var(--text-muted); margin: 0;">No archived students found</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student Number</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Email</th>
                            <th>Deleted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedStudents as $student)
                        <tr>
                            <td style="font-weight: 500;">{{ $student->student_number }}</td>
                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td>{{ $student->program ?? 'N/A' }}</td>
                            <td>{{ $student->email }}</td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">{{ $student->deleted_at ? $student->deleted_at->format('M d, Y') : '-' }}</td>
                            <td>
                                <form method="POST" action="/students/restore/{{ $student->id }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="action-btn restore" onclick="return confirm('Restore this student?')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                                        Restore
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

    {{-- Archived Faculty --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="card-header" style="background: var(--pup-maroon); color: white; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: none;">
            <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Archived Faculty</h4>
            <span class="card-badge" style="background: var(--pup-gold); color: var(--pup-maroon); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">{{ $archivedFaculties->count() }} archived</span>
        </div>
        @if($archivedFaculties->isEmpty())
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1.5" style="margin-bottom: 1rem;">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <p style="color: var(--text-muted); margin: 0;">No archived faculty found</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Deleted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedFaculties as $faculty)
                        <tr>
                            <td style="font-weight: 500;">{{ $faculty->employee_id }}</td>
                            <td>{{ $faculty->first_name }} {{ $faculty->last_name }}</td>
                            <td>{{ $faculty->department ?? 'N/A' }}</td>
                            <td>{{ $faculty->email }}</td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">{{ $faculty->deleted_at ? $faculty->deleted_at->format('M d, Y') : '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.faculties.restore', $faculty->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="action-btn restore" onclick="return confirm('Restore this faculty?')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                                        Restore
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
</div>

<style>
/* Action Buttons Container */
.action-buttons {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Button Outline Style - PUP Theme */
.btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    border: 2px solid var(--pup-maroon);
    border-radius: 8px;
    color: var(--pup-maroon);
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.2s ease;
    background: transparent;
    cursor: pointer;
}

.btn-outline:hover {
    background: var(--pup-maroon);
    color: white;
}

/* Restore Button - PUP Gold themed */
.action-btn.restore {
    background: var(--pup-gold);
    color: var(--pup-maroon);
    border: none;
    padding: 0.45rem 1rem;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.action-btn.restore:hover {
    background: var(--pup-gold-dark);
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(255, 199, 44, 0.4);
}

.action-btn.restore:active {
    transform: translateY(0);
}

/* Empty State */
.empty-state {
    padding: 3rem 1.5rem;
    text-align: center;
}

.empty-state p {
    color: var(--text-muted);
    font-size: 0.9375rem;
    margin: 0;
}

/* Table Styling */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead th {
    background: #f9fafb;
    color: var(--text);
    padding: 0.875rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid var(--border);
}

.data-table tbody td {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid var(--border);
    font-size: 0.875rem;
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

.data-table tbody tr:hover {
    background: #f9fafb;
}

/* Card Badge */
.card-badge {
    background: var(--pup-gold);
    color: var(--pup-maroon);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
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
    }
    
    .data-table {
        display: block;
        overflow-x: auto;
    }
}
</style>
@endsection