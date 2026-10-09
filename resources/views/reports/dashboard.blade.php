@extends('layouts.admin')

@section('content')

@php
    $currentPeriod = $period ?? 'all';
    $exportParams = ['period' => $currentPeriod];
    if (!empty($startDate)) $exportParams['start_date'] = $startDate;
    if (!empty($endDate)) $exportParams['end_date'] = $endDate;
@endphp

<div class="dashboard-page">
    {{-- Top Header & Unified Reports Actions --}}
    <div class="dash-header">
        <div class="dash-title-group">
            <h1 class="dash-title">Library Reports Hub</h1>
            <p class="dash-subtitle">Consolidated operational intelligence &amp; export center for: <strong>{{ $periodLabel ?? 'All Time' }}</strong></p>
        </div>

        <div class="dash-actions">
            {{-- Unified Report Export Dropdown (PUP Themed) --}}
            <div class="report-dropdown-wrapper" id="reportDropdownWrapper">
                <button type="button" class="btn-export-reports" onclick="toggleReportDropdown(event)">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#FFC72C" stroke-width="2.2">
                        <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Export Reports</span>
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#FFC72C" stroke-width="2.5"><path d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div class="report-dropdown-menu" id="reportDropdownMenu">
                    <div class="dropdown-header">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#FFC72C" stroke-width="2.2"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span style="color:#FFC72C; font-weight:800; font-size:0.9rem;">Select Report &amp; Format</span>
                        </div>
                        <small style="color:rgba(255,255,255,0.8); font-size:0.75rem;">Period: {{ $periodLabel ?? 'All Time' }}</small>
                    </div>

                    <div class="dropdown-category">Core Statistics</div>
                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>General Statistics</strong>
                            <small>Summary KPIs, course usage &amp; distribution</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.general', $exportParams) }}" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'general', 'format' => 'csv'])) }}" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'general', 'format' => 'xlsx'])) }}" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'general', 'format' => 'json'])) }}" class="fmt-btn">JSON</a>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>Circulation Report</strong>
                            <small>Most &amp; least borrowed catalog volumes</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.circulation', $exportParams) }}" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'circulation', 'format' => 'csv'])) }}" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'circulation', 'format' => 'xlsx'])) }}" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'circulation', 'format' => 'json'])) }}" class="fmt-btn">JSON</a>
                        </div>
                    </div>

                    <div class="dropdown-category">Patron Activity</div>
                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>Student Borrowing</strong>
                            <small>Student checkouts and course histories</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.students', $exportParams) }}" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'students', 'format' => 'csv'])) }}" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'students', 'format' => 'xlsx'])) }}" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'students', 'format' => 'json'])) }}" class="fmt-btn">JSON</a>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>Faculty Borrowing</strong>
                            <small>Faculty checkouts and department metrics</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.faculty', $exportParams) }}" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'faculty', 'format' => 'csv'])) }}" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'faculty', 'format' => 'xlsx'])) }}" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'faculty', 'format' => 'json'])) }}" class="fmt-btn">JSON</a>
                        </div>
                    </div>

                    <div class="dropdown-category">Catalog &amp; Collections</div>
                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>Acquired Books</strong>
                            <small>Accession timeline and donations</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.acquired', $exportParams) }}" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'acquired', 'format' => 'csv'])) }}" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'acquired', 'format' => 'xlsx'])) }}" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'acquired', 'format' => 'json'])) }}" class="fmt-btn">JSON</a>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>Condemned Books</strong>
                            <small>Deaccessioned and damaged volumes</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.condemned', $exportParams) }}" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'condemned', 'format' => 'csv'])) }}" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'condemned', 'format' => 'xlsx'])) }}" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'condemned', 'format' => 'json'])) }}" class="fmt-btn">JSON</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Period Filter Bar --}}
    <div class="period-filter-bar">
        <div class="period-filter">
            <span class="filter-caption">Presets:</span>
            <a href="{{ url('/reports?period=today') }}" class="period-btn {{ $currentPeriod === 'today' ? 'active' : '' }}">Today</a>
            <a href="{{ url('/reports?period=week') }}" class="period-btn {{ $currentPeriod === 'week' ? 'active' : '' }}">Past Week</a>
            <a href="{{ url('/reports?period=month') }}" class="period-btn {{ $currentPeriod === 'month' ? 'active' : '' }}">Past Month</a>
            <a href="{{ url('/reports?period=quarter') }}" class="period-btn {{ $currentPeriod === 'quarter' ? 'active' : '' }}">Past Quarter</a>
            <a href="{{ url('/reports?period=half_year') }}" class="period-btn {{ $currentPeriod === 'half_year' ? 'active' : '' }}">Half a Year</a>
            <a href="{{ url('/reports?period=year') }}" class="period-btn {{ $currentPeriod === 'year' ? 'active' : '' }}">Past Year</a>
            <a href="{{ url('/reports?period=all') }}" class="period-btn {{ $currentPeriod === 'all' ? 'active' : '' }}">All Time</a>
        </div>

        <form method="GET" action="{{ url('/reports') }}" class="custom-range-form">
            <span class="filter-caption">Custom:</span>
            <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="custom-range-input" title="Start date" required>
            <span style="color:#9ca3af; font-size:0.8rem;">to</span>
            <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="custom-range-input" title="End date" required>
            <button type="submit" class="custom-range-btn">Filter Range</button>
            @if(!empty($startDate) || !empty($endDate))
                <a href="{{ url('/reports?period=all') }}" class="reset-link">Reset</a>
            @endif
        </form>
    </div>

    {{-- Balanced Metric Grid --}}
    <div class="stats-grid" style="margin-bottom:20px;">
        <div class="card stat-card" style="border-left:4px solid var(--pup-maroon);">
            <div class="stat-info">
                <h4>Borrows This Month</h4>
                <p class="stat-number">{{ number_format($monthBorrows ?? 0) }}</p>
                <span class="stat-badge" style="background:#ecfdf5; color:#059669;">Current Month</span>
            </div>
        </div>

        <div class="card stat-card" style="border-left:4px solid #3b82f6;">
            <div class="stat-info">
                <h4>Active Users</h4>
                <p class="stat-number">{{ number_format($activeUsers ?? 0) }}</p>
                <span class="stat-badge" style="background:#eef2ff; color:#4f46e5;">In Selected Period</span>
            </div>
        </div>

        <div class="card stat-card" style="border-left:4px solid #10b981;">
            <div class="stat-info">
                <h4>Today's Borrows</h4>
                <p class="stat-number">{{ number_format($dailyBorrows ?? 0) }}</p>
                <span class="stat-meta">Avg/Day: {{ round($avgDailyBorrows ?? 0, 1) }}</span>
            </div>
        </div>

        <div class="card stat-card" style="border-left:4px solid #06b6d4;">
            <div class="stat-info">
                <h4>Currently On Loan</h4>
                <p class="stat-number">{{ number_format($currentlyOnLoan ?? 0) }}</p>
                <span class="stat-badge" style="background:#ecfeff; color:#0891b2;">Active</span>
            </div>
        </div>

        <div class="card stat-card" style="border-left:4px solid #14b8a6;">
            <div class="stat-info">
                <h4>Total Returns</h4>
                <p class="stat-number">{{ number_format($totalReturns ?? 0) }}</p>
                <span class="stat-badge" style="background:#f0fdfa; color:#0f766e;">Completed</span>
            </div>
        </div>

        <div class="card stat-card" style="border-left:4px solid #f59e0b;">
            <div class="stat-info">
                <h4>Never Borrowed</h4>
                <p class="stat-number" style="color:#b45309;">{{ number_format($neverBorrowedCount ?? 0) }}</p>
                <span class="stat-badge" style="background:#fef3c7; color:#b45309;">Needs Review</span>
            </div>
        </div>
    </div>

    {{-- Two Column Reports Grid --}}
    <div class="dashboard-two-col" style="padding:0; margin-bottom:20px;">
        {{-- Left: Most Borrowed Books & Subjects --}}
        <div class="dashboard-col">
            <div class="card" style="margin-bottom:20px;">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700;">Most Borrowed Books</h4>
                    <span style="font-size:0.75rem; color:#6b7280;">{{ $mostBorrowedBooks->count() }} titles</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="report-table">
                        <thead><tr><th>Book Title</th><th style="text-align:right;">Borrows</th></tr></thead>
                        <tbody>
                            @forelse($mostBorrowedBooks as $book)
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:#111;">{{ $book->book->title ?? '—' }}</div>
                                    <div style="font-size:0.75rem; color:#6b7280;">{{ $book->book->author ?? '' }}</div>
                                </td>
                                <td style="text-align:right; font-weight:700; color:#800000;">{{ $book->total }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" style="text-align:center; color:#9ca3af; padding:20px;">No borrow data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700;">Top Borrowed Subjects</h4>
                    <span style="font-size:0.75rem; color:#6b7280;">{{ $topSubjects->count() }} subjects</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="report-table">
                        <thead><tr><th>Subject</th><th style="text-align:right;">Borrows</th></tr></thead>
                        <tbody>
                            @forelse($topSubjects as $s)
                            <tr>
                                <td style="font-weight:600; color:#374151;">{{ $s->subject }}</td>
                                <td style="text-align:right; font-weight:700; color:#800000;">{{ $s->total }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" style="text-align:center; color:#9ca3af; padding:20px;">No subject data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right: Program Usage & New Acquisitions --}}
        <div class="dashboard-col">
            <div class="card" style="margin-bottom:20px;">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700;">Program Usage</h4>
                    <form method="GET" action="{{ url('/reports') }}" style="display:flex; align-items:center; gap:6px;">
                        <input type="hidden" name="period" value="{{ $currentPeriod }}">
                        <select name="program_filter" onchange="this.form.submit()" class="program-select">
                            <option value="">All Programs</option>
                            @foreach($allCourses as $c)
                                <option value="{{ $c->name }}" {{ ($programFilter ?? '') === $c->name ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div style="overflow-x:auto;">
                    <table class="report-table">
                        <thead><tr><th>Program</th><th style="text-align:right;">Total Borrows</th></tr></thead>
                        <tbody>
                            @forelse($programUsage as $p)
                            <tr>
                                <td style="font-weight:600; color:#374151;">{{ $p->program }}</td>
                                <td style="text-align:right; font-weight:700; color:#800000;">{{ $p->total }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" style="text-align:center; color:#9ca3af; padding:20px;">No records</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700;">New Acquisitions</h4>
                    <span style="font-size:0.75rem; color:#6b7280;">{{ $newAcquisitions->count() }} books</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="report-table">
                        <thead><tr><th>Title</th><th>Author</th><th>Date Added</th></tr></thead>
                        <tbody>
                            @forelse($newAcquisitions as $book)
                            <tr>
                                <td style="font-weight:600; color:#111;">{{ $book->title }}</td>
                                <td style="font-size:0.8rem; color:#6b7280;">{{ $book->author ?? '—' }}</td>
                                <td style="white-space:nowrap; font-size:0.78rem; color:#6b7280;">{{ $book->created_at ? $book->created_at->format('Y-m-d') : '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding:20px;">No records</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-page { max-width: 1400px; margin: 0 auto; }
.dash-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 16px; }
.dash-title { font-size: 1.55rem; font-weight: 800; color: #800000; margin: 0 0 4px; }
.dash-subtitle { font-size: 0.85rem; color: #6b7280; margin: 0; }
.dash-actions { display: flex; align-items: center; gap: 10px; }

.btn-export-reports {
    min-height: 44px; padding: 9px 18px; background: #800000; color: #FFC72C; border: none; border-radius: 8px;
    font-size: 0.88rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
    box-shadow: 0 2px 4px rgba(128,0,0,0.25);
    transition: background 0.15s, transform 0.15s;
}
.btn-export-reports:hover { background: #5a0000; transform: translateY(-1px); }
.report-dropdown-menu {
    position: absolute; top: calc(100% + 8px); right: 0; width: 400px; background: #fff; border: 1px solid #e5e7eb;
    border-radius: 12px; box-shadow: 0 14px 30px -5px rgba(0,0,0,0.2); z-index: 1050; display: none; overflow: hidden; max-height: 520px; overflow-y: auto;
}
.report-dropdown-menu.show { display: block; }
.dropdown-header { background: #800000; color: #fff; padding: 12px 18px; border-bottom: 2px solid #FFC72C; display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; font-weight: 700; }
.dropdown-category { padding: 10px 18px 4px; font-size: 0.72rem; font-weight: 800; color: #800000; text-transform: uppercase; background: #fafafa; border-bottom: 1px solid #f0f0f0; }
.dropdown-item { padding: 10px 18px; white-space: normal; width: auto; display: flex; justify-content: space-between; align-items: center; gap: 12px; border-bottom: 1px solid #f5f5f5; }
.dropdown-item:hover { background: #fdf8f8; }
.dropdown-item .item-title { flex: 1; min-width: 0; }
.dropdown-item .item-title strong { display: block; font-size: 0.84rem; color: #111827; font-weight: 700; }
.dropdown-item .item-title small { display: block; font-size: 0.72rem; color: #6b7280; margin-top: 2px; }
.dropdown-item .item-formats { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
.fmt-btn { padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; text-decoration: none; min-width: 32px; text-align: center; background: #ffffff; color: #800000; border: 1px solid #800000; transition: all 0.15s ease; }
.fmt-btn:hover { background: #800000; color: #FFC72C; border-color: #800000; transform: translateY(-1px); }

.period-filter-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 16px; }
.filter-caption { font-size: 0.74rem; font-weight: 700; color: #6b7280; text-transform: uppercase; }
.period-filter { display: flex; gap: 5px; flex-wrap: wrap; align-items: center; }
.period-btn { padding: 5px 12px; border-radius: 20px; text-decoration: none; font-size: 0.78rem; font-weight: 600; color: #800000; border: 1px solid #e5e7eb; background: #fff; min-height: 32px; display: inline-flex; align-items: center; }
.period-btn.active { background: #800000; color: #FFC72C; border-color: #800000; }
.custom-range-form { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.custom-range-input { padding: 5px 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.78rem; min-height: 32px; }
.custom-range-btn { padding: 5px 12px; background: #800000; color: #fff; border: none; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer; min-height: 32px; }
.reset-link { font-size: 0.78rem; color: #800000; text-decoration: none; font-weight: 700; }

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
.stat-card { background: #fff; border-radius: 10px; padding: 14px 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f0f0f0; }
.stat-info h4 { margin: 0; font-size: 0.8rem; font-weight: 700; color: #4b5563; text-transform: uppercase; }
.stat-number { font-size: 1.55rem; font-weight: 800; color: #111827; margin: 6px 0 4px; line-height: 1.1; }
.stat-badge { font-size: 0.72rem; font-weight: 700; padding: 2px 7px; border-radius: 10px; display: inline-block; }
.stat-meta { font-size: 0.72rem; color: #6b7280; font-weight: 600; }

.dashboard-two-col { display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 16px; }
.dashboard-col { min-width: 0; }
.report-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.report-table th { padding: 8px 14px; background: #800000; color: #fff; font-size: 0.74rem; font-weight: 700; text-transform: uppercase; text-align: left; }
.report-table td { padding: 8px 14px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.report-table tbody tr:hover { background: #fafafa; }
.program-select { padding: 4px 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.76rem; min-height: 32px; }
</style>

<script>
function toggleReportDropdown(event) {
    event.stopPropagation();
    const menu = document.getElementById('reportDropdownMenu');
    menu.classList.toggle('show');
}
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('reportDropdownWrapper');
    const menu = document.getElementById('reportDropdownMenu');
    if (wrapper && !wrapper.contains(e.target) && menu) {
        menu.classList.remove('show');
    }
});
</script>

@endsection
