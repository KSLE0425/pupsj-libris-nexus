@extends('layouts.admin')

@section('content')

@php
    $currentPeriod = $periodKey ?? ($period ?? 'all');
    $exportParams = ['period' => $currentPeriod];
    if (!empty($startDate)) $exportParams['start_date'] = $startDate;
    if (!empty($endDate)) $exportParams['end_date'] = $endDate;
@endphp

<div class="analytics-page">
    {{-- ═══════════════════ TOP HEADER & ACTIONS ═══════════════════ --}}
    <div class="dash-header">
        <div class="dash-title-group">
            <h1 class="dash-title">Library Analytics &amp; Strategic Insights</h1>
            <p class="dash-subtitle">
                Historical patterns, borrowing trends, and collection intelligence for: <strong>{{ $periodLabel ?? 'All Time' }}</strong>
            </p>
        </div>

        <div class="dash-actions">
            {{-- Quick Dashboard Link --}}
            <a href="{{ route('admin.dashboard') }}" class="btn-action-outline" title="Return to Operational Dashboard">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span>Operational Dashboard</span>
            </a>

            {{-- EXPORT DROPDOWN --}}
            <div class="report-dropdown-wrapper" id="analyticsExportWrapper">
                <button type="button" class="btn-report-dropdown" id="analyticsExportBtn" onclick="toggleAnalyticsExport(event)">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Export Analytics Data</span>
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div class="report-dropdown-menu" id="analyticsExportMenu">
                    <div class="dropdown-header">
                        <span>Select Output Format</span>
                        <small style="color:#6b7280; font-weight:normal;">Period: {{ $periodLabel ?? 'All Time' }}</small>
                    </div>
                    <div class="dropdown-item">
                        <div class="item-title">
                            <span class="item-icon">🔄</span>
                            <div>
                                <strong>Circulation &amp; Trends</strong>
                                <small>Full circulation analytics</small>
                            </div>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.circulation', $exportParams) }}" class="fmt-btn fmt-pdf">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'circulation', 'format' => 'csv'])) }}" class="fmt-btn fmt-csv">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'circulation', 'format' => 'xlsx'])) }}" class="fmt-btn fmt-xlsx">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'circulation', 'format' => 'json'])) }}" class="fmt-btn fmt-json">JSON</a>
                        </div>
                    </div>
                    <div class="dropdown-item">
                        <div class="item-title">
                            <span class="item-icon">📊</span>
                            <div>
                                <strong>General Statistics</strong>
                                <small>Summary &amp; course distribution</small>
                            </div>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.general', $exportParams) }}" class="fmt-btn fmt-pdf">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'general', 'format' => 'csv'])) }}" class="fmt-btn fmt-csv">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'general', 'format' => 'xlsx'])) }}" class="fmt-btn fmt-xlsx">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'general', 'format' => 'json'])) }}" class="fmt-btn fmt-json">JSON</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ PERIOD FILTER BAR ═══════════════════ --}}
    <div class="period-filter-bar">
        <div class="period-filter">
            <span class="filter-caption">Analysis Window:</span>
            <a href="{{ url('/admin/analytics?period=today') }}" class="period-btn {{ $currentPeriod === 'today' || $currentPeriod === 'day' ? 'active' : '' }}">Today</a>
            <a href="{{ url('/admin/analytics?period=week') }}" class="period-btn {{ $currentPeriod === 'week' ? 'active' : '' }}">Past Week</a>
            <a href="{{ url('/admin/analytics?period=month') }}" class="period-btn {{ $currentPeriod === 'month' ? 'active' : '' }}">Past Month</a>
            <a href="{{ url('/admin/analytics?period=quarter') }}" class="period-btn {{ $currentPeriod === 'quarter' ? 'active' : '' }}">Past Quarter</a>
            <a href="{{ url('/admin/analytics?period=half_year') }}" class="period-btn {{ $currentPeriod === 'half_year' ? 'active' : '' }}">Half a Year</a>
            <a href="{{ url('/admin/analytics?period=year') }}" class="period-btn {{ $currentPeriod === 'year' ? 'active' : '' }}">Past Year</a>
            <a href="{{ url('/admin/analytics?period=all') }}" class="period-btn {{ $currentPeriod === 'all' ? 'active' : '' }}">All Time</a>
        </div>

        <form method="GET" action="{{ url('/admin/analytics') }}" class="custom-range-form">
            <span class="filter-caption">Custom Range:</span>
            <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="custom-range-input" title="Start date" required>
            <span style="color:#9ca3af; font-size:0.8rem;">to</span>
            <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="custom-range-input" title="End date" required>
            <button type="submit" class="custom-range-btn">Filter Range</button>
            @if(!empty($startDate) || !empty($endDate))
                <a href="{{ url('/admin/analytics?period=all') }}" class="reset-link">Reset</a>
            @endif
        </form>
    </div>

    {{-- ═══════════════════ ANALYTICS KPI SUMMARY ═══════════════════ --}}
    <div class="stats-grid" style="margin-bottom:20px;">
        <div class="card stat-card" style="border-left:4px solid var(--pup-maroon);">
            <div class="stat-info">
                <h4>Period Borrows</h4>
                <p class="stat-number">{{ number_format($periodBorrows ?? 0) }}</p>
                <span class="stat-badge" style="background:#fef2f2; color:#800000;">{{ $periodLabel ?? 'All Time' }}</span>
            </div>
        </div>

        <div class="card stat-card" style="border-left:4px solid #10b981;">
            <div class="stat-info">
                <h4>Completed Returns</h4>
                <p class="stat-number">{{ number_format($periodReturns ?? 0) }}</p>
                <span class="stat-badge" style="background:#ecfdf5; color:#059669;">Recorded Check-ins</span>
            </div>
        </div>

        <div class="card stat-card" style="border-left:4px solid #6366f1;">
            <div class="stat-info">
                <h4>Active Patrons (Period)</h4>
                <p class="stat-number">{{ number_format($activeUsers ?? 0) }}</p>
                <span class="stat-meta">Unique Borrowers</span>
            </div>
        </div>

        <div class="card stat-card" style="border-left:4px solid #8b5cf6;">
            <div class="stat-info">
                <h4>Daily Borrow Velocity</h4>
                <p class="stat-number">{{ round($avgDailyBorrows ?? 0, 1) }}</p>
                <span class="stat-meta">Average Borrows / Day</span>
            </div>
        </div>

        <div class="card stat-card" style="border-left:4px solid #f59e0b;">
            <div class="stat-info">
                <h4>Never Borrowed Titles</h4>
                <p class="stat-number" style="color:#b45309;">{{ number_format($neverBorrowedCount ?? 0) }}</p>
                <span class="stat-badge" style="background:#fef3c7; color:#b45309;">Stack Review Needed</span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ BORROWING TRENDS TIMELINE CHART ═══════════════════ --}}
    <div class="card" style="margin-bottom:24px; border-top:4px solid var(--pup-maroon);">
        <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h3 style="margin:0; color:#800000; font-size:1.1rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M3 3v18h18"/><path d="M18 9l-5 5-4-4-6 6"/>
                    </svg>
                    Borrowing Trends Over Time
                </h3>
                <p style="margin:2px 0 0; font-size:0.8rem; color:#6b7280;">Longitudinal borrow velocity timeline for <strong>{{ $periodLabel ?? 'All Time' }}</strong></p>
            </div>
            <div style="font-size:0.78rem; color:#4b5563; background:#f3f4f6; padding:4px 10px; border-radius:20px; font-weight:600;">
                {{ $borrowTrend->count() }} Data Points
            </div>
        </div>

        @if($borrowTrend->count())
        <div style="padding:18px 20px;">
            <div style="position:relative; height:220px;">
                <canvas id="analyticsTrendChart"></canvas>
            </div>
        </div>
        @else
        <div style="padding:32px; text-align:center; color:#9ca3af;">
            <p>No borrow activity recorded in this selected timeframe.</p>
        </div>
        @endif
    </div>

    {{-- ═══════════════════ COMPARABLE CIRCULATION & PROGRAM INSIGHTS ═══════════════════ --}}
    <div class="comparable-section-wrapper" style="margin-bottom:24px;">
        <div class="comparable-header-bar">
            <div>
                <h3 style="margin:0; font-size:1.1rem; color:#800000; font-weight:700; display:flex; align-items:center; gap:8px;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Comparable Circulation &amp; Program Insights
                </h3>
                <p style="margin:2px 0 0; font-size:0.8rem; color:#6b7280;">Side-by-side performance: Top borrowed titles vs academic degree program distributions.</p>
            </div>
            <div style="font-size:0.78rem; color:#4b5563; background:#f3f4f6; padding:4px 10px; border-radius:20px; font-weight:600;">
                Period: {{ $periodLabel ?? 'All Time' }}
            </div>
        </div>

        <div class="dashboard-two-col">
            {{-- LEFT: Top Borrowed Books --}}
            <div class="dashboard-col">
                <div class="card" style="height:100%; display:flex; flex-direction:column; margin-bottom:0;">
                    <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; background:#fafafa;">
                        <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            Most Borrowed Books
                        </h4>
                        <span style="font-size:0.75rem; color:#6b7280; font-weight:600;">Top {{ $mostBorrowedBooks->count() }} titles</span>
                    </div>

                    <div style="overflow-x:auto; flex:1;">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width:55%;">Book Title / Author</th>
                                    <th style="width:25%;">Collection</th>
                                    <th style="width:20%; text-align:right;">Borrows</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostBorrowedBooks as $b)
                                <tr>
                                    <td>
                                        <div style="font-weight:600; color:#111827; font-size:0.85rem;">{{ $b->book->title ?? '—' }}</div>
                                        <div style="font-size:0.75rem; color:#6b7280;">{{ $b->book->author ?? 'Unknown' }}</div>
                                        @if(!empty($b->book->accession_number))
                                            <div style="font-size:0.7rem; color:#800000; font-family:monospace; font-weight:700;">Acc: {{ $b->book->accession_number }}</div>
                                        @endif
                                    </td>
                                    <td style="font-size:0.8rem; color:#4b5563;">{{ $b->book->collection ?? 'General' }}</td>
                                    <td style="text-align:right;">
                                        <span style="background:#dcfce7; color:#166534; padding:3px 8px; border-radius:12px; font-weight:700; font-size:0.78rem;">{{ $b->total }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding:24px;">No borrow records for this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Program / Course Usage with Chart --}}
            <div class="dashboard-col">
                <div class="card" style="height:100%; display:flex; flex-direction:column; margin-bottom:0;">
                    <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; background:#fafafa;">
                        <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            Usage by Program / Course
                        </h4>
                        <form method="GET" action="{{ url('/admin/analytics') }}" style="display:flex; align-items:center; gap:6px;">
                            <input type="hidden" name="period" value="{{ $currentPeriod }}">
                            @if(!empty($startDate)) <input type="hidden" name="start_date" value="{{ $startDate }}"> @endif
                            @if(!empty($endDate)) <input type="hidden" name="end_date" value="{{ $endDate }}"> @endif
                            <select name="program_filter" onchange="this.form.submit()" class="program-select">
                                <option value="">All Programs</option>
                                @foreach($allCourses ?? [] as $c)
                                    <option value="{{ $c->name }}" {{ ($programFilter ?? '') === $c->name ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    @if($programUsage->count())
                    <div style="padding:14px 18px; position:relative; height:180px; border-bottom:1px solid #f3f4f6;">
                        <canvas id="analyticsProgramChart"></canvas>
                    </div>
                    @endif

                    <div style="overflow-x:auto; flex:1;">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width:70%;">Program / Degree</th>
                                    <th style="width:30%; text-align:right;">Total Borrows</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programUsage as $p)
                                <tr>
                                    <td style="font-weight:600; color:#374151; font-size:0.85rem;">{{ $p->program }}</td>
                                    <td style="text-align:right;">
                                        <span style="background:rgba(128,0,0,0.08); color:var(--pup-maroon); padding:3px 8px; border-radius:12px; font-weight:700; font-size:0.78rem;">
                                            {{ $p->total }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="2" style="text-align:center; color:#9ca3af; padding:24px;">No program usage recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ SECONDARY SECTION: SUBJECTS & COLLECTION DISTRIBUTION ═══════════════════ --}}
    <div class="dashboard-two-col" style="padding:0; margin-bottom:24px;">
        {{-- LEFT COLUMN: Top Subjects --}}
        <div class="dashboard-col">
            <div class="card" style="height:100%; margin-bottom:0;">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 7h10M7 12h10M7 17h10"/></svg>
                        Top Borrowed Subjects
                    </h4>
                    <span style="font-size:0.75rem; color:#6b7280; font-weight:600;">{{ $topSubjects->count() }} subjects</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="report-table">
                        <thead><tr><th>Subject / Classification</th><th style="text-align:right;">Borrows</th></tr></thead>
                        <tbody>
                            @forelse($topSubjects as $s)
                            <tr>
                                <td style="font-weight:600; color:#374151;">{{ $s->subject }}</td>
                                <td style="text-align:right;">
                                    <span style="background:rgba(128,0,0,0.08); color:var(--pup-maroon); padding:2px 8px; border-radius:10px; font-weight:700; font-size:0.76rem;">{{ $s->total }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" style="text-align:center; color:#9ca3af; padding:20px;">No subject data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Borrows by Collection Type --}}
        <div class="dashboard-col">
            <div class="card" style="height:100%; margin-bottom:0;">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        Borrows by Collection Type
                    </h4>
                    <span style="font-size:0.75rem; color:#6b7280; font-weight:600;">Distribution</span>
                </div>
                <div style="padding:16px 18px;">
                    @if(($collectionUsage ?? collect())->count())
                        @php $collMax = $collectionUsage->max('total') ?: 1; $collSum = $collectionUsage->sum('total') ?: 1; @endphp
                        <div style="display:flex; flex-direction:column; gap:12px;">
                        @foreach($collectionUsage as $c)
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.82rem; margin-bottom:4px;">
                                <span style="font-weight:600; color:#374151;">{{ $c->collection ?: 'Uncategorised' }}</span>
                                <div>
                                    <span style="color:#800000; font-weight:700;">{{ $c->total }}</span>
                                    <span style="color:#6b7280; font-size:0.75rem;">({{ round($c->total / $collSum * 100) }}%)</span>
                                </div>
                            </div>
                            <div style="height:8px; background:#f3f4f6; border-radius:4px; overflow:hidden;">
                                <div style="height:100%; width:{{ round($c->total / $collMax * 100) }}%; background:linear-gradient(90deg, #800000, #c0392b); border-radius:4px;"></div>
                            </div>
                        </div>
                        @endforeach
                        </div>
                    @else
                        <p style="text-align:center; color:#9ca3af; padding:20px; margin:0;">No collection circulation data available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ LEAST BORROWED & NEVER BORROWED ═══════════════════ --}}
    <div class="card" style="margin-bottom:24px; border-top:4px solid #b45309;">
        <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h3 style="margin:0; color:#92400e; font-size:1.05rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>
                    </svg>
                    Least Borrowed / Unused Catalog Titles
                </h3>
                <p style="margin:2px 0 0; font-size:0.8rem; color:#6b7280;">Candidates for stack rotation, physical curation, or promotion.</p>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width:35px;">#</th>
                        <th style="width:130px;">Accession No.</th>
                        <th>Title</th>
                        <th>Collection</th>
                        <th style="text-align:right; width:100px;">Borrows</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leastBorrowedBooks ?? [] as $i => $b)
                    <tr>
                        <td style="color:#9ca3af; font-weight:700;">{{ $i+1 }}</td>
                        <td style="font-family:monospace; font-weight:700; color:#800000;">{{ $b->accession_number ?? '—' }}</td>
                        <td>
                            <div style="font-weight:600; color:#111;">{{ $b->title }}</div>
                            <div style="font-size:0.72rem; color:#9ca3af;">{{ $b->author ?? '' }}</div>
                        </td>
                        <td style="font-size:0.8rem; color:#6b7280;">{{ $b->collection ?? '—' }}</td>
                        <td style="text-align:right;">
                            @if($b->total == 0)
                                <span style="background:#fef3c7; color:#92400e; padding:2px 7px; border-radius:10px; font-weight:700; font-size:0.74rem;">Never</span>
                            @else
                                <span style="background:#fff7ed; color:#c2410c; padding:2px 7px; border-radius:10px; font-weight:700; font-size:0.74rem;">{{ $b->total }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="padding:20px; text-align:center; color:#9ca3af;">No books found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(($neverBorrowedCount ?? 0) > 0)
        <div style="padding:12px 18px; background:#fefce8; border-top:1px solid #fde68a; display:flex; align-items:center; gap:10px;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#b45309" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span style="font-size:0.82rem; color:#92400e;">
                <strong>{{ $neverBorrowedCount }}</strong> title{{ $neverBorrowedCount !== 1 ? 's' : '' }} in the collection {{ $neverBorrowedCount !== 1 ? 'have' : 'has' }} <strong>never been borrowed</strong>.
            </span>
        </div>
        @endif
    </div>

    {{-- ═══════════════════ FACULTY POPULAR & NEW ACQUISITIONS ═══════════════════ --}}
    <div class="dashboard-two-col" style="padding:0; margin-bottom:24px;">
        {{-- Faculty Popular Books --}}
        <div class="dashboard-col">
            <div class="card" style="height:100%; margin-bottom:0;">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Popular Among Faculty
                    </h4>
                    <span style="font-size:0.75rem; color:#6b7280;">Top {{ ($facultyPopularBooks ?? collect())->count() }}</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="report-table">
                        <thead><tr><th>Title / Accession</th><th>Author</th><th style="text-align:right;">Borrows</th></tr></thead>
                        <tbody>
                            @forelse($facultyPopularBooks ?? [] as $fb)
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:#111827;">{{ $fb->title }}</div>
                                    @if(!empty($fb->accession_number))
                                        <div style="font-size:0.7rem; color:#800000; font-family:monospace; font-weight:700;">Acc: {{ $fb->accession_number }}</div>
                                    @endif
                                </td>
                                <td style="font-size:0.8rem; color:#6b7280;">{{ $fb->author ?? '—' }}</td>
                                <td style="text-align:right; font-weight:700; color:#800000;">{{ $fb->total }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding:20px;">No faculty borrow records in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- New Acquisitions Timeline --}}
        <div class="dashboard-col">
            <div class="card" style="height:100%; margin-bottom:0;">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        New Acquisitions Timeline
                    </h4>
                    <span style="font-size:0.75rem; color:#6b7280; font-weight:600;">{{ ($newAcquisitions ?? collect())->count() }} books</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="report-table">
                        <thead><tr><th>Accession No.</th><th>Title</th><th>Date Added</th></tr></thead>
                        <tbody>
                            @forelse($newAcquisitions ?? [] as $book)
                            <tr>
                                <td style="white-space:nowrap;">
                                    <span style="background:rgba(128,0,0,0.08); color:#800000; padding:2px 6px; border-radius:4px; font-family:monospace; font-weight:700; font-size:0.75rem;">
                                        {{ $book->accession_number ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#111827; font-size:0.84rem;">{{ $book->title }}</div>
                                    <div style="font-size:0.72rem; color:#6b7280;">{{ $book->author ?? '—' }}</div>
                                </td>
                                <td style="white-space:nowrap; font-size:0.78rem; color:#6b7280;">{{ $book->created_at ? $book->created_at->format('Y-m-d') : '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding:20px;">No new acquisitions in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ═══════════════════ ANALYTICS STYLES ═══════════════════ */
.analytics-page {
    max-width: 1400px;
    margin: 0 auto;
}
.dash-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 16px;
}
.dash-title {
    font-size: 1.55rem;
    font-weight: 800;
    color: #800000;
    margin: 0 0 4px;
    line-height: 1.2;
}
.dash-subtitle {
    font-size: 0.85rem;
    color: #6b7280;
    margin: 0;
}
.dash-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.btn-action-outline {
    min-height: 44px;
    padding: 8px 16px;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.84rem;
    font-weight: 600;
    color: #374151;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-action-outline:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    color: #111827;
}
.report-dropdown-wrapper {
    position: relative;
    display: inline-block;
}
.btn-report-dropdown {
    min-height: 44px;
    padding: 9px 18px;
    background: #800000;
    color: #FFC72C;
    border: none;
    border-radius: 8px;
    font-size: 0.86rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(128,0,0,0.2);
    transition: background 0.15s, transform 0.15s;
}
.btn-report-dropdown:hover {
    background: #5a0000;
    transform: translateY(-1px);
}
.report-dropdown-menu {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    width: 340px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1);
    z-index: 1050;
    display: none;
    padding: 8px 0;
}
.report-dropdown-menu.show {
    display: block;
    animation: dropFade 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes dropFade {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
.dropdown-header {
    padding: 10px 16px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    font-weight: 700;
    color: #800000;
}
.dropdown-item {
    padding: 8px 16px;
    white-space: normal;
    width: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    transition: background 0.12s;
}
.dropdown-item:hover { background: #fef2f2; }
.dropdown-item .item-title {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}
.dropdown-item .item-icon { font-size: 1.1rem; }
.dropdown-item .item-title strong {
    display: block;
    font-size: 0.82rem;
    color: #1f2937;
}
.dropdown-item .item-title small {
    display: block;
    font-size: 0.72rem;
    color: #6b7280;
}
.dropdown-item .item-formats {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}
.fmt-btn {
    padding: 4px 7px;
    border-radius: 4px;
    font-size: 0.68rem;
    font-weight: 800;
    text-decoration: none;
    min-width: 28px;
    text-align: center;
}
.fmt-pdf { background: #fee2e2; color: #991b1b; }
.fmt-csv { background: #dcfce7; color: #166534; }
.fmt-xlsx { background: #e0e7ff; color: #3730a3; }
.fmt-json { background: #fef3c7; color: #92400e; }

/* Period Filter Bar */
.period-filter-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 16px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.filter-caption {
    font-size: 0.74rem;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.period-filter { display: flex; gap: 5px; flex-wrap: wrap; align-items: center; }
.period-btn {
    padding: 5px 12px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 0.78rem;
    font-weight: 600;
    color: #800000;
    border: 1px solid #e5e7eb;
    background: #fff;
    transition: all 0.15s;
    min-height: 32px;
    display: inline-flex;
    align-items: center;
}
.period-btn:hover { background: rgba(128,0,0,0.05); color: #800000; }
.period-btn.active { background: #800000; color: #FFC72C; border-color: #800000; }

.custom-range-form { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.custom-range-input {
    padding: 5px 8px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.78rem;
    font-family: inherit;
    color: #374151;
    background: #fff;
    min-height: 32px;
}
.custom-range-btn {
    padding: 5px 12px;
    background: #800000;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
    min-height: 32px;
}
.custom-range-btn:hover { background: #5a0000; }
.reset-link { font-size: 0.78rem; color: #800000; text-decoration: none; font-weight: 700; margin-left: 4px; }

/* Stat Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}
.stat-card {
    background: #fff;
    border-radius: 10px;
    padding: 14px 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #f0f0f0;
}
.stat-info h4 {
    margin: 0;
    font-size: 0.8rem;
    font-weight: 700;
    color: #4b5563;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.stat-number {
    font-size: 1.55rem;
    font-weight: 800;
    color: #111827;
    margin: 6px 0 4px;
    line-height: 1.1;
}
.stat-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 10px;
    display: inline-block;
}
.stat-meta { font-size: 0.72rem; color: #6b7280; font-weight: 600; }

/* Comparable Section Layout */
.comparable-section-wrapper {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    overflow: hidden;
}
.comparable-header-bar {
    padding: 14px 20px;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.dashboard-two-col {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
    gap: 16px;
    padding: 16px;
}
.dashboard-col { min-width: 0; }

/* Tables */
.report-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.report-table th {
    padding: 8px 14px;
    background: #800000;
    color: #fff;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    text-align: left;
}
.report-table td {
    padding: 8px 14px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.report-table tbody tr:hover { background: #fafafa; }
.program-select {
    padding: 4px 8px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.76rem;
    font-family: inherit;
    color: #374151;
    background: #fff;
    cursor: pointer;
    min-height: 32px;
}
</style>

{{-- ═══════════════════ SCRIPT LOGIC (Charts & Export Dropdown) ═══════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleAnalyticsExport(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('analyticsExportMenu');
    if (menu) menu.classList.toggle('show');
}
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('analyticsExportWrapper');
    const menu = document.getElementById('analyticsExportMenu');
    if (wrapper && !wrapper.contains(e.target) && menu) {
        menu.classList.remove('show');
    }
});

(function() {
    // 1. Borrow Trends Line Chart
    const trendCtx = document.getElementById('analyticsTrendChart');
    if (trendCtx) {
        const labels = {!! json_encode($borrowTrend->pluck('day')) !!};
        const values = {!! json_encode($borrowTrend->pluck('total')) !!};
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Borrows',
                    data: values,
                    borderColor: '#800000',
                    backgroundColor: 'rgba(128,0,0,0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#800000',
                    pointBorderColor: '#FFC72C',
                    pointBorderWidth: 1.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ' ' + c.parsed.y + ' borrows' } }
                },
                scales: {
                    x: { ticks: { font: { size: 10 }, maxTicksLimit: 12 }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } }
                }
            }
        });
    }

    // 2. Usage by Program Chart
    const progCtx = document.getElementById('analyticsProgramChart');
    if (progCtx) {
        const palette = ['#800000','#a82020','#c0392b','#d97706','#e67e22','#27ae60','#0f766e','#2980b9','#8e44ad','#2c3e50'];
        const count = {!! $programUsage->count() !!};
        const colors = Array.from({length: count}, (_, i) => palette[i % palette.length]);
        new Chart(progCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($programUsage->pluck('program')) !!},
                datasets: [{
                    label: 'Total Borrows',
                    data: {!! json_encode($programUsage->pluck('total')) !!},
                    backgroundColor: colors,
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed.x + ' borrows' } }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.06)' } },
                    y: { ticks: { font: { size: 10 } }, grid: { display: false } }
                }
            }
        });
    }
})();
</script>
@endsection