@extends('layouts.admin')

@section('content')

@php
    $currentPeriod = $period ?? 'all';
    $exportParams = ['period' => $currentPeriod];
    if (!empty($startDate)) $exportParams['start_date'] = $startDate;
    if (!empty($endDate)) $exportParams['end_date'] = $endDate;
@endphp

<div class="dashboard-page">
    {{-- ═══════════════════ TOP HEADER & OPERATIONAL ACTIONS ═══════════════════ --}}
    <div class="dash-header">
        <div class="dash-title-group">
            <h1 class="dash-title">Operational Dashboard</h1>
            <p class="dash-subtitle">
                Real-time circulation activity, desk operations, and today's snapshot for: <strong>{{ $periodLabel ?? 'All Time' }}</strong>
            </p>
        </div>

        <div class="dash-actions">
            {{-- Notification Bell (Requirement #1f) --}}
            <div class="notification-dropdown-wrapper" id="notifDropdownWrapper">
                <button type="button" class="btn-notif-bell" id="notifBellBtn" onclick="toggleNotifDropdown(event)" title="View pending operational notifications">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if(($totalPendingNotifications ?? 0) > 0)
                        <span class="notif-badge">{{ $totalPendingNotifications }}</span>
                    @endif
                </button>

                <div class="notif-dropdown-menu" id="notifDropdownMenu">
                    <div class="notif-header">
                        <span>Pending Operational Tasks</span>
                        <span class="notif-count-pill">{{ $totalPendingNotifications ?? 0 }} total</span>
                    </div>

                    <div class="notif-list">
                        <a href="{{ route('admin.account.requests') }}" class="notif-item">
                            <div class="notif-icon notif-icon-maroon">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg>
                            </div>
                            <div class="notif-content">
                                <strong>User Registrations</strong>
                                <small>{{ $pendingUserApprovals ?? 0 }} awaiting verification</small>
                            </div>
                            <span class="notif-pill {{ ($pendingUserApprovals ?? 0) > 0 ? 'pill-alert' : 'pill-ok' }}">{{ $pendingUserApprovals ?? 0 }}</span>
                        </a>

                        <a href="{{ route('admin.operations.overdue') }}" class="notif-item">
                            <div class="notif-icon notif-icon-amber">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            </div>
                            <div class="notif-content">
                                <strong>Overdue Loans</strong>
                                <small>{{ $overdueBooksCount ?? 0 }} books past due date</small>
                            </div>
                            <span class="notif-pill {{ ($overdueBooksCount ?? 0) > 0 ? 'pill-warn' : 'pill-ok' }}">{{ $overdueBooksCount ?? 0 }}</span>
                        </a>

                        <a href="{{ route('admin.operations.damage') }}" class="notif-item">
                            <div class="notif-icon notif-icon-red">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="notif-content">
                                <strong>Damage Reports</strong>
                                <small>{{ $pendingDamageReports ?? 0 }} requiring assessment</small>
                            </div>
                            <span class="notif-pill {{ ($pendingDamageReports ?? 0) > 0 ? 'pill-alert' : 'pill-ok' }}">{{ $pendingDamageReports ?? 0 }}</span>
                        </a>

                        <a href="{{ route('admin.operations.index') }}" class="notif-item">
                            <div class="notif-icon notif-icon-maroon">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div class="notif-content">
                                <strong>Unresolved Penalties</strong>
                                <small>{{ $pendingPenaltiesCount ?? 0 }} pending settlement</small>
                            </div>
                            <span class="notif-pill {{ ($pendingPenaltiesCount ?? 0) > 0 ? 'pill-alert' : 'pill-ok' }}">{{ $pendingPenaltiesCount ?? 0 }}</span>
                        </a>

                        <a href="{{ route('admin.operations.index') }}" class="notif-item">
                            <div class="notif-icon notif-icon-teal">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="notif-content">
                                <strong>Book Requisitions</strong>
                                <small>{{ $pendingRequisitions ?? 0 }} submitted purchase requests</small>
                            </div>
                            <span class="notif-pill {{ ($pendingRequisitions ?? 0) > 0 ? 'pill-info' : 'pill-ok' }}">{{ $pendingRequisitions ?? 0 }}</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- System Collation Guide Button --}}
            <button type="button" class="btn-action-outline" onclick="openCollationModal()" title="View how library data is collated and processed">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                </svg>
                <span>Collation Guide</span>
            </button>

            {{-- Report Downloads Modal Button (Requirement #3) --}}
            <button type="button" class="btn-action-outline" onclick="openReportPresetModal()" title="Open Report Downloads filter modal">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                <span>Report Downloads</span>
            </button>

            {{-- Go to Analytics Hub --}}
            <a href="{{ route('admin.analytics') }}" class="btn-action-outline" style="border-color:var(--pup-maroon); color:var(--pup-maroon); font-weight:700;" title="View longitudinal trends and strategic analytics">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>
                </svg>
                <span>View Analytics Hub</span>
            </a>

            {{-- REDESIGNED EXPORT REPORTS BUTTON & DROPDOWN (Requirement #2) --}}
            <div class="report-dropdown-wrapper" id="reportDropdownWrapper">
                <button type="button" class="btn-export-reports" id="reportDropdownBtn" onclick="toggleReportDropdown(event)">
                    <svg class="gold-icon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#FFC72C" stroke-width="2.2">
                        <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Export Reports</span>
                    <svg class="chevron-icon" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#FFC72C" stroke-width="2.5">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div class="report-dropdown-menu" id="reportDropdownMenu">
                    <div class="dropdown-header">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#FFC72C" stroke-width="2.2"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span style="color:#FFC72C; font-weight:800; font-size:0.9rem;">Select Report &amp; Format</span>
                        </div>
                        <small style="color:rgba(255,255,255,0.8); font-size:0.75rem;">Period: {{ $periodLabel ?? 'All Time' }}</small>
                    </div>

                    {{-- Category 1: Core Statistics --}}
                    <div class="dropdown-category">Core Statistics</div>
                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>General Statistics</strong>
                            <small>Patron ratios, course breakdown &amp; book usage</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.general', $exportParams) }}" title="Download PDF" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'general', 'format' => 'csv'])) }}" title="Export CSV" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'general', 'format' => 'xlsx'])) }}" title="Export Excel" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'general', 'format' => 'json'])) }}" title="Export JSON" class="fmt-btn">JSON</a>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>Circulation Report</strong>
                            <small>Most &amp; least borrowed catalog volumes</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.circulation', $exportParams) }}" title="Download PDF" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'circulation', 'format' => 'csv'])) }}" title="Export CSV" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'circulation', 'format' => 'xlsx'])) }}" title="Export Excel" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'circulation', 'format' => 'json'])) }}" title="Export JSON" class="fmt-btn">JSON</a>
                        </div>
                    </div>

                    {{-- Category 2: Patron Activity --}}
                    <div class="dropdown-category">Patron Activity</div>
                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>Student Borrowing</strong>
                            <small>Student checkouts and course histories</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.students', $exportParams) }}" title="Download PDF" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'students', 'format' => 'csv'])) }}" title="Export CSV" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'students', 'format' => 'xlsx'])) }}" title="Export Excel" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'students', 'format' => 'json'])) }}" title="Export JSON" class="fmt-btn">JSON</a>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>Faculty Borrowing</strong>
                            <small>Faculty checkouts and department metrics</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.faculty', $exportParams) }}" title="Download PDF" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'faculty', 'format' => 'csv'])) }}" title="Export CSV" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'faculty', 'format' => 'xlsx'])) }}" title="Export Excel" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'faculty', 'format' => 'json'])) }}" title="Export JSON" class="fmt-btn">JSON</a>
                        </div>
                    </div>

                    {{-- Category 3: Catalog & Collections --}}
                    <div class="dropdown-category">Catalog &amp; Collections</div>
                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>Acquired Books</strong>
                            <small>Accession timeline and donations</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.acquired', $exportParams) }}" title="Download PDF" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'acquired', 'format' => 'csv'])) }}" title="Export CSV" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'acquired', 'format' => 'xlsx'])) }}" title="Export Excel" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'acquired', 'format' => 'json'])) }}" title="Export JSON" class="fmt-btn">JSON</a>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>Condemned Books</strong>
                            <small>Deaccessioned and damaged volumes</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.condemned', $exportParams) }}" title="Download PDF" class="fmt-btn">PDF</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'condemned', 'format' => 'csv'])) }}" title="Export CSV" class="fmt-btn">CSV</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'condemned', 'format' => 'xlsx'])) }}" title="Export Excel" class="fmt-btn">XLSX</a>
                            <a href="{{ route('admin.reports.export.raw', array_merge($exportParams, ['type' => 'condemned', 'format' => 'json'])) }}" title="Export JSON" class="fmt-btn">JSON</a>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <div class="item-title">
                            <strong>Book Recommendations</strong>
                            <small>AI suggested purchases based on catalog gaps</small>
                        </div>
                        <div class="item-formats">
                            <a href="{{ route('admin.reports.book-recommendations') }}" title="Download PDF" class="fmt-btn">PDF</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ PERIOD FILTER BAR ═══════════════════ --}}
    <div class="period-filter-bar">
        <div class="period-filter">
            <span class="filter-caption">Preset:</span>
            <a href="{{ url('/dashboard?period=today') }}" class="period-btn {{ $currentPeriod === 'today' || $currentPeriod === 'day' ? 'active' : '' }}">Today</a>
            <a href="{{ url('/dashboard?period=week') }}" class="period-btn {{ $currentPeriod === 'week' ? 'active' : '' }}">Past Week</a>
            <a href="{{ url('/dashboard?period=month') }}" class="period-btn {{ $currentPeriod === 'month' ? 'active' : '' }}">Past Month</a>
            <a href="{{ url('/dashboard?period=quarter') }}" class="period-btn {{ $currentPeriod === 'quarter' ? 'active' : '' }}">Past Quarter</a>
            <a href="{{ url('/dashboard?period=half_year') }}" class="period-btn {{ $currentPeriod === 'half_year' ? 'active' : '' }}">Half a Year</a>
            <a href="{{ url('/dashboard?period=year') }}" class="period-btn {{ $currentPeriod === 'year' ? 'active' : '' }}">Past Year</a>
            <a href="{{ url('/dashboard?period=all') }}" class="period-btn {{ $currentPeriod === 'all' ? 'active' : '' }}">All Time</a>
        </div>

        <form method="GET" action="{{ url('/dashboard') }}" class="custom-range-form">
            <span class="filter-caption">Custom Range:</span>
            <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="custom-range-input" title="Start date" required>
            <span style="color:#9ca3af; font-size:0.8rem;">to</span>
            <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="custom-range-input" title="End date" required>
            <button type="submit" class="custom-range-btn">Filter Range</button>
            @if(!empty($startDate) || !empty($endDate))
                <a href="{{ url('/dashboard?period=all') }}" class="reset-link">Reset</a>
            @endif
        </form>
    </div>

    {{-- ═══════════════════ OPERATIONAL SNAPSHOT TABLE (Requirement #1) ═══════════════════ --}}
    <div class="card stat-table-card" style="margin-bottom:24px;">
        <div class="stat-table-header">
            <div style="display:flex; align-items:center; gap:10px;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#FFC72C" stroke-width="2.2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                </svg>
                <h3 style="margin:0; font-size:1rem; font-weight:700; color:#fff;">Operational Statistics &amp; Circulation Snapshot</h3>
            </div>
            <span class="stat-period-pill">
                Period: {{ $periodLabel ?? 'All Time' }}
            </span>
        </div>

        <div style="overflow-x:auto;">
            <table class="dash-stat-table">
                <thead>
                    <tr>
                        <th style="width:38%;">Metric</th>
                        <th style="width:24%; text-align:center;">Value</th>
                        <th style="width:38%; text-align:right;">Sub-label</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="stat-metric-name">
                                <span class="metric-dot" style="background:#d97706;"></span>
                                <strong>Overdue Books</strong>
                                {{-- <span class="warning-pill" style="margin-left:8px;">Action Req.</span> --}}
                            </div>
                        </td>
                        <td class="stat-cell-value" style="color:#b45309; text-align:center;">
                            {{ number_format($overdueBooksCount ?? 0) }}
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('admin.operations.overdue') }}" class="overdue-link" style="color:#b45309; font-weight:700; text-decoration:none;">
                                View Overdue Records &rarr;
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="stat-metric-name">
                                <span class="metric-dot" style="background:#0891b2;"></span>
                                <strong>Currently On Loan</strong>
                            </div>
                        </td>
                        <td class="stat-cell-value" style="color:#0891b2; text-align:center;">
                            {{ number_format($currentlyOnLoan ?? 0) }}
                        </td>
                        <td style="text-align:right;">
                            <span class="stat-badge" style="background:#ecfeff; color:#0891b2; font-weight:700;">Active Borrowers</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="stat-metric-name">
                                <span class="metric-dot" style="background:#800000;"></span>
                                <strong>Today's Borrows</strong>
                            </div>
                        </td>
                        <td class="stat-cell-value" style="color:#800000; text-align:center;">
                            {{ number_format($dailyBorrows ?? 0) }}
                        </td>
                        <td style="text-align:right;">
                            <span class="stat-badge" style="background:#fef2f2; color:#800000; font-weight:700;">Today's Checkouts</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="stat-metric-name">
                                <span class="metric-dot" style="background:#059669;"></span>
                                <strong>Today's Returns</strong>
                            </div>
                        </td>
                        <td class="stat-cell-value" style="color:#059669; text-align:center;">
                            {{ number_format(($todayReturnsList ?? collect())->count()) }}
                        </td>
                        <td style="text-align:right;">
                            <span class="stat-badge" style="background:#ecfdf5; color:#059669; font-weight:700;">Completed Today</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="stat-metric-name">
                                <span class="metric-dot" style="background:#2563eb;"></span>
                                <strong>Registered Users</strong>
                            </div>
                        </td>
                        <td class="stat-cell-value" style="color:#1e40af; text-align:center;">
                            {{ number_format(($totalStudents ?? 0) + ($totalFaculty ?? 0)) }}
                        </td>
                        <td style="text-align:right;">
                            <span class="stat-meta" style="font-weight:700; color:#4b5563;">{{ $totalStudents ?? 0 }} Students | {{ $totalFaculty ?? 0 }} Faculty</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="stat-metric-name">
                                <span class="metric-dot" style="background:#dc2626;"></span>
                                <strong>Pending Approvals</strong>
                            </div>
                        </td>
                        <td class="stat-cell-value" style="color:#b91c1c; text-align:center;">
                            {{ number_format($pendingUserApprovals ?? 0) }}
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('admin.account.requests') }}" style="font-size:0.78rem; color:#b91c1c; font-weight:700; text-decoration:none; background:#fee2e2; padding:3px 10px; border-radius:12px;">
                                Review Requests &rarr;
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══════════════════ OPERATIONAL ACTION SHORTCUTS ═══════════════════ --}}
    <div class="card" style="margin-bottom:24px; padding:16px 20px; background:linear-gradient(135deg, #fafafa 0%, #ffffff 100%); border-left:4px solid var(--pup-maroon);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h3 style="margin:0; font-size:1rem; font-weight:700; color:var(--pup-maroon);">Quick Operational Launchpad</h3>
                <p style="margin:2px 0 0; font-size:0.8rem; color:#6b7280;">Direct shortcuts to operational modules and desk tasks.</p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('admin.operations.index') }}" class="btn-action-outline" style="background:#800000; color:#FFC72C; border-color:#800000; font-weight:700;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                    <span>Desk Transactions</span>
                </a>
                <a href="/admin/archive-scanner" class="btn-action-outline" style="background:#fff; color:#800000; border-color:#800000; font-weight:700;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                    <span>Batch Scan &amp; Archive</span>
                </a>
                <a href="{{ route('admin.account.requests') }}" class="btn-action-outline">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg>
                    <span>User Approvals ({{ $pendingUserApprovals ?? 0 }})</span>
                </a>
                <a href="{{ route('admin.books') }}" class="btn-action-outline">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <span>Books Catalog</span>
                </a>
                <a href="{{ route('kiosk.index') }}" target="_blank" class="btn-action-outline">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    <span>Kiosk Mode</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ REAL-TIME OPERATIONAL COCKPIT (Balanced Two-Column Layout) ═══════════════════ --}}
    <div class="dashboard-two-col" style="padding:0; margin-bottom:24px;">
        {{-- LEFT COLUMN: Currently Active Loans, Today's Borrows & Returns, Recently Added Books --}}
        <div class="dashboard-col" style="display:flex; flex-direction:column; gap:20px;">
            {{-- Currently Active Loans --}}
            <div class="card" style="margin-bottom:0;">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; background:#fafafa;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Currently Active Loans (Checkouts)
                    </h4>
                    <span style="font-size:0.75rem; background:#ecfeff; color:#0891b2; font-weight:700; padding:2px 8px; border-radius:10px;">
                        {{ $currentlyOnLoan ?? 0 }} on loan
                    </span>
                </div>

                <div style="overflow-x:auto;">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Borrower</th>
                                <th style="text-align:right;">Borrowed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeLoansList ?? [] as $u)
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:#111827; font-size:0.85rem;">{{ $u->book->title ?? '—' }}</div>
                                    @if(!empty($u->book->accession_number))
                                        <div style="font-size:0.7rem; color:#800000; font-family:monospace; font-weight:700;">Acc: {{ $u->book->accession_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($u->student)
                                        <div style="font-weight:600; font-size:0.82rem; color:#1f2937;">{{ $u->student->first_name }} {{ $u->student->last_name }}</div>
                                        <small style="color:#6b7280;">Student ({{ $u->student->student_number ?? '—' }})</small>
                                    @elseif($u->faculty)
                                        <div style="font-weight:600; font-size:0.82rem; color:#1f2937;">{{ $u->faculty->first_name }} {{ $u->faculty->last_name }}</div>
                                        <small style="color:#6b7280;">Faculty ({{ $u->faculty->employee_id ?? '—' }})</small>
                                    @else
                                        <span style="color:#9ca3af;">Unknown Patron</span>
                                    @endif
                                </td>
                                <td style="text-align:right; font-size:0.78rem; color:#4b5563; white-space:nowrap;">
                                    {{ $u->time_in ? $u->time_in->format('M d, h:i A') : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding:24px;">No active loans at this moment.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Today's Circulation Activity --}}
            <div class="card" style="margin-bottom:0;">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; background:#fafafa;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Today's Borrows &amp; Returns
                    </h4>
                    <span style="font-size:0.75rem; background:#fef2f2; color:#800000; font-weight:700; padding:2px 8px; border-radius:10px;">
                        {{ ($todayBorrowsList ?? collect())->count() + ($todayReturnsList ?? collect())->count() }} today
                    </span>
                </div>

                <div style="overflow-x:auto;">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Patron / Book</th>
                                <th style="text-align:right;">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $todayEvents = collect();
                                foreach($todayBorrowsList ?? [] as $b) {
                                    $todayEvents->push([
                                        'type' => 'Borrow',
                                        'item' => $b,
                                        'time' => $b->time_in,
                                    ]);
                                }
                                foreach($todayReturnsList ?? [] as $r) {
                                    $todayEvents->push([
                                        'type' => 'Return',
                                        'item' => $r,
                                        'time' => $r->time_out,
                                    ]);
                                }
                                $todayEvents = $todayEvents->sortByDesc('time')->take(8);
                            @endphp

                            @forelse($todayEvents as $evt)
                            @php $usage = $evt['item']; @endphp
                            <tr>
                                <td>
                                    @if($evt['type'] === 'Borrow')
                                        <span style="background:#fee2e2; color:#991b1b; padding:2px 7px; border-radius:10px; font-weight:700; font-size:0.72rem;">Borrow</span>
                                    @else
                                        <span style="background:#dcfce7; color:#166534; padding:2px 7px; border-radius:10px; font-weight:700; font-size:0.72rem;">Return</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#111827; font-size:0.82rem;">{{ $usage->book->title ?? '—' }}</div>
                                    <div style="font-size:0.74rem; color:#6b7280;">
                                        @if($usage->student)
                                            {{ $usage->student->first_name }} {{ $usage->student->last_name }} (Student)
                                        @elseif($usage->faculty)
                                            {{ $usage->faculty->first_name }} {{ $usage->faculty->last_name }} (Faculty)
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align:right; font-size:0.78rem; color:#4b5563; white-space:nowrap;">
                                    {{ $evt['time'] ? $evt['time']->format('h:i A') : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding:24px;">No transactions recorded today yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recently Added Books --}}
            <div class="card" style="margin-bottom:0;">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; background:#fafafa;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                        Recently Added Books
                    </h4>
                    <a href="{{ route('admin.books') }}" style="font-size:0.75rem; color:#800000; font-weight:700; text-decoration:none;">View All Catalog &rarr;</a>
                </div>

                <div class="recent-books-list">
                    @forelse($recentAddedBooks ?? [] as $book)
                        <div class="recent-book-item">
                            <div class="recent-book-icon">📖</div>
                            <div class="recent-book-info">
                                <div class="recent-book-title">
                                    {{ $book->title }}
                                </div>
                                <div class="recent-book-author">{{ $book->author ?? '—' }}</div>
                            </div>
                            <div class="recent-book-date">
                                {{ $book->created_at ? $book->created_at->format('M d, Y') : '—' }}
                            </div>
                        </div>
                    @empty
                        <div style="padding:20px; text-align:center; color:#9ca3af; font-size:0.82rem;">No recent books added.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Weekly Highlights & Recent Activity Feed --}}
        <div class="dashboard-col" style="display:flex; flex-direction:column; gap:20px;">
            {{-- Top 5 Borrowed This Week --}}
            <div class="card" style="margin-bottom:0;">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; background:#fafafa;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2"><path d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        Top 5 Borrowed This Week
                    </h4>
                    <span style="font-size:0.75rem; color:#6b7280; font-weight:600;">Current Week</span>
                </div>

                <div class="leaderboard-list">
                    @forelse($topBorrowedThisWeek ?? [] as $idx => $item)
                        <div class="leaderboard-item">
                            <div class="leaderboard-rank rank-{{ $idx + 1 }}">{{ $idx + 1 }}</div>
                            <div class="leaderboard-details">
                                <div class="leaderboard-title">
                                    {{ $item->book->title ?? 'Unknown Book' }}
                                </div>
                                <div class="leaderboard-author">{{ $item->book->author ?? 'Unknown Author' }}</div>
                            </div>
                            <div class="leaderboard-count">
                                <strong>{{ $item->total }}</strong>
                                <span>borrows</span>
                            </div>
                        </div>
                    @empty
                        <div style="padding:20px; text-align:center; color:#9ca3af; font-size:0.82rem;">No borrowing records recorded this week yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Activity Feed --}}
            <div class="card" style="margin-bottom:0;">
                <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; background:#fafafa;">
                    <h4 style="margin:0; color:#800000; font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        Recent Activity Feed
                    </h4>
                    <a href="{{ route('admin.operations.logs') }}" style="font-size:0.75rem; color:#800000; font-weight:700; text-decoration:none;">Full Audit Log &rarr;</a>
                </div>

                <div class="timeline-feed">
                    @forelse($recentActivities ?? [] as $act)
                        @php
                            $actionLower = strtolower($act->action ?? '');
                            $dotColor = '#800000';
                            if (str_contains($actionLower, 'borrow')) $dotColor = '#800000';
                            elseif (str_contains($actionLower, 'return')) $dotColor = '#10b981';
                            elseif (str_contains($actionLower, 'archive')) $dotColor = '#64748b';
                            elseif (str_contains($actionLower, 'approved')) $dotColor = '#059669';
                            elseif (str_contains($actionLower, 'penalty') || str_contains($actionLower, 'overdue') || str_contains($actionLower, 'damage')) $dotColor = '#d97706';
                        @endphp
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background:{{ $dotColor }};"></div>
                            <div class="timeline-body">
                                <div class="timeline-header">
                                    <span class="timeline-action">{{ $act->action_label ?? ucwords(str_replace('_', ' ', $act->action)) }}</span>
                                    <span class="timeline-time">{{ $act->created_at ? $act->created_at->diffForHumans() : '—' }}</span>
                                </div>
                                <div class="timeline-desc">{{ $act->description ?? 'System action performed' }}</div>
                            </div>
                        </div>
                    @empty
                        <div style="padding:24px; text-align:center; color:#9ca3af; font-size:0.82rem;">No system actions recorded recently.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════ MODAL 1: REPORT DOWNLOADS (Requirement #3) ═══════════════════ --}}
<div id="reportPresetModal" class="custom-modal-backdrop" style="display:none;">
    <div class="custom-modal-card" style="max-width:620px;">
        <div class="modal-header-bar">
            <div>
                <h3 style="margin:0; font-size:1.15rem; color:#800000; font-weight:700;">Report Downloads</h3>
                <p style="margin:2px 0 0; font-size:0.78rem; color:#6b7280;">Choose target report type and customize data sections to include in the output.</p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeReportPresetModal()">&times;</button>
        </div>

        <div class="modal-body-content">
            <form id="customReportForm" method="GET" action="{{ route('admin.reports.general') }}">
                <input type="hidden" name="custom_options_applied" value="1">
                <input type="hidden" name="period" value="{{ $currentPeriod }}">
                @if(!empty($startDate)) <input type="hidden" name="start_date" value="{{ $startDate }}"> @endif
                @if(!empty($endDate)) <input type="hidden" name="end_date" value="{{ $endDate }}"> @endif

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:#374151; margin-bottom:6px;">Target Report Type:</label>
                    <select id="presetReportType" name="report_type" onchange="handleReportTypeChange()" style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:0.88rem; font-weight:600; color:#1f2937;">
                        <option value="general">General Statistics Report</option>
                        <option value="circulation">Circulation Report</option>
                        <option value="students">Student Borrowing Report</option>
                        <option value="faculty">Faculty Borrowing Report</option>
                        <option value="acquired">Acquired Books Report</option>
                        <option value="condemned">Condemned Books Report</option>
                    </select>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:#374151; margin-bottom:8px;">Include in Report:</label>
                    <div id="dynamicReportOptionsContainer" style="display:grid; grid-template-columns:repeat(2, 1fr); gap:10px; font-size:0.82rem; background:#f9fafb; padding:14px; border-radius:8px; border:1px solid #e5e7eb;">
                        {{-- Populated dynamically via JS --}}
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn-action-outline" onclick="closeReportPresetModal()">Cancel</button>
                    <button type="submit" class="btn-primary" style="background:#800000; color:#FFC72C; font-weight:700; padding:9px 20px; border-radius:8px; border:none; cursor:pointer; min-height:44px;">
                        Generate Custom Table Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════ MODAL 2: HOW THE SYSTEM COLLATES INFORMATION ═══════════════════ --}}
<div id="collationModal" class="custom-modal-backdrop" style="display:none;">
    <div class="custom-modal-card" style="max-width:750px;">
        <div class="modal-header-bar">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(128,0,0,0.1); color:#800000; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.1rem;">ℹ️</div>
                <div>
                    <h3 style="margin:0; font-size:1.15rem; color:#800000; font-weight:700;">How PUPSJ Libris Collates Library Information</h3>
                    <p style="margin:2px 0 0; font-size:0.78rem; color:#6b7280;">Documentation on circulation data indexing, operational aggregation &amp; reporting</p>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeCollationModal()">&times;</button>
        </div>

        <div class="modal-body-content" style="max-height:70vh; overflow-y:auto; font-size:0.85rem; line-height:1.6; color:#374151;">
            <div class="collation-section">
                <h4 style="color:#800000; margin:0 0 6px; font-size:0.95rem; font-weight:700;">1. Transaction &amp; Circulation Processing</h4>
                <p>Every checkout and return is captured as an atomic record in the <code>book_usages</code> ledger. When a patron borrows a book:</p>
                <ul style="margin:4px 0 10px 20px; padding:0;">
                    <li><strong>Timestamping:</strong> The exact local time is stamped (<code>time_in</code>) alongside patron credentials (Student Number or Faculty ID).</li>
                    <li><strong>Status Lifecycle:</strong> Transitions from <code>active</code> &rarr; <code>completed</code> (on return) or <code>overdue</code> (when return threshold is breached).</li>
                    <li><strong>Copy Tracking:</strong> The system manages copies dynamically, calculating available stock vs checkouts.</li>
                </ul>
            </div>

            <div class="collation-section">
                <h4 style="color:#800000; margin:0 0 6px; font-size:0.95rem; font-weight:700;">2. Patron Categorization &amp; Course Mapping</h4>
                <p>Patron records are split into Student and Faculty sub-registries with distinct privilege bounds:</p>
                <ul style="margin:4px 0 10px 20px; padding:0;">
                    <li><strong>Academic Program Aggregation:</strong> Student usage joins with the <code>students</code> directory and <code>courses</code> master table to compute program-specific demand metrics.</li>
                    <li><strong>Faculty Department Analytics:</strong> Faculty checkouts aggregate department-level research and instructional literature consumption.</li>
                </ul>
            </div>

            <div class="collation-section">
                <h4 style="color:#800000; margin:0 0 6px; font-size:0.95rem; font-weight:700;">3. Catalog Indexing &amp; Subject Tokenization</h4>
                <p>Catalog items maintain accession numbers, Library of Congress (LOC) classification, Dewey decimal classes, and collection tags (Filipiniana, Circulation, Reference, Theses):</p>
                <ul style="margin:4px 0 10px 20px; padding:0;">
                    <li><strong>Subject Tokenization:</strong> Multi-subject books are tokenized by comma delimiters to ensure accurate representation in subject frequency charts.</li>
                    <li><strong>Zero-Borrow Auditing:</strong> The <em>Never Borrowed</em> metric evaluates books with zero recorded transactions to surface candidates for physical stack rotation or weeding.</li>
                </ul>
            </div>
        </div>

        <div style="padding:12px 20px; background:#f9fafb; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end;">
            <button type="button" class="btn-primary" style="background:#800000; color:#fff; padding:8px 18px; border-radius:8px; border:none; cursor:pointer;" onclick="closeCollationModal()">Understood</button>
        </div>
    </div>
</div>

<style>
/* ═══════════════════ DASHBOARD STYLES (PUP Themed) ═══════════════════ */
.dashboard-page {
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
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-action-outline:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    color: #111827;
}

/* Notification Bell */
.notification-dropdown-wrapper {
    position: relative;
    display: inline-block;
}
.btn-notif-bell {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    background: #fff;
    border: 1px solid #d1d5db;
    color: #800000;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    position: relative;
    transition: all 0.15s ease;
}
.btn-notif-bell:hover {
    background: #fef2f2;
    border-color: #800000;
}
.notif-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc2626;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 800;
    min-width: 19px;
    height: 19px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    box-shadow: 0 2px 4px rgba(220,38,38,0.3);
}

.notif-dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 330px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 12px 28px -5px rgba(0,0,0,0.2);
    z-index: 1060;
    display: none;
    overflow: hidden;
}
.notif-dropdown-menu.show {
    display: block;
    animation: dropFade 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}
.notif-header {
    background: #800000;
    color: #fff;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    font-weight: 700;
}
.notif-count-pill {
    background: #FFC72C;
    color: #5a0000;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 12px;
}
.notif-list {
    max-height: 380px;
    overflow-y: auto;
}
.notif-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    text-decoration: none;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.15s;
}
.notif-item:hover {
    background: #fef2f2;
}
.notif-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.notif-icon-maroon { background: #fee2e2; color: #800000; }
.notif-icon-amber { background: #fef3c7; color: #b45309; }
.notif-icon-red { background: #fee2e2; color: #dc2626; }
.notif-icon-teal { background: #ccfbf1; color: #0f766e; }
.notif-content { flex: 1; min-width: 0; }
.notif-content strong { display: block; font-size: 0.82rem; color: #1f2937; }
.notif-content small { display: block; font-size: 0.72rem; color: #6b7280; }
.notif-pill {
    font-size: 0.74rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 10px;
}
.pill-alert { background: #fee2e2; color: #991b1b; }
.pill-warn { background: #fef3c7; color: #92400e; }
.pill-info { background: #e0f2fe; color: #0369a1; }
.pill-ok { background: #f3f4f6; color: #6b7280; }

/* Redesigned Export Reports Button (Requirement #2) */
.report-dropdown-wrapper {
    position: relative;
    display: inline-block;
}
.btn-export-reports {
    min-height: 44px;
    padding: 9px 18px;
    background: #800000;
    color: #FFC72C;
    border: none;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(128,0,0,0.25);
    transition: background 0.15s, transform 0.15s;
}
.btn-export-reports:hover {
    background: #5a0000;
    transform: translateY(-1px);
}
.btn-export-reports .gold-icon {
    flex-shrink: 0;
}
.btn-export-reports .chevron-icon {
    transition: transform 0.2s;
}

.report-dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 420px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 14px 30px -5px rgba(0,0,0,0.2), 0 8px 10px -6px rgba(0,0,0,0.1);
    z-index: 1050;
    display: none;
    overflow: hidden;
    max-height: 540px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #c5a059 #f1f1f1;
}
.report-dropdown-menu::-webkit-scrollbar {
    width: 6px;
}
.report-dropdown-menu::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.report-dropdown-menu::-webkit-scrollbar-thumb {
    background: #c5a059;
    border-radius: 3px;
}
.report-dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: #800000;
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
    background: #800000;
    color: #ffffff;
    padding: 12px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #FFC72C;
}

.dropdown-category {
    padding: 10px 18px 4px;
    font-size: 0.72rem;
    font-weight: 800;
    color: #800000;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    background: #fafafa;
    border-bottom: 1px solid #f0f0f0;
}

.dropdown-item {
    padding: 10px 18px;
    white-space: normal;
    width: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #f5f5f5;
    transition: background 0.12s;
}
.dropdown-item:hover {
    background: #fdf8f8;
}
.dropdown-item .item-title {
    flex: 1;
    min-width: 0;
}
.dropdown-item .item-title strong {
    display: block;
    font-size: 0.84rem;
    color: #111827;
    font-weight: 700;
}
.dropdown-item .item-title small {
    display: block;
    font-size: 0.72rem;
    color: #6b7280;
    margin-top: 2px;
    line-height: 1.25;
    white-space: normal;
    overflow-wrap: anywhere;
}
.dropdown-item .item-formats {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}

/* PUP Themed Format Buttons (Strictly Maroon & Gold on Hover) */
.fmt-btn {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 700;
    text-decoration: none;
    min-width: 32px;
    text-align: center;
    background: #ffffff;
    color: #800000;
    border: 1px solid #800000;
    transition: all 0.15s ease;
}
.fmt-btn:hover {
    background: #800000;
    color: #FFC72C;
    border-color: #800000;
    transform: translateY(-1px);
}

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

.custom-range-form {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
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
.reset-link {
    font-size: 0.78rem;
    color: #800000;
    text-decoration: none;
    font-weight: 700;
    margin-left: 4px;
}

/* Operational Snapshot Table (Requirement #1) */
.stat-table-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.stat-table-header {
    padding: 12px 20px;
    background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.stat-period-pill {
    font-size: 0.75rem;
    background: rgba(255, 199, 44, 0.2);
    color: #FFC72C;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 12px;
    border: 1px solid rgba(255, 199, 44, 0.35);
}
.dash-stat-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.dash-stat-table th {
    padding: 10px 20px;
    background: #fafafa;
    color: #4b5563;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e5e7eb;
    text-align: left;
}
.dash-stat-table td {
    padding: 12px 20px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.dash-stat-table tbody tr:hover {
    background: #fffdf5;
}
.dash-stat-table tbody tr:last-child td {
    border-bottom: none;
}
.stat-metric-name {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.88rem;
    color: #1f2937;
}
.metric-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.stat-cell-value {
    font-size: 1.25rem;
    font-weight: 800;
    line-height: 1;
}

.stat-badge {
    font-size: 0.74rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 10px;
    display: inline-block;
}
.stat-meta {
    font-size: 0.76rem;
    color: #6b7280;
    font-weight: 600;
}

.warning-pill {
    background: #fef3c7;
    color: #b45309;
    font-size: 0.68rem;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 8px;
    text-transform: uppercase;
}
.overdue-link {
    font-size: 0.78rem;
    color: #b45309;
    font-weight: 700;
    text-decoration: none;
    display: inline-block;
    background: #fef3c7;
    padding: 3px 10px;
    border-radius: 10px;
    transition: all 0.15s;
}
.overdue-link:hover {
    background: #fde68a;
}

.dashboard-two-col {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
    gap: 20px;
}
.dashboard-col { min-width: 0; }

/* Tables */
.report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
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
    padding: 9px 14px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.report-table tbody tr:hover { background: #fafafa; }

/* Top 5 Leaderboard */
.leaderboard-list {
    padding: 8px 0;
}
.leaderboard-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 18px;
    border-bottom: 1px solid #f5f5f5;
    transition: background 0.12s;
}
.leaderboard-item:last-child { border-bottom: none; }
.leaderboard-item:hover { background: #fdf8f8; }
.leaderboard-rank {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    font-weight: 800;
    flex-shrink: 0;
}
.rank-1 { background: #FFC72C; color: #800000; }
.rank-2 { background: #e5e7eb; color: #374151; }
.rank-3 { background: #fed7aa; color: #9a3412; }
.rank-4, .rank-5 { background: #f3f4f6; color: #6b7280; }

.leaderboard-details {
    flex: 1;
    min-width: 0;
}
.leaderboard-title {
    display: block;
    font-size: 0.84rem;
    font-weight: 700;
    color: #1f2937;
    text-decoration: none;
    cursor: default;
    user-select: text;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.leaderboard-author {
    font-size: 0.74rem;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.leaderboard-count {
    text-align: right;
    flex-shrink: 0;
}
.leaderboard-count strong {
    display: block;
    font-size: 1rem;
    color: #800000;
    font-weight: 800;
    line-height: 1;
}
.leaderboard-count span {
    font-size: 0.68rem;
    color: #6b7280;
    text-transform: uppercase;
}

/* Recently Added Books */
.recent-books-list {
    padding: 8px 0;
}
.recent-book-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 18px;
    border-bottom: 1px solid #f5f5f5;
    transition: background 0.12s;
}
.recent-book-item:last-child { border-bottom: none; }
.recent-book-item:hover { background: #fdf8f8; }
.recent-book-icon {
    font-size: 1.2rem;
    flex-shrink: 0;
}
.recent-book-info {
    flex: 1;
    min-width: 0;
}
.recent-book-title {
    display: block;
    font-size: 0.84rem;
    font-weight: 700;
    color: #1f2937;
    text-decoration: none;
    cursor: default;
    user-select: text;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.recent-book-author {
    font-size: 0.74rem;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.recent-book-date {
    font-size: 0.75rem;
    color: #6b7280;
    white-space: nowrap;
    flex-shrink: 0;
    background: #f3f4f6;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 600;
}

/* Timeline Feed */
.timeline-feed {
    padding: 16px 18px;
    position: relative;
}
.timeline-feed::before {
    content: '';
    position: absolute;
    top: 24px;
    bottom: 24px;
    left: 24px;
    width: 2px;
    background: #e5e7eb;
}
.timeline-item {
    position: relative;
    padding-left: 26px;
    margin-bottom: 16px;
}
.timeline-item:last-child { margin-bottom: 0; }
.timeline-dot {
    position: absolute;
    left: 0;
    top: 4px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e5e7eb;
}
.timeline-body {
    background: #fafafa;
    border: 1px solid #f0f0f0;
    border-radius: 8px;
    padding: 8px 12px;
}
.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-bottom: 2px;
}
.timeline-action {
    font-size: 0.78rem;
    font-weight: 700;
    color: #800000;
}
.timeline-time {
    font-size: 0.7rem;
    color: #9ca3af;
    white-space: nowrap;
}
.timeline-desc {
    font-size: 0.76rem;
    color: #4b5563;
    line-height: 1.35;
}

/* Modals */
.custom-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.custom-modal-card {
    background: #fff;
    width: 100%;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2), 0 10px 10px -5px rgba(0,0,0,0.1);
    overflow: hidden;
    animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.modal-header-bar {
    padding: 16px 20px;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-body-content {
    padding: 20px;
}
.btn-close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #9ca3af;
    cursor: pointer;
    line-height: 1;
    min-width: 36px;
    min-height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}
.btn-close-modal:hover { color: #111; background: #f3f4f6; }
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    user-select: none;
}
.collation-section {
    margin-bottom: 16px;
    background: #f9fafb;
    padding: 12px 16px;
    border-radius: 8px;
    border-left: 3px solid #800000;
}

@media (max-width: 640px) {
    .report-dropdown-menu {
        width: calc(100vw - 32px);
        right: -8px;
    }
    .notif-dropdown-menu {
        width: calc(100vw - 32px);
        right: -8px;
    }
}
</style>

{{-- ═══════════════════ SCRIPT LOGIC ═══════════════════ --}}
<script>
// 1. Notification Dropdown Toggle
function toggleNotifDropdown(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('notifDropdownMenu');
    const reportMenu = document.getElementById('reportDropdownMenu');
    if (reportMenu) reportMenu.classList.remove('show');
    if (menu) menu.classList.toggle('show');
}

// 2. Export Reports Dropdown Toggle
function toggleReportDropdown(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('reportDropdownMenu');
    const notifMenu = document.getElementById('notifDropdownMenu');
    if (notifMenu) notifMenu.classList.remove('show');
    if (menu) menu.classList.toggle('show');
}

document.addEventListener('click', function(e) {
    const reportWrapper = document.getElementById('reportDropdownWrapper');
    const reportMenu = document.getElementById('reportDropdownMenu');
    if (reportWrapper && !reportWrapper.contains(e.target) && reportMenu) {
        reportMenu.classList.remove('show');
    }

    const notifWrapper = document.getElementById('notifDropdownWrapper');
    const notifMenu = document.getElementById('notifDropdownMenu');
    if (notifWrapper && !notifWrapper.contains(e.target) && notifMenu) {
        notifMenu.classList.remove('show');
    }
});

// 3. Collation Documentation Modal
function openCollationModal() {
    const m = document.getElementById('collationModal');
    if (m) m.style.display = 'flex';
}
function closeCollationModal() {
    const m = document.getElementById('collationModal');
    if (m) m.style.display = 'none';
}

// 4. Report Downloads Modal (Requirement #3)
const reportTypeOptionsMap = {
    general: [
        { name: 'inc_summary', label: 'Summary Overview & KPIs' },
        { name: 'inc_most_borrowed', label: 'Most Borrowed Titles' },
        { name: 'inc_least_borrowed', label: 'Least Borrowed / Unused' },
        { name: 'inc_top_subjects', label: 'Top Subjects / Categories' },
        { name: 'inc_program_usage', label: 'Program / Course Usage' },
        { name: 'inc_borrowing_trends', label: 'Borrowing Trends Timeline' },
        { name: 'inc_acquisitions', label: 'New Acquisitions' },
        { name: 'inc_collection_dist', label: 'Collection Distribution' }
    ],
    circulation: [
        { name: 'inc_summary', label: 'Circulation Summary KPIs' },
        { name: 'inc_most_borrowed', label: 'Most Borrowed (Top 20)' },
        { name: 'inc_least_borrowed', label: 'Least Borrowed / Unused (Bottom 20)' },
        { name: 'inc_borrowing_trends', label: 'Daily Borrow Trend Timeline' },
        { name: 'inc_collection_dist', label: 'Collection Distribution' }
    ],
    students: [
        { name: 'inc_active_loans', label: 'Active Loans Only' },
        { name: 'inc_completed_returns', label: 'Completed Returns Only' },
        { name: 'inc_program_usage', label: 'Program / Course Breakdown' },
        { name: 'inc_timestamp_logs', label: 'Detailed Transaction Logs' }
    ],
    faculty: [
        { name: 'inc_active_loans', label: 'Active Faculty Loans Only' },
        { name: 'inc_completed_returns', label: 'Completed Returns Only' },
        { name: 'inc_department_breakdown', label: 'Department Breakdown' },
        { name: 'inc_timestamp_logs', label: 'Detailed Transaction Logs' }
    ],
    acquired: [
        { name: 'inc_accession', label: 'Accession Number Column' },
        { name: 'inc_donations_only', label: 'Donations Only (Filter)' },
        { name: 'inc_publisher_info', label: 'Publisher & Publication Year' },
        { name: 'inc_date_acquired', label: 'Date Acquired Column' }
    ],
    condemned: [
        { name: 'inc_call_number', label: 'Call / LOC Number Column' },
        { name: 'inc_reason', label: 'Condemnation Reason Column' },
        { name: 'inc_condemned_by', label: 'Condemned By Admin Column' },
        { name: 'inc_last_borrower', label: 'Last Borrower Column' }
    ]
};

function renderDynamicCheckboxes(type) {
    const container = document.getElementById('dynamicReportOptionsContainer');
    if (!container) return;
    const options = reportTypeOptionsMap[type] || reportTypeOptionsMap['general'];
    container.innerHTML = '';
    options.forEach(opt => {
        const label = document.createElement('label');
        label.className = 'checkbox-label';
        label.innerHTML = `<input type="checkbox" name="${opt.name}" value="1" checked> <span>${opt.label}</span>`;
        container.appendChild(label);
    });
}

function handleReportTypeChange() {
    const typeEl = document.getElementById('presetReportType');
    if (!typeEl) return;
    const type = typeEl.value;
    const form = document.getElementById('customReportForm');
    if (form) {
        if (type === 'circulation') {
            form.action = "{{ route('admin.reports.circulation') }}";
        } else if (type === 'students') {
            form.action = "{{ route('admin.reports.students') }}";
        } else if (type === 'faculty') {
            form.action = "{{ route('admin.reports.faculty') }}";
        } else if (type === 'acquired') {
            form.action = "{{ route('admin.reports.acquired') }}";
        } else if (type === 'condemned') {
            form.action = "{{ route('admin.reports.condemned') }}";
        } else {
            form.action = "{{ route('admin.reports.general') }}";
        }
    }
    renderDynamicCheckboxes(type);
}

function openReportPresetModal() {
    const m = document.getElementById('reportPresetModal');
    if (m) {
        m.style.display = 'flex';
        handleReportTypeChange();
    }
}
function closeReportPresetModal() {
    const m = document.getElementById('reportPresetModal');
    if (m) m.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    handleReportTypeChange();
});
</script>

@endsection