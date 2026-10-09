{{-- resources/views/faculty/dashboard.blade.php --}}
@extends('layouts.faculty')

@section('title', 'Faculty Dashboard')

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

    /* ── WELCOME BANNER ── */
    .welcome-banner {
        background: var(--maroon);
        border-radius: 12px;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 22px;
        box-shadow: 0 4px 18px rgba(128,0,0,0.18);
    }
    .welcome-icon-box {
        width: 50px; height: 50px;
        border-radius: 12px;
        background: rgba(255,255,255,0.13);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .welcome-info h4 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 3px;
    }
    .welcome-info .w-department {
        font-size: 0.77rem;
        color: rgba(255,255,255,0.65);
        margin-bottom: 5px;
    }
    .welcome-info .w-eid {
        display: inline-block;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        font-size: 0.71rem;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
        letter-spacing: 0.4px;
    }

    /* ── SECTION CARD ── */
    .s-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .s-card-head {
        padding: 14px 20px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--maroon);
    }
    .s-card-head svg { flex-shrink: 0; }
    .s-card-body { padding: 20px; }
    .s-card-foot {
        padding: 11px 20px;
        border-top: 1px solid #f0f0f0;
        background: #fafafa;
    }
    .s-card-foot a {
        font-size: 0.81rem;
        font-weight: 600;
        color: var(--maroon);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: gap 0.15s;
    }
    .s-card-foot a:hover { gap: 8px; }

    /* Recommendation cards */
    .rec-scroll {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        padding: 4px 2px 8px;
        scrollbar-width: thin;
    }
    .rec-card {
        min-width: 160px;
        max-width: 180px;
        background: #fdf8f8;
        border: 1px solid #eee;
        border-radius: 10px;
        padding: 14px 12px 12px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .rec-card-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #222;
        line-height: 1.3;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .rec-card-author { font-size: 0.71rem; color: #888; }
    .rec-card-reason { font-size: 0.67rem; color: #aaa; font-style: italic; margin-top: 2px; }

    /* ── POPULAR LIST ── */
    .popular-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .popular-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 13px 20px;
        border-bottom: 1px solid #f4f4f4;
        gap: 12px;
        transition: background 0.15s;
    }
    .popular-item:last-child { border-bottom: none; }
    .popular-item:hover { background: rgba(128,0,0,0.02); }
    .popular-info {
        flex: 1;
        min-width: 0;
    }
    .popular-title {
        font-size: 0.86rem;
        font-weight: 600;
        color: #222;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 3px;
    }
    .popular-author {
        font-size: 0.72rem;
        color: #aaa;
    }
    .borrow-count {
        background: rgba(128,0,0,0.08);
        color: var(--maroon);
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* ── HISTORY LIST ── */
    .history-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .history-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 13px 20px;
        border-bottom: 1px solid #f4f4f4;
        gap: 12px;
        transition: background 0.15s;
    }
    .history-item:last-child { border-bottom: none; }
    .history-item:hover { background: rgba(128,0,0,0.02); }
    .history-info {
        flex: 1;
        min-width: 0;
    }
    .history-title {
        font-size: 0.86rem;
        font-weight: 600;
        color: #222;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 3px;
    }
    .history-date {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.72rem;
        color: #aaa;
    }
    .badge-returned {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #edf7ed;
        color: #2e7d32;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* ── EMPTY STATE ── */
    .empty-state {
        text-align: center;
        padding: 42px 20px;
    }
    .empty-icon {
        width: 60px; height: 60px;
        border-radius: 14px;
        background: rgba(128,0,0,0.06);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
    }
    .empty-state h5 {
        font-size: 0.94rem;
        font-weight: 700;
        color: var(--maroon);
        margin-bottom: 6px;
    }
    .empty-state p {
        font-size: 0.81rem;
        color: #999;
        margin-bottom: 0;
    }

    /* ── MOBILE POLISH ── */
    .welcome-info { min-width: 0; flex: 1; }
    .welcome-info h4, .welcome-info .w-course, .welcome-info .w-department { overflow-wrap: anywhere; }
    .rec-scroll { scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; overscroll-behavior-x: contain; }
    .rec-card { scroll-snap-align: start; }
    @media (max-width: 768px) {
        .welcome-banner {
            flex-direction: column;
            align-items: flex-start;
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            border-bottom: 3px solid #FFC72C;
        }
        .s-card-body { padding: 14px; }
        .rec-scroll { gap: 10px; margin: 0 -14px; padding: 2px 14px 10px; scroll-padding-left: 14px; }
        .rec-card { min-width: min(62vw, 210px); max-width: min(62vw, 210px); }
        .history-item, .popular-item { flex-wrap: wrap; }
        .h-title, .history-title, .popular-title { white-space: normal; }
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <h1>Faculty Dashboard</h1>
    <p>Welcome back — here's your library overview.</p>
</div>

{{-- Welcome Banner --}}
<div class="welcome-banner">
    <div class="welcome-icon-box">
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
    </div>
    <div class="welcome-info">
        <h4>Welcome back, {{ Auth::guard('faculty')->user()->first_name }}!</h4>
        <div class="w-department">{{ Auth::guard('faculty')->user()->department ?? 'Faculty Member' }}</div>
        <span class="w-eid">ID: {{ Auth::guard('faculty')->user()->employee_id }}</span>
    </div>
</div>

{{-- AI Recommendations --}}
@if(!empty($recommendations))
<div class="s-card">
    <div class="s-card-head">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2a10 10 0 0 1 10 10c0 5-3 8-6 8l-4 2-4-2c-3 0-6-3-6-8a10 10 0 0 1 10-10z"/>
            <path d="M9 12h.01"/><path d="M15 12h.01"/>
            <path d="M12 16c-1 0-1.5-.5-2-1"/>
        </svg>
        Recommended for You
    </div>
    <div class="s-card-body">
        <div class="rec-scroll">
            @foreach($recommendations as $rec)
            <div class="rec-card">
                <div class="rec-card-title">{{ $rec['title'] }}</div>
                <div class="rec-card-author">by {{ $rec['author'] ?? 'Unknown' }}</div>
                <div class="rec-card-reason">✨ {{ $rec['reason'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Popular Among Faculty --}}
<div class="s-card">
    <div class="s-card-head">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Popular Among Faculty
    </div>
    <div>
        @if(!empty($facultyPopularBooks) && $facultyPopularBooks->count())
            <ul class="popular-list">
                @foreach($facultyPopularBooks as $book)
                <li class="popular-item">
                    <div class="popular-info">
                        <div class="popular-title">{{ $book->title }}</div>
                        <div class="popular-author">by {{ $book->author ?? 'Unknown' }}</div>
                    </div>
                    <span class="borrow-count">{{ $book->total }} borrows</span>
                </li>
                @endforeach
            </ul>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#800000" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h5>No Data Yet</h5>
                <p>Popular books will appear here as borrowing data accumulates.</p>
            </div>
        @endif
    </div>
</div>

{{-- Recent Returns --}}
<div class="s-card">
    <div class="s-card-head">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Recent Returns
    </div>
    <div>
        @if($recentHistory->count())
            <ul class="history-list">
                @foreach($recentHistory as $h)
                <li class="history-item">
                    <div class="history-info">
                        <div class="history-title">{{ $h->book->title ?? 'Unknown Book' }}</div>
                        <div class="history-date">
                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Returned: {{ $h->time_out->format('M d, Y') }}
                        </div>
                    </div>
                    <span class="badge-returned">
                        <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Returned
                    </span>
                </li>
                @endforeach
            </ul>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#800000" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h5>No Return History</h5>
                <p>Your recent returns will appear here.</p>
            </div>
        @endif
    </div>
    @if($recentHistory->count())
    <div class="s-card-foot">
        <a href="{{ route('faculty.history') }}">
            View Full History
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    @endif
</div>

@endsection

