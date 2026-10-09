@extends('layouts.faculty')

@section('title', 'Acquisition Requests')

@push('styles')
<style>
    .req-page { max-width: 960px; margin: 0 auto; }

    .req-page-header {
        display: flex; align-items: flex-start; justify-content: space-between;
        flex-wrap: wrap; gap: 12px; margin-bottom: 24px;
    }
    .req-page-title { border-left: 4px solid #FFC72C; padding-left: 14px; }
    .req-page-title h1 { font-size: 1.55rem; font-weight: 800; color: #800000; margin: 0 0 3px; }
    .req-page-title p  { font-size: 0.83rem; color: #666; margin: 0; }

    .btn-gold {
        display: inline-flex; align-items: center; gap: 7px;
        background: #FFC72C; color: #800000;
        font-family: 'Poppins', sans-serif; font-size: 0.85rem; font-weight: 700;
        padding: 10px 20px; border-radius: 10px; border: none;
        text-decoration: none; cursor: pointer; transition: all 0.2s;
    }
    .btn-gold:hover { background: #e8b800; transform: translateY(-1px); }

    .flash-success {
        background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981;
        padding: 12px 16px; border-radius: 10px; margin-bottom: 18px;
        font-size: 0.88rem; font-weight: 600;
    }

    .req-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
    .req-card-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 22px;
        background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
        border-bottom: 3px solid #FFC72C;
    }
    .req-card-head h3 { font-size: 0.95rem; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px; }
    .req-card-head .count-badge { background: rgba(255,199,44,0.25); color: #FFC72C; font-size: 0.73rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }

    .req-table { width: 100%; border-collapse: collapse; font-size: 0.86rem; }
    .req-table th { text-align: left; padding: 10px 16px; background: #6a0000; color: #fff; font-size: 0.73rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .req-table td { padding: 13px 16px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
    .req-table tbody tr:last-child td { border-bottom: none; }
    .req-table tbody tr:hover td { background: #fffbf0; }
    .book-title { font-weight: 600; color: #1a1a1a; }
    .book-meta  { font-size: 0.75rem; color: #888; margin-top: 2px; }
    .justification-cell { font-size: 0.82rem; color: #555; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .admin-note { background: #fefce8; padding: 7px 10px; border-radius: 8px; font-size: 0.78rem; color: #92400e; border-left: 3px solid #f59e0b; }

    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.73rem; font-weight: 700; white-space: nowrap; }
    .s-submitted { background: rgba(255,199,44,0.2); color: #7a4700; }
    .s-approved  { background: #ecfdf5; color: #065f46; }
    .s-rejected  { background: rgba(128,0,0,0.09); color: #800000; }
    .s-ordered   { background: #dbeafe; color: #1e40af; }
    .s-received  { background: #ede9fe; color: #5b21b6; }
    .s-default   { background: #f3f4f6; color: #374151; }

    .empty-state { text-align: center; padding: 52px 24px; color: #9ca3af; }
    .empty-state svg { margin: 0 auto 14px; display: block; opacity: 0.3; }
    .empty-state p { margin: 0 0 16px; font-size: 0.9rem; }

    .pag-wrap { padding: 14px 20px; border-top: 1px solid #f0f0f0; }
    .pag-wrap .pagination { display: flex; flex-wrap: wrap; gap: 6px; list-style: none; padding: 0; margin: 0; justify-content: center; }
    .pag-wrap .page-item { display: inline-block; }
    .pag-wrap .page-link {
        display: flex; align-items: center; justify-content: center;
        min-width: 36px; height: 36px; padding: 0 10px;
        background: #fff; border: 1px solid #e0e0e0; border-radius: 8px;
        color: #555; font-size: 0.8rem; font-weight: 500; text-decoration: none; transition: all 0.2s;
    }
    .pag-wrap .page-link:hover { background: #FFC72C; border-color: #FFC72C; color: #800000; }
    .pag-wrap .active .page-link { background: #800000; border-color: #800000; color: #fff; }
    .pag-wrap .disabled .page-link { opacity: 0.45; pointer-events: none; }
</style>
@endpush

@section('content')
<div class="req-page">

    <div class="req-page-header">
        <div class="req-page-title">
            <h1>Acquisition Requests</h1>
            <p>Request new books for the library collection and track their status.</p>
        </div>
        <a href="{{ route('faculty.requisitions.create') }}" class="btn-gold">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Request
        </a>
    </div>

    @if(session('success'))
        <div class="flash-success">{{ session('success') }}</div>
    @endif

    <div class="req-card">
        <div class="req-card-head">
            <h3>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#FFC72C" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Your Requests
            </h3>
            <span class="count-badge">{{ $requisitions->total() }} total</span>
        </div>

        @if($requisitions->isEmpty())
            <div class="empty-state">
                <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <p>No requests yet.</p>
                <a href="{{ route('faculty.requisitions.create') }}" class="btn-gold" style="display:inline-flex;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Submit Your First Request
                </a>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="req-table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Justification</th>
                            <th>Admin Notes</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requisitions as $req)
                        @php
                            $sMap = [
                                'submitted' => ['s-submitted','Submitted'],
                                'approved'  => ['s-approved','Approved'],
                                'granted'   => ['s-approved','Granted'],
                                'rejected'  => ['s-rejected','Rejected'],
                                'ordered'   => ['s-ordered','Ordered'],
                                'received'  => ['s-received','Received'],
                            ];
                            [$sCls, $sLabel] = $sMap[$req->status] ?? ['s-default', ucfirst($req->status)];
                        @endphp
                        <tr>
                            <td>
                                <div class="book-title">{{ $req->title }}</div>
                                @if($req->author)
                                    <div class="book-meta">{{ $req->author }}{{ $req->publication_year ? ' · ' . $req->publication_year : '' }}</div>
                                @endif
                            </td>
                            <td><div class="justification-cell" title="{{ $req->justification ?? '' }}">{{ $req->justification ?? '—' }}</div></td>
                            <td>
                                @if($req->admin_notes)
                                    <div class="admin-note">{{ $req->admin_notes }}</div>
                                @else
                                    <span style="color:#bbb;font-size:0.78rem;">—</span>
                                @endif
                            </td>
                            <td><span class="status-badge {{ $sCls }}">{{ $sLabel }}</span></td>
                            <td style="white-space:nowrap;font-size:0.82rem;color:#666;">{{ $req->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($requisitions->hasPages())
                <div class="pag-wrap">
                    {{ $requisitions->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
