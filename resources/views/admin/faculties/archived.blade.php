@extends('layouts.admin')

@section('content')
<div class="faculty-list-page">
    <h2>Archived Faculty</h2>
    <p class="page-subtitle">Soft‑deleted faculty records. Restore to make them active again.</p>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="action-buttons">
        <a href="{{ route('admin.faculties.index') }}" class="btn-outline">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back to Active Faculty
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Archived Faculty Records</h4>
            <span class="card-badge">{{ $faculties->count() }} archived</span>
        </div>
        @if($faculties->isEmpty())
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <p>No archived faculty found</p>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($faculties as $faculty)
                        <tr>
                            <td>{{ $faculty->employee_id }}</td>
                            <td>{{ $faculty->first_name }} {{ $faculty->last_name }}</td>
                            <td>{{ $faculty->department ?? 'N/A' }}</td>
                            <td>{{ $faculty->email }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.faculties.restore', $faculty->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="action-btn restore" onclick="return confirm('Restore this faculty?')">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
    Restore
</button>
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
    .faculty-list-page { /* reuse styles from index page, but could be omitted since it's included */ }
    .action-btn.restore { background: #16a34a; color: white; border: none; padding: 0.35rem 0.8rem; border-radius: 6px; cursor: pointer; }
    .action-btn.restore:hover { background: #15803d; }
</style>
@endsection