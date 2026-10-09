@extends('layouts.admin')

@section('content')
<div class="audit-page">

    {{-- Page Header --}}
    <div class="audit-page-header">
        <div>
            <h2>Audit Logs</h2>
            <p class="page-subtitle">Centralized system activity, user management, circulation, operations, and security audit trail.</p>
        </div>
        <div class="header-actions">
            <span class="live-badge">
                <span class="pulse-dot"></span> Live Activity Tracking
            </span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-overview">
        <div class="stat-mini-card">
            <div class="stat-icon-wrap" style="background:#fef2f2;color:#991b1b;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <div>
                <div class="stat-val">{{ number_format($stats['total_logs']) }}</div>
                <div class="stat-lbl">Total Audit Records</div>
            </div>
        </div>
        <div class="stat-mini-card">
            <div class="stat-icon-wrap" style="background:#ecfdf5;color:#065f46;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div class="stat-val">{{ number_format($stats['today_logs']) }}</div>
                <div class="stat-lbl">Events Today</div>
            </div>
        </div>
        <div class="stat-mini-card">
            <div class="stat-icon-wrap" style="background:#eff6ff;color:#1d4ed8;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div class="stat-val">{{ number_format($stats['user_events']) }}</div>
                <div class="stat-lbl">User Management Logs</div>
            </div>
        </div>
        <div class="stat-mini-card">
            <div class="stat-icon-wrap" style="background:#fffbeb;color:#b45309;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <div>
                <div class="stat-val">{{ number_format($stats['circ_events']) }}</div>
                <div class="stat-lbl">Circulation &amp; Kiosk Logs</div>
            </div>
        </div>
    </div>

    {{-- Main Audit Card --}}
    <div class="card audit-card">
        
        {{-- Subtabs bar --}}
        <div class="audit-tabs-bar">
            <button type="button" class="tab-btn active" id="tab-btn-activity" onclick="switchAuditTab('activity', this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span>System Activity Logs</span>
                <span class="tab-count-badge">{{ $logs->total() }}</span>
            </button>
            <button type="button" class="tab-btn" id="tab-btn-damage" onclick="switchAuditTab('damage', this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>Damage Reports History</span>
                <span class="tab-count-badge">{{ $damageHistory->count() }}</span>
            </button>
            <button type="button" class="tab-btn" id="tab-btn-penalties" onclick="switchAuditTab('penalties', this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <span>Penalties &amp; Fines History</span>
                <span class="tab-count-badge">{{ $penaltyHistory->count() }}</span>
            </button>
            <button type="button" class="tab-btn" id="tab-btn-bans" onclick="switchAuditTab('bans', this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                <span>Bans &amp; Suspensions History</span>
                <span class="tab-count-badge">{{ $banHistory->count() }}</span>
            </button>
        </div>

        {{-- PANEL 1: SYSTEM ACTIVITY LOGS --}}
        <div id="panel-activity" class="audit-panel">
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="audit-filter-form" id="auditFilterForm">
                
                {{-- Date Presets Chips --}}
                <div class="filter-row-presets">
                    <span class="filter-label-inline">Date Range:</span>
                    <div class="preset-chips">
                        <button type="button" class="chip {{ $preset === 'all' ? 'active' : '' }}" onclick="setDatePreset('all')">All Time</button>
                        <button type="button" class="chip {{ $preset === 'today' ? 'active' : '' }}" onclick="setDatePreset('today')">Today</button>
                        <button type="button" class="chip {{ $preset === 'yesterday' ? 'active' : '' }}" onclick="setDatePreset('yesterday')">Yesterday</button>
                        <button type="button" class="chip {{ $preset === 'this_week' ? 'active' : '' }}" onclick="setDatePreset('this_week')">This Week</button>
                        <button type="button" class="chip {{ $preset === 'this_month' ? 'active' : '' }}" onclick="setDatePreset('this_month')">This Month</button>
                        <button type="button" class="chip {{ $preset === 'last_month' ? 'active' : '' }}" onclick="setDatePreset('last_month')">Last Month</button>
                        <button type="button" class="chip {{ $preset === 'custom' ? 'active' : '' }}" onclick="toggleCustomDates()">Custom Range</button>
                    </div>
                    <input type="hidden" name="date_preset" id="date_preset_input" value="{{ $preset }}">
                </div>

                {{-- Custom Date Range Inputs --}}
                <div id="custom-dates-wrap" class="custom-dates-bar" style="display:{{ ($preset==='custom' || $dateFrom || $dateTo) ? 'flex' : 'none' }};">
                    <div class="date-input-group">
                        <label>From:</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="date-field">
                    </div>
                    <div class="date-input-group">
                        <label>To:</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="date-field">
                    </div>
                    <button type="submit" class="btn-filter-apply">Apply Range</button>
                </div>

                {{-- Dropdowns & Search Bar --}}
                <div class="filter-controls-grid">
                    <div class="select-group">
                        <label>Module</label>
                        <select name="module" onchange="this.form.submit()" class="filter-select">
                            <option value="all" {{ $module === 'all' ? 'selected' : '' }}>All Modules</option>
                            <option value="users" {{ $module === 'users' ? 'selected' : '' }}>Users Management</option>
                            <option value="books" {{ $module === 'books' ? 'selected' : '' }}>Books Management</option>
                            <option value="circulation" {{ $module === 'circulation' ? 'selected' : '' }}>Circulation (Desk / OPAC)</option>
                            <option value="kiosk" {{ $module === 'kiosk' ? 'selected' : '' }}>Library Kiosk</option>
                            <option value="operations" {{ $module === 'operations' ? 'selected' : '' }}>Transactions &amp; Damage</option>
                            <option value="auth" {{ $module === 'auth' ? 'selected' : '' }}>Authentication &amp; Security</option>
                            <option value="programs" {{ $module === 'programs' ? 'selected' : '' }}>Programs &amp; Departments</option>
                            <option value="collection_types" {{ $module === 'collection_types' ? 'selected' : '' }}>Collection Types</option>
                            <option value="settings" {{ $module === 'settings' ? 'selected' : '' }}>System Settings</option>
                        </select>
                    </div>

                    <div class="select-group">
                        <label>Action</label>
                        <select name="action" onchange="this.form.submit()" class="filter-select">
                            <option value="all" {{ $action === 'all' ? 'selected' : '' }}>All Actions ({{ $allActions->count() }})</option>
                            @foreach($allActions as $act)
                            <option value="{{ $act }}" {{ $action === $act ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $act)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="select-group">
                        <label>Performer</label>
                        <select name="performer_type" onchange="this.form.submit()" class="filter-select">
                            <option value="all" {{ $performerType === 'all' ? 'selected' : '' }}>All Performers</option>
                            <option value="admin" {{ $performerType === 'admin' ? 'selected' : '' }}>Admin / Staff</option>
                            <option value="student" {{ $performerType === 'student' ? 'selected' : '' }}>Students</option>
                            <option value="faculty" {{ $performerType === 'faculty' ? 'selected' : '' }}>Faculty</option>
                            <option value="system" {{ $performerType === 'system' ? 'selected' : '' }}>System (Automated)</option>
                        </select>
                    </div>

                    <div class="select-group">
                        <label>Sort By</label>
                        <select name="sort" onchange="this.form.submit()" class="filter-select">
                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        </select>
                    </div>

                    <div class="search-wrap-full">
                        <label>Search Query</label>
                        <div class="search-input-box">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search action, patron, book, admin, or remarks..." autocomplete="off">
                            @if(!empty($search) || $module !== 'all' || $action !== 'all' || $preset !== 'all' || $performerType !== 'all')
                                <a href="{{ route('admin.audit-logs.index') }}" class="btn-clear-filter" title="Reset all filters">Reset</a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:140px;">Timestamp</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Performed By</th>
                            <th>Affected User / Book</th>
                            <th>Description &amp; Remarks</th>
                            <th style="width:130px;" class="col-center">Audit Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        @php
                            $meta = $log->metadata ?? [];
                            $actionBadge = 'badge-blue';
                            if (str_contains($log->action, 'banned') || str_contains($log->action, 'delete') || str_contains($log->action, 'failed') || str_contains($log->action, 'rejected')) {
                                $actionBadge = 'badge-red';
                            } elseif (str_contains($log->action, 'created') || str_contains($log->action, 'paid') || str_contains($log->action, 'resolved') || str_contains($log->action, 'unban') || str_contains($log->action, 'approved') || str_contains($log->action, 'restored')) {
                                $actionBadge = 'badge-green';
                            } elseif (str_contains($log->action, 'warning') || str_contains($log->action, 'overdue') || str_contains($log->action, 'fine') || str_contains($log->action, 'archive') || str_contains($log->action, 'suspend')) {
                                $actionBadge = 'badge-amber';
                            }

                            $modBadge = 'badge-gray';
                            if ($log->module === 'users') $modBadge = 'badge-blue';
                            elseif ($log->module === 'books') $modBadge = 'badge-amber';
                            elseif ($log->module === 'circulation' || $log->module === 'kiosk') $modBadge = 'badge-green';
                            elseif ($log->module === 'auth') $modBadge = 'badge-purple';
                            elseif ($log->module === 'operations') $modBadge = 'badge-red';

                            $affectedUser = $meta['affected_user'] ?? $meta['patron'] ?? null;
                            $affectedBook = $meta['affected_book'] ?? $meta['book_title'] ?? null;
                            $remarks = $meta['remarks'] ?? $meta['reason'] ?? $meta['notes'] ?? null;
                            $before = $meta['before'] ?? null;
                            $after = $meta['after'] ?? null;
                        @endphp
                        <tr>
                            <td class="text-muted-sm" style="white-space:nowrap;">
                                <div style="font-weight:600;color:#1e293b;">{{ $log->created_at?->format('M d, Y') ?? '—' }}</div>
                                <div style="font-size:0.75rem;color:#64748b;">{{ $log->created_at?->format('h:i:s A') ?? '' }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $actionBadge }}" style="font-weight:700;font-size:0.75rem;">
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $modBadge }}" style="font-size:0.72rem;">
                                    {{ $log->module_label }}
                                </span>
                            </td>
                            <td>
                                <strong style="color:#111827;font-size:0.875rem;">{{ $log->performer_name }}</strong>
                                <div style="font-size:0.72rem;color:#6b7280;font-weight:600;text-transform:uppercase;">{{ ucfirst($log->performed_by_type ?? 'Admin') }}</div>
                            </td>
                            <td>
                                @if($affectedUser)
                                    <div style="font-weight:600;color:#1e293b;font-size:0.85rem;">{{ $affectedUser }}</div>
                                @endif
                                @if($affectedBook)
                                    <div style="font-size:0.78rem;color:#475569;font-style:italic;">📖 {{ $affectedBook }}</div>
                                @endif
                                @if(!$affectedUser && !$affectedBook)
                                    <span class="text-muted-sm">—</span>
                                @endif
                            </td>
                            <td style="max-width:320px;white-space:normal;">
                                <div style="font-size:0.84rem;color:#334155;line-height:1.4;">{{ $log->description }}</div>
                                @if($remarks && !str_contains($log->description, $remarks))
                                    <div style="margin-top:4px;font-size:0.76rem;color:#475569;background:#f1f5f9;padding:2px 8px;border-radius:6px;display:inline-block;">
                                        <strong>Note:</strong> {{ $remarks }}
                                    </div>
                                @endif
                            </td>
                            <td class="col-center">
                                <button type="button" class="btn-inspect" onclick="openLogModal({{ json_encode([
                                    'id' => $log->id,
                                    'action' => $log->action_label,
                                    'module' => $log->module_label,
                                    'performer' => $log->performer_name,
                                    'performer_type' => ucfirst($log->performed_by_type ?? 'Admin'),
                                    'timestamp' => $log->created_at?->format('M d, Y h:i:s A'),
                                    'description' => $log->description,
                                    'affected_user' => $affectedUser,
                                    'affected_book' => $affectedBook,
                                    'remarks' => $remarks,
                                    'before' => $before,
                                    'after' => $after,
                                    'metadata' => $meta
                                ]) }})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                                    Inspect
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state-content">
                                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <p style="font-weight:600;font-size:1rem;color:#475569;margin-bottom:4px;">No audit logs match your criteria</p>
                                    <p style="font-size:0.82rem;color:#94a3b8;margin:0;">Try adjusting your date range, module, or search filters.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
            <div class="audit-pagination-wrap">
                {{ $logs->links() }}
            </div>
            @endif
        </div>

        {{-- PANEL 2: DAMAGE REPORTS HISTORY --}}
        <div id="panel-damage" class="audit-panel" style="display:none;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Patron</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Damage Level</th>
                            <th>Admin Notes</th>
                            <th>Resolved By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($damageHistory as $dh)
                        @php
                            $dhP    = $dh->student ?? $dh->faculty;
                            $dhN    = $dhP ? ($dhP->first_name.' '.$dhP->last_name) : '—';
                            $dhType = $dh->student_id ? 'student' : 'faculty';
                            $dhSC   = ['pending'=>'badge-amber','processed'=>'badge-green','dismissed'=>'badge-gray'][$dh->status] ?? 'badge-gray';
                        @endphp
                        <tr>
                            <td><strong>{{ $dh->book->title ?? '—' }}</strong></td>
                            <td>{{ $dhN }}</td>
                            <td><span class="badge {{ $dhType==='student'?'badge-blue':'badge-amber' }}">{{ ucfirst($dhType) }}</span></td>
                            <td><span class="badge {{ $dhSC }}">{{ ucfirst($dh->status) }}</span></td>
                            <td>{{ $dh->damage_level ? 'Level '.$dh->damage_level : '—' }}</td>
                            <td class="text-muted-sm">{{ $dh->admin_notes ?? '—' }}</td>
                            <td class="text-muted-sm">{{ $dh->adminUser?->name ?? '—' }}</td>
                            <td class="text-muted-sm">{{ $dh->updated_at?->format('M d, Y h:i A') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state-content">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <p>No damage report records found.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PANEL 3: PENALTIES HISTORY --}}
        <div id="panel-penalties" class="audit-panel" style="display:none;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patron</th>
                            <th>Type</th>
                            <th>Book Title</th>
                            <th>Penalty Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Admin Note</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penaltyHistory as $ph)
                        @php
                            $phP    = $ph->student ?? $ph->faculty;
                            $phN    = $phP ? ($phP->first_name.' '.$phP->last_name) : '—';
                            $phType = $ph->student_id ? 'student' : 'faculty';
                            $phSC   = ['pending'=>'badge-amber','paid'=>'badge-green','recorded_cash'=>'badge-green','waived'=>'badge-blue','disputed'=>'badge-red'][$ph->status] ?? 'badge-gray';
                            $phLabel= ['recorded_cash'=>'Recorded Cash','paid'=>'Paid','waived'=>'Waived','pending'=>'Pending','disputed'=>'Disputed'][$ph->status] ?? ucfirst($ph->status);
                        @endphp
                        <tr>
                            <td><strong>{{ $phN }}</strong></td>
                            <td><span class="badge {{ $phType==='student'?'badge-blue':'badge-amber' }}">{{ ucfirst($phType) }}</span></td>
                            <td>{{ $ph->book->title ?? '—' }}</td>
                            <td class="text-muted-sm">{{ ucfirst(str_replace('_',' ',$ph->penalty_type ?? '—')) }}</td>
                            <td><strong style="color:var(--pup-maroon);">₱{{ number_format($ph->amount,2) }}</strong></td>
                            <td><span class="badge {{ $phSC }}">{{ $phLabel }}</span></td>
                            <td class="text-muted-sm">{{ $ph->admin_note ?? '—' }}</td>
                            <td class="text-muted-sm">{{ $ph->created_at?->format('M d, Y h:i A') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state-content">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <p>No penalty records found.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PANEL 4: BANS & SUSPENSIONS HISTORY --}}
        <div id="panel-bans" class="audit-panel" style="display:none;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patron Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Banned At</th>
                            <th>Banned By</th>
                            <th>Unbanned At</th>
                            <th>Unbanned By</th>
                            <th>Unban Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banHistory as $bh)
                        @php
                            $bhP    = $bh->student ?? $bh->faculty;
                            $bhN    = $bhP ? ($bhP->first_name.' '.$bhP->last_name) : '—';
                            $bhType = $bh->user_type ?? ($bh->student_id ? 'student' : 'faculty');
                            $isAct  = is_null($bh->unbanned_at);
                        @endphp
                        <tr>
                            <td><strong>{{ $bhN }}</strong></td>
                            <td><span class="badge {{ $bhType==='student'?'badge-blue':'badge-amber' }}">{{ ucfirst($bhType) }}</span></td>
                            <td>
                                <span class="badge {{ $isAct ? 'badge-red' : 'badge-green' }}">
                                    {{ $isAct ? 'Active Ban' : 'Unbanned' }}
                                </span>
                            </td>
                            <td class="text-muted-sm">{{ $bh->banned_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            <td class="text-muted-sm">{{ $bh->bannedByAdmin?->name ?? 'Admin' }}</td>
                            <td class="text-muted-sm">{{ $bh->unbanned_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            <td class="text-muted-sm">{{ $bh->unbannedByAdmin?->name ?? '—' }}</td>
                            <td class="text-muted-sm" style="max-width:200px;white-space:normal;">{{ $bh->unban_reason ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state-content">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                    <p>No ban history records found.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- INSPECT AUDIT LOG MODAL                                             --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<dialog id="audit-detail-modal" class="audit-dialog">
    <div class="audit-dialog-header">
        <button type="button" onclick="document.getElementById('audit-detail-modal').close()" class="dialog-close-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <p class="dialog-eyebrow">Audit Record Details</p>
        <p id="modal-log-action" class="dialog-title">Activity Details</p>
    </div>
    <div class="audit-dialog-body">
        
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Timestamp:</span>
                <strong id="modal-log-ts" class="detail-val">—</strong>
            </div>
            <div class="detail-item">
                <span class="detail-label">Module:</span>
                <span id="modal-log-module" class="badge badge-blue">—</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Performed By:</span>
                <strong id="modal-log-performer" class="detail-val">—</strong>
            </div>
            <div class="detail-item">
                <span class="detail-label">Performer Role:</span>
                <span id="modal-log-role" class="badge badge-gray">—</span>
            </div>
            <div class="detail-item" id="modal-log-user-wrap">
                <span class="detail-label">Affected Patron / User:</span>
                <strong id="modal-log-user" class="detail-val">—</strong>
            </div>
            <div class="detail-item" id="modal-log-book-wrap">
                <span class="detail-label">Affected Book:</span>
                <span id="modal-log-book" class="detail-val" style="font-style:italic;color:#1e293b;">—</span>
            </div>
            <div class="detail-item full-width">
                <span class="detail-label">Description:</span>
                <div id="modal-log-desc" class="detail-val" style="white-space:normal;line-height:1.45;color:#1e293b;">—</div>
            </div>
            <div class="detail-item full-width" id="modal-log-remarks-wrap" style="display:none;">
                <span class="detail-label">Remarks / Notes:</span>
                <div id="modal-log-remarks" class="detail-val" style="white-space:normal;line-height:1.45;color:#475569;background:#f8fafc;padding:8px 12px;border-radius:8px;border:1px solid #e2e8f0;">—</div>
            </div>
        </div>

        {{-- Before & After and Raw Metadata sections hidden for now --}}
        <div id="modal-diff-section" style="display:none;">
            <pre id="modal-log-before"></pre>
            <pre id="modal-log-after"></pre>
        </div>
        <div id="modal-raw-section" style="display:none;">
            <pre id="modal-log-raw"></pre>
        </div>

    </div>
    <div class="audit-dialog-footer">
        <button type="button" onclick="document.getElementById('audit-detail-modal').close()" class="btn-outline">Close</button>
    </div>
</dialog>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- STYLES                                                             --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<style>
.audit-page { max-width: 1400px; margin: 0 auto; padding: 0 1rem 3rem; }
.audit-page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
.audit-page-header h2 { margin:0 0 4px; font-size:1.75rem; font-weight:800; color:var(--pup-maroon); }
.page-subtitle { color:var(--text-muted); font-size:0.92rem; margin:0; }

.live-badge { display:inline-flex; align-items:center; gap:8px; padding:6px 14px; background:#fff; border:1.5px solid #e2e8f0; border-radius:20px; font-size:0.78rem; font-weight:700; color:#334155; box-shadow:var(--shadow); }
.pulse-dot { width:8px; height:8px; border-radius:50%; background:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,0.25); animation:pulseDot 2s infinite; }
@keyframes pulseDot { 0%,100%{ opacity:1; transform:scale(1); } 50%{ opacity:0.5; transform:scale(1.2); } }

/* Stats Mini Overview */
.stats-overview { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; margin-bottom:1.5rem; }
.stat-mini-card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:1.25rem 1.5rem; display:flex; align-items:center; gap:14px; box-shadow:var(--shadow); }
.stat-icon-wrap { width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.stat-val { font-size:1.5rem; font-weight:800; color:#0f172a; line-height:1; }
.stat-lbl { font-size:0.78rem; color:#64748b; font-weight:600; margin-top:4px; }

/* Card & Tabs */
.audit-card { background:#fff; border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow); }
.audit-tabs-bar { display:flex; background:#f8fafc; border-bottom:1.5px solid var(--border); overflow-x:auto; padding:8px 16px 0; gap:6px; }
.tab-btn { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:10px 10px 0 0; border:none; background:transparent; color:#64748b; font-size:0.875rem; font-weight:600; cursor:pointer; font-family:inherit; transition:all 0.15s; border-bottom:3px solid transparent; white-space:nowrap; }
.tab-btn:hover { color:var(--pup-maroon); background:rgba(128,0,0,0.04); }
.tab-btn.active { color:var(--pup-maroon); background:#fff; font-weight:700; border-bottom:3px solid var(--pup-maroon); box-shadow:0 -2px 6px rgba(0,0,0,0.02); }
.tab-count-badge { padding:2px 8px; border-radius:12px; font-size:0.72rem; font-weight:700; background:#e2e8f0; color:#475569; }
.tab-btn.active .tab-count-badge { background:var(--pup-gold); color:var(--pup-maroon); }

/* Filter Bar */
.audit-filter-form { padding:1.25rem 1.5rem; background:#fafafa; border-bottom:1px solid var(--border); display:flex; flex-direction:column; gap:12px; }
.filter-row-presets { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.filter-label-inline { font-size:0.75rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; }
.preset-chips { display:flex; gap:4px; flex-wrap:wrap; }
.chip { padding:5px 14px; border-radius:20px; font-size:0.78rem; font-weight:600; cursor:pointer; border:1px solid #d1d5db; background:#fff; color:#475569; transition:all 0.15s; font-family:inherit; }
.chip:hover { border-color:var(--pup-maroon); color:var(--pup-maroon); }
.chip.active { background:var(--pup-maroon); color:#fff; border-color:var(--pup-maroon); font-weight:700; }

.custom-dates-bar { display:flex; align-items:center; gap:12px; padding:10px 14px; background:#f1f5f9; border-radius:10px; flex-wrap:wrap; }
.date-input-group { display:flex; align-items:center; gap:6px; font-size:0.8rem; font-weight:600; color:#334155; }
.date-field { padding:5px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.8rem; font-family:inherit; background:#fff; }
.btn-filter-apply { padding:6px 14px; border-radius:6px; background:var(--pup-maroon); color:#fff; font-size:0.78rem; font-weight:700; border:none; cursor:pointer; }

.filter-controls-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)) minmax(280px,1.5fr); gap:12px; align-items:end; }
.select-group { display:flex; flex-direction:column; gap:4px; }
.select-group label { font-size:0.72rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.4px; }
.filter-select { width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:0.84rem; font-family:inherit; background:#fff; color:#1e293b; outline:none; }
.filter-select:focus { border-color:var(--pup-maroon); }

.search-wrap-full { display:flex; flex-direction:column; gap:4px; }
.search-wrap-full label { font-size:0.72rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.4px; }
.search-input-box { position:relative; display:flex; align-items:center; }
.search-input-box svg { position:absolute; left:12px; pointer-events:none; }
.search-input-box input { width:100%; padding:8px 65px 8px 34px; border:1px solid var(--border); border-radius:8px; font-size:0.84rem; font-family:inherit; outline:none; }
.search-input-box input:focus { border-color:var(--pup-maroon); }
.btn-clear-filter { position:absolute; right:8px; font-size:0.72rem; font-weight:700; color:#dc2626; text-decoration:none; padding:3px 8px; border-radius:4px; background:#fee2e2; }

/* Table */
.data-table { width:100%; border-collapse:collapse; min-width:850px; }
.data-table th { background:var(--pup-maroon); color:#fff; padding:0.875rem 1rem; text-align:left; font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
.data-table th.col-center { text-align:center; }
.data-table td { padding:0.875rem 1rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; font-size:0.86rem; color:#1e293b; }
.data-table td.col-center { text-align:center; }
.data-table tbody tr:hover { background:#fefce8; }

/* Badges */
.badge { display:inline-block; padding:3px 9px; border-radius:14px; font-size:0.72rem; font-weight:600; white-space:nowrap; }
.badge-blue { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
.badge-green { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
.badge-red { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
.badge-amber { background:#fffbeb; color:#92400e; border:1px solid #fde68a; }
.badge-purple { background:#faf5ff; color:#6b21a8; border:1px solid #e9d5ff; }
.badge-gray { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }

.text-muted-sm { color:var(--text-muted); font-size:0.8rem; }
.empty-state-content { padding:3rem 2rem; text-align:center; color:var(--text-muted); }
.empty-state-content svg { opacity:0.35; display:block; margin:0 auto 0.75rem; }

.btn-inspect { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:6px; border:1px solid #cbd5e1; background:#fff; color:#334155; font-size:0.75rem; font-weight:600; cursor:pointer; font-family:inherit; transition:all 0.15s; }
.btn-inspect:hover { border-color:var(--pup-maroon); color:var(--pup-maroon); background:#fff5f5; }
.btn-outline { padding:8px 18px; border:1.5px solid var(--border); border-radius:8px; background:#fff; color:#374151; font-size:0.82rem; font-weight:600; cursor:pointer; font-family:inherit; }

.audit-pagination-wrap { padding:1rem 1.5rem; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
.audit-pagination-wrap nav { width: 100%; }

/* Dialog Modal */
.audit-dialog { border:none; border-radius:16px; width:calc(100% - 32px); max-width:620px; padding:0; box-shadow:0 24px 64px rgba(0,0,0,0.28); overflow:hidden; margin:auto; }
.audit-dialog::backdrop { background:rgba(0,0,0,0.45); }
.audit-dialog-header { background: linear-gradient(135deg, var(--pup-maroon-dark, #5a0000) 0%, var(--pup-maroon) 100%); padding:24px 28px 20px; position:relative; }
.dialog-close-btn { position:absolute; top:14px; right:14px; background:rgba(255,255,255,0.15); border:none; border-radius:50%; width:32px; height:32px; cursor:pointer; color:#fff; display:flex; align-items:center; justify-content:center; }
.dialog-eyebrow { margin:0 0 3px; font-size:0.68rem; font-weight:600; color:rgba(255,255,255,0.7); text-transform:uppercase; letter-spacing:0.5px; }
.dialog-title { margin:0; font-size:1.15rem; font-weight:700; color:#fff; padding-right:36px; line-height:1.3; }
.audit-dialog-body { padding:22px 28px; max-height:70vh; overflow-y:auto; }
.audit-dialog-footer { padding:14px 28px 20px; display:flex; justify-content:flex-end; border-top:1px solid #e2e8f0; }

.detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px 16px; }
.detail-item { display:flex; flex-direction:column; gap:3px; }
.detail-item.full-width { grid-column:1 / -1; }
.detail-label { font-size:0.72rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.4px; }
.detail-val { font-size:0.875rem; color:#0f172a; }

@media(max-width:768px) {
    .filter-controls-grid { grid-template-columns:1fr; }
    .stats-overview { grid-template-columns:1fr; }
}
</style>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- JAVASCRIPT                                                         --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<script>
function switchAuditTab(tabId, btn) {
    document.querySelectorAll('.audit-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.audit-tabs-bar .tab-btn').forEach(b => b.classList.remove('active'));

    const panel = document.getElementById('panel-' + tabId);
    if (panel) panel.style.display = 'block';
    if (btn) btn.classList.add('active');
}

function setDatePreset(preset) {
    document.getElementById('date_preset_input').value = preset;
    document.querySelectorAll('.preset-chips .chip').forEach(c => c.classList.remove('active'));
    
    if (preset === 'custom') {
        document.getElementById('custom-dates-wrap').style.display = 'flex';
    } else {
        document.getElementById('custom-dates-wrap').style.display = 'none';
        document.getElementById('auditFilterForm').submit();
    }
}

function toggleCustomDates() {
    const wrap = document.getElementById('custom-dates-wrap');
    const showing = wrap.style.display !== 'none';
    wrap.style.display = showing ? 'none' : 'flex';
    if (!showing) {
        document.getElementById('date_preset_input').value = 'custom';
        document.querySelectorAll('.preset-chips .chip').forEach(c => c.classList.remove('active'));
    }
}

function openLogModal(log) {
    document.getElementById('modal-log-action').textContent = log.action || 'Activity Record';
    document.getElementById('modal-log-ts').textContent = log.timestamp || '—';
    document.getElementById('modal-log-module').textContent = log.module || 'General';
    document.getElementById('modal-log-performer').textContent = log.performer || '—';
    document.getElementById('modal-log-role').textContent = log.performer_type || 'Admin';
    document.getElementById('modal-log-desc').textContent = log.description || '—';

    // Affected User
    const userWrap = document.getElementById('modal-log-user-wrap');
    if (log.affected_user) {
        userWrap.style.display = 'flex';
        document.getElementById('modal-log-user').textContent = log.affected_user;
    } else {
        userWrap.style.display = 'none';
    }

    // Affected Book
    const bookWrap = document.getElementById('modal-log-book-wrap');
    if (log.affected_book) {
        bookWrap.style.display = 'flex';
        document.getElementById('modal-log-book').textContent = '📖 ' + log.affected_book;
    } else {
        bookWrap.style.display = 'none';
    }

    // Remarks / Notes
    const remarksWrap = document.getElementById('modal-log-remarks-wrap');
    const meta = log.metadata || {};
    const remarks = log.remarks || meta.remarks || meta.reason || meta.notes;
    if (remarks) {
        remarksWrap.style.display = 'block';
        document.getElementById('modal-log-remarks').textContent = remarks;
    } else {
        remarksWrap.style.display = 'none';
    }

    document.getElementById('audit-detail-modal').showModal();
}
</script>
@endsection
