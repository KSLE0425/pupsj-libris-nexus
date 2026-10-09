@extends('layouts.admin')

@section('content')
<div class="ops-page">

    {{-- ── PAGE HEADER ───────────────────────────────────────────────── --}}
    <div class="ops-page-header">
        <div>
            <h2>Library Transactions</h2>
            <p class="page-subtitle">Unified kiosk return reviews, damage assessments, penalties, and warning/ban oversight.</p>
        </div>
        <a href="{{ route('admin.book.suggestions') }}" class="ops-pill-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            Book Recommendations
        </a>
    </div>

    {{-- ── TYPE TABS ─────────────────────────────────────────────────── --}}
    <div class="type-tabs">
        <button class="type-tab active" data-tab="damage" onclick="switchOpTab('damage', this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span>Returns &amp; Damage Assessment</span>
            <span class="tab-count">{{ ($pendingKioskReturns->count() ?? 0) + ($damageReports->count() ?? 0) + ($pendingPenalties->count() ?? 0) }}</span>
        </button>
        <button class="type-tab" data-tab="overdue" onclick="switchOpTab('overdue', this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>
            </svg>
            <span>Overdue Borrows</span>
            <span class="tab-count">{{ $overdueBorrows->count() }}</span>
        </button>
        <button class="type-tab" data-tab="banned" onclick="switchOpTab('banned', this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
            <span>Banned &amp; Suspended</span>
            <span class="tab-count">{{ ($restrictedPatrons ?? $bannedPatrons)->count() }}</span>
        </button>
        <button class="type-tab" data-tab="config" onclick="switchOpTab('config', this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            <span>Configuration</span>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- PANEL 1: MERGED KIOSK RETURNS & DAMAGE ASSESSMENT MODULE (Req #3) --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div id="op-panel-damage" class="op-panel">
        
        {{-- Unified Overview Header --}}
        <div class="card" style="margin-bottom:1.5rem; background:linear-gradient(135deg, #fafafa 0%, #ffffff 100%); border-left:4px solid var(--pup-maroon); padding:16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div>
                    <h4 style="margin:0; font-size:1.05rem; font-weight:700; color:var(--pup-maroon);">Unified Return &amp; Condition Management</h4>
                    <p style="margin:2px 0 0; font-size:0.82rem; color:var(--text-muted);">Verify books returned via kiosk, assess condition reports, and manage resulting penalties in one flow.</p>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <span style="background:#dcfce7; color:#166534; font-size:0.75rem; font-weight:700; padding:4px 10px; border-radius:12px; border:1px solid #bbf7d0;">
                        {{ $pendingKioskReturns->count() ?? 0 }} Kiosk Returns
                    </span>
                    <span style="background:#fef3c7; color:#92400e; font-size:0.75rem; font-weight:700; padding:4px 10px; border-radius:12px; border:1px solid #fde68a;">
                        {{ $damageReports->count() ?? 0 }} Damage Reports
                    </span>
                    <span style="background:#fee2e2; color:#991b1b; font-size:0.75rem; font-weight:700; padding:4px 10px; border-radius:12px; border:1px solid #fecaca;">
                        {{ $pendingPenalties->count() ?? 0 }} Pending Penalties
                    </span>
                </div>
            </div>
        </div>

        {{-- FLOW SECTION 1: KIOSK RETURNS CONDITION CHECK --}}
        @if(isset($pendingKioskReturns) && $pendingKioskReturns->count())
        <div class="card" style="margin-bottom:1.5rem; border-top:4px solid #16a34a;">
            <div class="card-header">
                <div>
                    <h4>Kiosk Returns — Condition Check</h4>
                    <p class="card-sub">Books returned at the self-service kiosk. Verify physical condition before returning to circulation shelves.</p>
                </div>
                <span id="kiosk-pending-badge" class="card-badge" style="background:#dcfce7;color:#166534;font-weight:700;">{{ $pendingKioskReturns->count() }} awaiting check</span>
            </div>
            <div id="kiosk-returns-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;padding:20px;">
                @foreach($pendingKioskReturns as $usage)
                @php $patron = $usage->student ?? $usage->faculty; @endphp
                <div class="kiosk-return-card" style="background:#fff;border:1.5px solid #e5e7eb;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;">
                    <div style="padding:18px 18px 14px;text-align:center;border-bottom:1px solid #f0f0f0;">
                        <div style="width:48px;height:48px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div style="font-weight:700;font-size:0.92rem;color:#111;margin-bottom:4px;line-height:1.3;">
                            {{ $usage->book->title ?? 'Unknown Book' }}
                        </div>
                        @if(!empty($usage->book->accession_number))
                            <div style="font-size:0.72rem;color:#800000;font-family:monospace;font-weight:700;">Acc: {{ $usage->book->accession_number }}</div>
                        @endif
                        <div style="font-size:0.78rem;color:#6b7280;margin-top:4px;">
                            {{ $patron ? $patron->first_name . ' ' . $patron->last_name : 'Unknown patron' }}
                            @if($usage->student)
                                <small>({{ $usage->student->student_number ?? 'Student' }})</small>
                            @elseif($usage->faculty)
                                <small>({{ $usage->faculty->employee_id ?? 'Faculty' }})</small>
                            @endif
                        </div>
                    </div>
                    <div style="padding:12px 14px;display:flex;gap:8px;background:#fafafa;">
                        <button type="button" onclick="acknowledgeKioskAllGood(this, '{{ route('admin.operations.return.acknowledge', $usage) }}')" class="action-btn approve" style="flex:1;justify-content:center;padding:8px 10px;font-weight:700;">All Good</button>
                        <button type="button" onclick="openIssueModal('{{ route('admin.operations.return.acknowledge', $usage) }}', '{{ addslashes($usage->book->title ?? 'Unknown Book') }}')" class="action-btn reject" style="flex:1;justify-content:center;padding:8px 10px;font-weight:700;">Report Issue</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- FLOW SECTION 2: PENDING DAMAGE REPORTS & ASSESSMENT QUEUE --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <h4>Pending Damage Reports &amp; Condition Issues</h4>
                    <p class="card-sub">Assessed returns and condition flags awaiting damage level assessment and penalty calculation.</p>
                </div>
                <span class="card-badge">{{ $damageReports->count() }} waiting assessment</span>
            </div>

            <div class="card-search-bar">
                <div class="search-wrap">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="pendingDamageSearch" placeholder="Search by book title or patron name..." oninput="debouncedFilterPendingDamage()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Patron</th>
                            <th>Type</th>
                            <th>Returned At</th>
                            <th>Issue / Condition Flags</th>
                            <th class="col-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pendingDamageBody">
                        @forelse($damageReports as $i => $report)
                        @php
                            $patron    = $report->student ?? $report->faculty;
                            $pType     = $report->student ? 'student' : 'faculty';
                            $pName     = $patron ? ($patron->first_name . ' ' . $patron->last_name) : '—';
                            $retAt     = $report->bookUsage?->time_out?->format('M j, Y g:i A') ?? '—';
                            $warnCount = $patron?->damage_warning_count ?? 0;
                            $isBanned  = (bool)($patron?->is_banned ?? false);
                        @endphp
                        <tr data-book="{{ strtolower($report->book->title ?? '') }}" data-patron="{{ strtolower($pName) }}">
                            <td>
                                <strong>{{ $report->book->title ?? 'Unknown Book' }}</strong>
                                @if($isBanned)
                                    <span class="badge badge-red" style="margin-left:6px;">BANNED</span>
                                @elseif($warnCount > 0)
                                    <span class="badge badge-yellow" style="margin-left:6px;">⚠ {{ $warnCount }} {{ $warnCount===1?'warning':'warnings' }}</span>
                                @endif
                            </td>
                            <td>{{ $pName }}</td>
                            <td>
                                <span class="badge {{ $pType==='student' ? 'badge-blue' : 'badge-amber' }}">{{ ucfirst($pType) }}</span>
                            </td>
                            <td class="text-muted-sm">{{ $retAt }}</td>
                            <td class="text-muted-sm">
                                <div>{{ $report->patron_note ?? 'Reported Issue' }}</div>
                                @if($report->admin_notes)
                                    <small style="color:#6b7280;display:block;margin-top:2px;">Notes: {{ $report->admin_notes }}</small>
                                @endif
                            </td>
                            <td class="col-center">
                                <div class="actions-wrapper">
                                    <button type="button" class="action-btn reject" onclick="toggleDmgActions({{ $report->id }})">Assess / Resolve</button>
                                    <button type="button" class="action-btn" onclick="toggleDmgDismiss({{ $report->id }})">Dismiss</button>
                                </div>
                            </td>
                        </tr>
                        <tr id="dmg-actions-{{ $report->id }}" style="display:none;">
                            <td colspan="6" class="expand-panel">
                                <form method="POST" action="{{ route('admin.operations.damage.resolve', $report) }}">
                                    @csrf
                                    <div class="expand-grid">
                                        <div class="form-group">
                                            <label>Damage Level <span class="required">*</span></label>
                                            <select name="damage_level" required onchange="updateDmgFee(this, {{ $report->id }})">
                                                <option value="" disabled selected>Select damage severity...</option>
                                                @foreach($feeLevels as $lvl => $meta)
                                                <option value="{{ $lvl }}" data-fee="{{ $meta['default_amount'] }}">
                                                    {{ $lvl }} — {{ $meta['label'] }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Fee Amount (₱)</label>
                                            <input id="dmg-fee-{{ $report->id }}" type="number" step="0.01" name="amount" min="0" placeholder="Leave blank for default">
                                        </div>
                                        <div class="form-group">
                                            <label>Suspend Borrowing (days)</label>
                                            <input type="number" name="suspend_borrowing_days" value="0" min="0" max="365">
                                        </div>
                                        <div class="form-group">
                                            <label>Admin Assessment Notes</label>
                                            <input type="text" name="admin_notes" placeholder="Optional notes regarding condition assessment">
                                        </div>
                                    </div>
                                    <div class="expand-footer">
                                        <label class="checkbox-label text-maroon">
                                            <input type="checkbox" name="condemn_book" value="1"> Condemn this book
                                        </label>
                                        <label class="checkbox-label text-amber">
                                            <input type="checkbox" name="record_warning" value="1" {{ $isBanned ? 'disabled' : 'checked' }}> ⚠ Record Warning (Auto-bans on 3, 4, 5...)
                                        </label>
                                        <button type="submit" class="btn-maroon" style="margin-left:auto;">Apply Penalty &amp; Record</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr id="dmg-dismiss-{{ $report->id }}" style="display:none;">
                            <td colspan="6" class="expand-panel expand-panel-light">
                                <form method="POST" action="{{ route('admin.operations.damage.dismiss', $report) }}" class="expand-inline">
                                    @csrf
                                    <input type="text" name="admin_notes" placeholder="Dismiss reason / explanation (optional)" style="flex:1;">
                                    <button type="submit" class="action-btn">Confirm Dismiss</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state-content">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="20 6 9 17 4 12"/></svg>
                                    <p>No pending damage reports. All returned items have been verified.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="pendingDamageNoResults" style="display:none;" class="empty-state-content">
                <p>No damage reports match your search.</p>
            </div>
        </div>

        {{-- FLOW SECTION 3: RESULTING PENALTIES & FINES SECTION --}}
        <div class="card" style="margin-top:1.5rem;border-top:4px solid var(--pup-maroon);">
            <div class="card-header">
                <div>
                    <h4>Pending Penalties &amp; Fines</h4>
                    <p class="card-sub">Assessed damages, manual penalties, and overdue fines awaiting payment or resolution.</p>
                </div>
                <span class="card-badge" style="background:#fee2e2;color:#991b1b;font-weight:700;">{{ $pendingPenalties->count() }} pending payment</span>
            </div>

            <div class="card-search-bar">
                <div class="search-wrap">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="pendingPenaltySearch" placeholder="Search by patron name, book title, or penalty type..." oninput="debouncedFilterPendingPenalties()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patron</th>
                            <th>Type</th>
                            <th>Book Title</th>
                            <th>Penalty Type</th>
                            <th>Amount</th>
                            <th>Issued Date</th>
                            <th>Notes / Reason</th>
                            <th class="col-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pendingPenaltyBody">
                        @forelse($pendingPenalties as $penalty)
                        @php
                            $pPatron = $penalty->student ?? $penalty->faculty;
                            $pType   = $penalty->student_id ? 'student' : 'faculty';
                            $pName   = $pPatron ? ($pPatron->first_name . ' ' . $pPatron->last_name) : '—';
                            $bTitle  = $penalty->book->title ?? 'General/Usage';
                            $amtFmt  = '₱' . number_format($penalty->amount, 2);
                        @endphp
                        <tr data-patron="{{ strtolower($pName) }}" data-book="{{ strtolower($bTitle) }}" data-type="{{ strtolower($penalty->penalty_type) }}">
                            <td>
                                <strong>{{ $pName }}</strong>
                                @if(($pPatron->damage_warning_count ?? 0) > 0)
                                    <span class="badge badge-yellow" style="margin-left:4px;font-size:0.68rem;">⚠ {{ $pPatron->damage_warning_count }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $pType==='student' ? 'badge-blue' : 'badge-amber' }}">{{ ucfirst($pType) }}</span>
                            </td>
                            <td>{{ $bTitle }}</td>
                            <td>
                                <span class="badge badge-yellow">{{ ucfirst(str_replace('_', ' ', $penalty->penalty_type)) }}</span>
                            </td>
                            <td><strong class="text-maroon">{{ $amtFmt }}</strong></td>
                            <td class="text-muted-sm">{{ $penalty->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td class="text-muted-sm" style="max-width:220px;white-space:normal;">{{ $penalty->admin_note ?? $penalty->patron_note ?? '—' }}</td>
                            <td class="col-center">
                                <button type="button" class="action-btn approve" style="font-weight:700;"
                                    onclick="openResolvePenaltyModal('{{ route('admin.operations.penalty.status', $penalty->id) }}', '{{ addslashes($pName) }}', '{{ addslashes($bTitle) }}', '{{ number_format($penalty->amount, 2) }}', '{{ ucfirst(str_replace('_', ' ', $penalty->penalty_type)) }}', '{{ addslashes($penalty->admin_note ?? '') }}')">
                                    Resolve / Paid
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state-content">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="20 6 9 17 4 12"/></svg>
                                    <p>No pending penalties or fines.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="pendingPenaltyNoResults" style="display:none;" class="empty-state-content">
                <p>No penalties match your search.</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- PANEL 2: OVERDUE BORROWS                                        --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div id="op-panel-overdue" class="op-panel" style="display:none;">
        <div class="card">
            <div class="card-header">
                <div>
                    <h4>Overdue Borrows</h4>
                    <p class="card-sub">Active overdue borrow transactions requiring follow-up or overdue fines.</p>
                </div>
                <span class="card-badge">{{ $overdueBorrows->count() }} overdue</span>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Patron</th>
                            <th>Type</th>
                            <th>Borrowed Date</th>
                            <th class="col-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overdueBorrows as $ob)
                        @php $obP = $ob->student ?? $ob->faculty; @endphp
                        <tr>
                            <td><strong>{{ $ob->book->title ?? '—' }}</strong></td>
                            <td>{{ $obP ? ($obP->first_name.' '.$obP->last_name) : '—' }}</td>
                            <td><span class="badge {{ $ob->student ? 'badge-blue' : 'badge-amber' }}">{{ $ob->student ? 'Student' : 'Faculty' }}</span></td>
                            <td class="text-muted-sm">{{ $ob->time_in ? \Carbon\Carbon::parse($ob->time_in)->format('M j, Y') : '—' }}</td>
                            <td class="col-center">
                                <div class="actions-wrapper">
                                    <form method="POST" action="{{ route('admin.operations.apply-overdue-fine') }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="usage_id" value="{{ $ob->id }}">
                                        <button type="submit" class="action-btn reject">Apply Fine</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.operations.mark-overdue-resolved') }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="usage_id" value="{{ $ob->id }}">
                                        <button type="submit" class="action-btn approve">Mark Resolved</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5">
                            <div class="empty-state-content">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                                <p>No overdue borrows at this time.</p>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- PANEL 3: BANNED, SUSPENDED & WARNED PATRONS (Req #4 & #5)       --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div id="op-panel-banned" class="op-panel" style="display:none;">
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-header">
                <div>
                    <h4>Banned, Suspended &amp; Warned Patrons</h4>
                    <p class="card-sub">Patrons currently restricted from borrowing or with accumulated warning counts (Warnings never reset).</p>
                </div>
                <span class="card-badge tab-badge-red" style="background:#fee2e2;color:#991b1b;font-weight:700;">{{ ($restrictedPatrons ?? $bannedPatrons)->count() }} listed</span>
            </div>

            {{-- Filter Chips for Banned / Suspended / Warned --}}
            <div class="card-search-bar" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div class="search-wrap" style="flex:1; max-width:320px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="restrictedPatronsSearch" placeholder="Search patron name..." oninput="debouncedFilterRestrictedPatrons()">
                </div>
                <div class="chip-group">
                    <button type="button" class="chip active" data-filter="all" onclick="filterRestrictedByKind('all', this)">All ({{ ($restrictedPatrons ?? $bannedPatrons)->count() }})</button>
                    <button type="button" class="chip" data-filter="banned" onclick="filterRestrictedByKind('banned', this)">Banned ({{ $bannedPatrons->count() }})</button>
                    <button type="button" class="chip" data-filter="suspended" onclick="filterRestrictedByKind('suspended', this)">Suspended ({{ $suspendedPatrons->count() }})</button>
                    <button type="button" class="chip" data-filter="warned" onclick="filterRestrictedByKind('warned', this)">With Warnings ({{ $warnedPatrons->count() ?? 0 }})</button>
                </div>
            </div>

            @php $mixedPatrons = $restrictedPatrons ?? $bannedPatrons; @endphp
            @if($mixedPatrons->isEmpty())
                <div class="empty-state-content">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    <p>No currently banned, suspended, or warned patrons.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Patron Name</th>
                                <th>Type</th>
                                <th>Warning Count</th>
                                <th>Status</th>
                                <th>Restriction Details</th>
                                <th class="col-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="restrictedPatronsBody">
                            @foreach($mixedPatrons as $bnp)
                            @php 
                                $kind = $bnp['kind'] ?? 'banned'; 
                                $pName = $bnp['model']->first_name . ' ' . $bnp['model']->last_name;
                                $wCount = $bnp['model']->damage_warning_count ?? 0;
                            @endphp
                            <tr data-name="{{ strtolower($pName) }}" data-kind="{{ $kind }}">
                                <td>
                                    <strong>{{ $pName }}</strong>
                                    <div class="text-muted-sm">{{ $bnp['type'] === 'student' ? ($bnp['model']->student_number ?? 'Student') : ($bnp['model']->employee_id ?? 'Faculty') }}</div>
                                </td>
                                <td><span class="badge {{ $bnp['type']==='student' ? 'badge-blue' : 'badge-amber' }}">{{ ucfirst($bnp['type']) }}</span></td>
                                <td>
                                    @if($wCount > 0)
                                        <span class="badge {{ $wCount >= 3 ? 'badge-red' : 'badge-yellow' }}" style="font-weight:700;">
                                            ⚠ {{ $wCount }} {{ $wCount === 1 ? 'warning' : 'warnings' }}
                                        </span>
                                    @else
                                        <span class="text-muted-sm">0 warnings</span>
                                    @endif
                                </td>
                                <td>
                                    @if($kind === 'suspended')
                                        <span class="badge badge-amber">Suspended</span>
                                    @elseif($kind === 'banned')
                                        <span class="badge badge-red">Banned</span>
                                    @else
                                        <span class="badge badge-yellow">Warned (Active)</span>
                                    @endif
                                </td>
                                <td class="text-muted-sm">
                                    @if($kind === 'suspended')
                                        Suspended until {{ optional($bnp['model']->borrowing_suspended_until)->format('M d, Y g:i A') ?? '—' }}
                                    @elseif($kind === 'banned')
                                        Banned since {{ $bnp['ban']?->banned_at?->format('M d, Y') ?? 'Recently' }}
                                        @if($bnp['ban']?->reason)
                                            — {{ $bnp['ban']->reason }}
                                        @else
                                            — Warning threshold ban (Count: {{ $wCount }})
                                        @endif
                                    @else
                                        Accumulated {{ $wCount }} warning(s). Next warning at threshold will trigger auto-ban.
                                    @endif
                                </td>
                                <td class="col-center">
                                    @if($kind === 'suspended')
                                        <form method="POST" action="{{ route('admin.operations.unsuspend') }}" style="display:inline;" onsubmit="return confirm('Unsuspend this patron and restore borrowing privilege?')">
                                            @csrf
                                            <input type="hidden" name="user_type" value="{{ $bnp['type'] }}">
                                            <input type="hidden" name="user_id" value="{{ $bnp['model']->id }}">
                                            <button type="submit" class="action-btn approve" style="font-weight:700;">Unsuspend</button>
                                        </form>
                                    @elseif($kind === 'banned')
                                        <button type="button" class="action-btn approve" style="font-weight:700;" onclick="openUnbanModal('{{ $bnp['type'] }}', {{ $bnp['model']->id }}, '{{ addslashes($pName) }}', {{ $wCount }})">Unban</button>
                                    @else
                                        <button type="button" class="action-btn reject" onclick="openBanModal('{{ $bnp['type'] }}', {{ $bnp['model']->id }}, '{{ addslashes($pName) }}')">Ban Patron</button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Ban History Card (Req #5 - Connected with Audit Log) --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <h4>Ban History Log</h4>
                    <p class="card-sub">Complete historical record of all patron bans, auto-ban triggers, and unban justifications (synchronized with System Audit Log).</p>
                </div>
                <span class="card-badge">{{ $banHistory->count() }} records</span>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patron</th>
                            <th>Type</th>
                            <th>Warnings at Ban</th>
                            <th>Banned At</th>
                            <th>Ban Trigger / Reason</th>
                            <th>Status</th>
                            <th>Unbanned At</th>
                            <th>Unban Justification</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banHistory as $bh)
                        @php $bhP = $bh->student ?? $bh->faculty; @endphp
                        <tr>
                            <td><strong>{{ $bhP ? ($bhP->first_name.' '.$bhP->last_name) : '—' }}</strong></td>
                            <td><span class="badge {{ $bh->student_id ? 'badge-blue' : 'badge-amber' }}">{{ $bh->student_id ? 'Student' : 'Faculty' }}</span></td>
                            <td>
                                <span class="badge badge-yellow" style="font-weight:700;">⚠ {{ $bh->warning_count_at_ban ?? '—' }}</span>
                            </td>
                            <td class="text-muted-sm">{{ $bh->banned_at?->format('M d, Y g:i A') ?? '—' }}</td>
                            <td class="text-muted-sm" style="max-width:200px;white-space:normal;">
                                {{ $bh->reason ?? ($bh->warning_count_at_ban >= 3 ? "Auto-ban triggered (Warning #{$bh->warning_count_at_ban})" : "Administrative Ban") }}
                            </td>
                            <td>
                                @if($bh->unbanned_at)
                                    <span class="badge badge-green">Unbanned</span>
                                @else
                                    <span class="badge badge-red">Active Ban</span>
                                @endif
                            </td>
                            <td class="text-muted-sm">{{ $bh->unbanned_at?->format('M d, Y g:i A') ?? '—' }}</td>
                            <td class="text-muted-sm" style="max-width:200px;white-space:normal;">{{ $bh->unban_reason ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8">
                            <div class="empty-state-content">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <p>No ban records yet.</p>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- PANEL 4: LIBRARY CONFIGURATION (PUP THEMED)                     --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div id="op-panel-config" class="op-panel" style="display:none;">
        <div class="card">
            <div class="card-header">
                <div>
                    <h4>Library &amp; Ban Configuration</h4>
                    <p class="card-sub">Global penalty rates, warning auto-ban rules, ban durations, and borrowing policies.</p>
                </div>
                <span class="card-badge">System Settings</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    
                    <h5 style="margin:0 0 12px; color:var(--pup-maroon); font-size:0.95rem; font-weight:700;">Financial &amp; Circulation Parameters</h5>
                    <div class="cfg-grid">
                        <div class="form-group">
                            <label for="cfg_damage">Damage Penalty Base Amount (₱)</label>
                            <input id="cfg_damage" type="number" name="damage_penalty_amount" min="0" step="0.01" value="{{ $libraryConfig['damage_penalty_amount'] }}">
                        </div>
                        <div class="form-group">
                            <label for="cfg_lost">Lost Book Penalty Base Amount (₱)</label>
                            <input id="cfg_lost" type="number" name="lost_penalty_amount" min="0" step="0.01" value="{{ $libraryConfig['lost_penalty_amount'] }}">
                        </div>
                        <div class="form-group">
                            <label for="cfg_borrow_days">Max Borrow Days (Students)</label>
                            <input id="cfg_borrow_days" type="number" name="max_borrow_days_student" min="1" value="{{ $libraryConfig['max_borrow_days_student'] }}">
                        </div>
                    </div>

                    <h5 style="margin:20px 0 12px; color:var(--pup-maroon); font-size:0.95rem; font-weight:700;">Warning &amp; Ban Policies</h5>
                    <div class="cfg-grid">
                        <div class="form-group">
                            <label for="cfg_warning_threshold">Auto-Ban Warning Threshold (warnings)</label>
                            <input id="cfg_warning_threshold" type="number" name="warning_ban_threshold" min="1" max="10" value="{{ $libraryConfig['warning_ban_threshold'] ?? 3 }}">
                            <small style="color:var(--text-muted);font-size:0.75rem;">Default: 3. Reaching this count auto-bans patron. Any warning thereafter triggers auto-ban again upon issuance.</small>
                        </div>
                        <div class="form-group">
                            <label for="cfg_ban_days">Default Ban / Suspension Duration (days)</label>
                            <input id="cfg_ban_days" type="number" name="ban_duration_days" min="0" max="365" value="{{ $libraryConfig['ban_duration_days'] ?? 0 }}">
                            <small style="color:var(--text-muted);font-size:0.75rem;">Enter <strong>0</strong> for Permanent / Indefinite Ban until manually unbanned by librarian.</small>
                        </div>
                    </div>

                    <div class="cfg-checks" style="margin-top:20px;">
                        {{-- Global Student Take-Home Toggle --}}
                        <div class="check-card check-card-blue" style="border:1.5px solid #bae6fd;background:#f0f9ff;border-radius:12px;padding:16px;">
                            <div class="check-row" style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                                <input type="checkbox" id="cfg_student_take_home" name="student_take_home_allowed" value="1"
                                    {{ ($libraryConfig['student_take_home_allowed'] ?? '1') === '1' ? 'checked' : '' }}>
                                <label for="cfg_student_take_home" style="font-weight:700;color:#0369a1;font-size:0.95rem;cursor:pointer;">
                                    Allow Students to Take Books Home
                                </label>
                            </div>
                            <p style="font-size:0.82rem;color:#475569;margin:0;line-height:1.45;">
                                When checked, students are allowed to borrow books for take-home use up to the max borrow duration. When unchecked, students are restricted to <strong>in-library use only</strong> (must return books on the same day). Faculty are not affected by this setting.
                            </p>
                        </div>

                        <div class="check-card check-card-amber" style="border:1.5px solid #fde68a;background:#fffdf5;border-radius:12px;padding:16px;">
                            <div class="check-row">
                                <input type="checkbox" id="cfg_fines" name="overdue_fines_enabled" value="1"
                                    {{ ($libraryConfig['overdue_fines_enabled'] ?? '0') === '1' ? 'checked' : '' }}
                                    onchange="document.getElementById('overdueFineRates').style.display=this.checked?'grid':'none'">
                                <label for="cfg_fines" style="font-weight:700;color:#92400e;font-size:0.95rem;cursor:pointer;">Enable Overdue Fines</label>
                            </div>
                            <p style="font-size:0.82rem;color:#475569;margin:0;line-height:1.45;">Automatically calculate and charge daily fines for overdue items.</p>
                            <div id="overdueFineRates" style="display:{{ ($libraryConfig['overdue_fines_enabled'] ?? '0') === '1' ? 'grid' : 'none' }};grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
                                <div class="form-group">
                                    <label for="cfg_fine_student">Fine Per Day – Student (₱)</label>
                                    <input id="cfg_fine_student" type="number" name="overdue_fine_per_day_student" min="0" step="0.01" value="{{ $libraryConfig['overdue_fine_per_day_student'] ?? 10 }}">
                                </div>
                                <div class="form-group">
                                    <label for="cfg_fine_faculty">Fine Per Day – Faculty (₱)</label>
                                    <input id="cfg_fine_faculty" type="number" name="overdue_fine_per_day_faculty" min="0" step="0.01" value="{{ $libraryConfig['overdue_fine_per_day_faculty'] ?? 20 }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;margin-top:1.5rem;">
                        <button type="submit" class="btn-maroon" style="padding:11px 28px;font-size:0.92rem;font-weight:700;">Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- DEBUG: Simulate Overdue Borrow --}}
        <div class="card" style="margin-top:1.5rem;border:2px dashed #fda4af;">
            <div class="card-header" style="background:#fff1f2;">
                <div>
                    <h4 style="color:#be123c;">🧪 Debug: Simulate Overdue Borrow</h4>
                    <p class="card-sub">Creates an active borrow for the selected book and patron that is already past its due date. Use it to test overdue flagging and fines.</p>
                </div>
                <span class="card-badge" style="background:#ffe4e6;color:#be123c;">Testing only</span>
            </div>
            <div class="card-body">
                @if($errors->has('book_id') || $errors->has('patron_id') || $errors->has('days_overdue') || $errors->has('patron_type'))
                    <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:0.85rem;">
                        {{ $errors->first('book_id') ?: ($errors->first('patron_id') ?: ($errors->first('patron_type') ?: $errors->first('days_overdue'))) }}
                    </div>
                @endif
                <form method="POST" action="{{ route('admin.operations.debug-overdue') }}" id="debugOverdueForm">
                    @csrf
                    <div class="cfg-grid">
                        <div class="form-group">
                            <label for="dbg_book">Book</label>
                            <select id="dbg_book" name="book_id" required>
                                <option value="">— Select a book —</option>
                                @foreach($debugBooks as $b)
                                    <option value="{{ $b->id }}" @selected(old('book_id') == $b->id)>{{ $b->title }}{{ $b->barcode ? ' · ' . $b->barcode : '' }} ({{ $b->copies }} {{ $b->copies == 1 ? 'copy' : 'copies' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Patron Type</label>
                            <div style="display:flex;gap:16px;align-items:center;min-height:42px;">
                                <label style="display:flex;align-items:center;gap:6px;font-weight:600;text-transform:none;letter-spacing:0;cursor:pointer;margin:0;">
                                    <input type="radio" name="patron_type" value="student" {{ old('patron_type', 'student') === 'student' ? 'checked' : '' }} onchange="toggleDebugPatron()"> Student
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;font-weight:600;text-transform:none;letter-spacing:0;cursor:pointer;margin:0;">
                                    <input type="radio" name="patron_type" value="faculty" {{ old('patron_type') === 'faculty' ? 'checked' : '' }} onchange="toggleDebugPatron()"> Faculty
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="dbg_days">Days Overdue</label>
                            <input id="dbg_days" type="number" name="days_overdue" min="1" max="365" value="{{ old('days_overdue', 3) }}" required>
                        </div>
                    </div>
                    <div class="cfg-grid" style="margin-top:12px;">
                        <div class="form-group" id="dbg_student_wrap">
                            <label for="dbg_student">Student</label>
                            <select id="dbg_student" name="patron_id" required>
                                <option value="">— Select a student —</option>
                                @foreach($debugStudents as $s)
                                    <option value="{{ $s->id }}" @selected(old('patron_type', 'student') === 'student' && old('patron_id') == $s->id)>{{ $s->last_name }}, {{ $s->first_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="dbg_faculty_wrap" hidden>
                            <label for="dbg_faculty">Faculty</label>
                            <select id="dbg_faculty" name="patron_id" required disabled>
                                <option value="">— Select a faculty member —</option>
                                @foreach($debugFaculty as $f)
                                    <option value="{{ $f->id }}" @selected(old('patron_type') === 'faculty' && old('patron_id') == $f->id)>{{ $f->last_name }}, {{ $f->first_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <p style="font-size:0.78rem;color:var(--text-muted);margin:10px 0 0;">
                        The borrow date is set to (days overdue + max borrow days) ago, and the due date to the chosen number of days ago. The book's available copies go down by 1, the same as a real kiosk borrow. The record is tagged <strong>[DEBUG]</strong> in its remarks.
                    </p>
                    <div style="display:flex;justify-content:flex-end;margin-top:1.25rem;">
                        <button type="submit" class="btn-maroon" style="padding:11px 28px;font-size:0.92rem;font-weight:700;">Create Overdue Borrow</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
        function toggleDebugPatron() {
            const isFaculty = document.querySelector('#debugOverdueForm input[name="patron_type"]:checked')?.value === 'faculty';
            const sWrap = document.getElementById('dbg_student_wrap'), fWrap = document.getElementById('dbg_faculty_wrap');
            sWrap.hidden = isFaculty;
            fWrap.hidden = !isFaculty;
            document.getElementById('dbg_student').disabled = isFaculty;
            document.getElementById('dbg_faculty').disabled = !isFaculty;
        }
        document.addEventListener('DOMContentLoaded', toggleDebugPatron);
        </script>
    </div>

</div>{{-- /ops-page --}}

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- MODALS                                                             --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
@php
$issueTypes = [
    'warning' => 'Warning (Issue Formal Warning)',
    'lost'    => 'Lost (Lost Book Charge)',
    'damaged' => 'Damaged (Physical / Page Damage)',
    'others'  => 'Others (Special / Missing Notes)',
];
@endphp
<dialog id="issue-modal" class="ops-dialog">
    <div class="ops-dialog-header">
        <button type="button" onclick="document.getElementById('issue-modal').close()" class="dialog-close-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <p class="dialog-eyebrow">Condition Issue Report</p>
        <p id="issue-modal-title" class="dialog-title"></p>
    </div>
    <form id="issue-modal-form" method="POST">
        @csrf
        <input type="hidden" name="action" value="damaged">
        <div class="dialog-body">
            <p class="form-section-label">Select all issue / damage types that apply</p>
            <div style="display:grid;grid-template-columns:1fr;gap:10px;">
                @foreach($issueTypes as $val => $label)
                <label class="dmg-tile">
                    <input type="checkbox" name="condition_flags[]" value="{{ $val }}" onchange="toggleDmgTile(this){{ $val==='others' ? '; toggleOthersNote(this)' : '' }}">
                    <div class="dmg-tile-inner">
                        <span class="dmg-tile-label">{{ $label }}</span>
                    </div>
                </label>
                @endforeach
            </div>
            <div id="issue-notes-wrap" style="margin-top:14px;">
                <div class="form-group">
                    <label>Remarks / Issue Description</label>
                    <textarea name="remarks" rows="2" placeholder="Details regarding the condition, damaged pages, or warning notes..."></textarea>
                </div>
            </div>
        </div>
        <div class="dialog-footer">
            <button type="button" onclick="document.getElementById('issue-modal').close()" class="btn-outline">Cancel</button>
            <button type="submit" class="btn-maroon">Submit Condition Report</button>
        </div>
    </form>
</dialog>

<dialog id="ban-modal" class="ops-dialog">
    <div class="ops-dialog-header">
        <button type="button" onclick="document.getElementById('ban-modal').close()" class="dialog-close-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <p class="dialog-eyebrow">Patron Ban</p>
        <p id="ban-modal-subtitle" class="dialog-title"></p>
    </div>
    <form method="POST" action="{{ route('admin.operations.ban-patron') }}">
        @csrf
        <input type="hidden" name="patron_type" id="ban-patron-type">
        <input type="hidden" name="patron_id"   id="ban-patron-id">
        <div class="dialog-body">
            <div class="form-group">
                <label>Reason for Ban <span class="required">*</span></label>
                <textarea name="reason" rows="3" required placeholder="Specify why this patron is being banned..."></textarea>
            </div>
        </div>
        <div class="dialog-footer">
            <button type="button" onclick="document.getElementById('ban-modal').close()" class="btn-outline">Cancel</button>
            <button type="submit" class="btn-danger">Confirm Ban</button>
        </div>
    </form>
</dialog>

{{-- UNBAN MODAL (PUP Themed, Requires Reason, Retains Warning Count) --}}
<dialog id="unban-modal" class="ops-dialog">
    <div class="ops-dialog-header" style="background:linear-gradient(135deg, #800000 0%, #5a0000 100%);">
        <button type="button" onclick="document.getElementById('unban-modal').close()" class="dialog-close-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <p class="dialog-eyebrow" style="color:#FFC72C;">Patron Unban Resolution</p>
        <p id="unban-modal-subtitle" class="dialog-title"></p>
    </div>
    <form method="POST" action="{{ route('admin.operations.unban') }}">
        @csrf
        <input type="hidden" name="patron_type" id="unban-patron-type">
        <input type="hidden" name="patron_id"   id="unban-patron-id">
        <div class="dialog-body">
            <div style="background:#fffdf5; border:1px solid #fde68a; border-radius:10px; padding:12px 14px; margin-bottom:16px;">
                <p style="margin:0; font-size:0.82rem; color:#92400e; line-height:1.4;">
                    <strong>Notice:</strong> Unbanning restores this patron's borrowing privilege. Their warning count (<span id="unban-warn-count-display">0</span> warnings) <strong>will be retained</strong> and never resets. If they receive a new warning in the future, auto-ban will trigger again.
                </p>
            </div>

            <div class="form-group">
                <label>Reason / Justification for Unbanning <span class="required">*</span></label>
                <textarea name="unban_reason" rows="3" required placeholder="Enter the justification (e.g., student submitted appeal letter, formal letter of explanation, disciplinary settlement)..."></textarea>
                <small style="color:var(--text-muted);font-size:0.75rem;margin-top:4px;">This reason will be recorded in Ban History and the System Audit Log.</small>
            </div>
        </div>
        <div class="dialog-footer">
            <button type="button" onclick="document.getElementById('unban-modal').close()" class="btn-outline">Cancel</button>
            <button type="submit" class="btn-maroon" style="background:#800000; color:#FFC72C; font-weight:700;">Confirm Unban</button>
        </div>
    </form>
</dialog>

<dialog id="resolve-penalty-modal" class="ops-dialog ops-dialog-green">
    <div class="ops-dialog-header">
        <button type="button" onclick="document.getElementById('resolve-penalty-modal').close()" class="dialog-close-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <p class="dialog-eyebrow">Resolve Penalty &amp; Record Payment</p>
        <p id="resolve-penalty-modal-subtitle" class="dialog-title">Resolve Penalty</p>
    </div>
    <form id="resolve-penalty-form" method="POST" action="">
        @csrf
        <div class="dialog-body">
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:0.8rem;color:#64748b;font-weight:600;">Patron:</span>
                    <strong id="resolve-patron-name" style="font-size:0.85rem;color:#1e293b;">—</strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:0.8rem;color:#64748b;font-weight:600;">Book / Item:</span>
                    <span id="resolve-book-title" style="font-size:0.85rem;color:#334155;font-weight:600;">—</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:0.8rem;color:#64748b;font-weight:600;">Penalty Type:</span>
                    <span id="resolve-penalty-type" class="badge badge-yellow" style="font-size:0.75rem;">—</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding-top:6px;border-top:1px dashed #cbd5e1;align-items:center;">
                    <span style="font-size:0.85rem;color:#0f172a;font-weight:700;">Amount Due:</span>
                    <strong id="resolve-amount" style="font-size:1.15rem;color:#800000;font-weight:800;">₱0.00</strong>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label>Resolution Status <span class="required">*</span></label>
                <select name="status" id="resolve-status-select" required style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-family:inherit;">
                    <option value="paid" selected>Paid / Settled (Receipt / Cashier)</option>
                    <option value="recorded_cash">Recorded Cash (Collected at Desk)</option>
                    <option value="waived">Waived / Forgiven (Admin Approval)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Remarks / Receipt Number / Details <span class="required">*</span></label>
                <textarea name="remarks" id="resolve-remarks-input" rows="3" required placeholder="Enter Official Receipt (OR) number, cashier reference, or resolution notes..."></textarea>
                <p style="font-size:0.75rem;color:#6b7280;margin:4px 0 0;">These remarks will be saved permanently in Historical Logs and the patron's penalty records.</p>
            </div>
        </div>
        <div class="dialog-footer">
            <button type="button" onclick="document.getElementById('resolve-penalty-modal').close()" class="btn-outline">Cancel</button>
            <button type="submit" class="btn-maroon" style="background:#16a34a;border-color:#16a34a;color:#fff;">Confirm &amp; Resolve</button>
        </div>
    </form>
</dialog>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- STYLES                                                             --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<style>
.ops-page {
    --pup-maroon: #800000;
    --pup-maroon-dark: #5a0000;
    --pup-gold: #FFC72C;
    --bg-main: #FAF9F6;
    --text-muted: #6b7280;
    --border: #e5e7eb;
    --radius: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,0.1);
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}

.ops-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.ops-page h2 {
    margin: 0 0 6px;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
}
.page-subtitle { color: var(--text-muted); font-size: 0.9375rem; margin: 0; }

.ops-pill-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: 1.5px solid var(--border);
    border-radius: 20px;
    background: #fff;
    color: var(--pup-maroon);
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    box-shadow: var(--shadow);
    transition: all 0.2s;
}
.ops-pill-link:hover { background: var(--pup-maroon); color: #fff; border-color: var(--pup-maroon); }

.alert-success { background:#ECFDF5;color:#065F46;padding:1rem 1.25rem;border-radius:10px;margin-bottom:1.5rem;border-left:4px solid #10B981; }
.alert-error   { background:#FEE2E2;color:#991B1B;padding:1rem 1.25rem;border-radius:10px;margin-bottom:1.5rem;border-left:4px solid #EF4444; }

/* ── Type Tabs ───────────────────────────────────────── */
.type-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    background: white;
    padding: 0.5rem;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    flex-wrap: wrap;
    box-shadow: var(--shadow);
}
.type-tab {
    flex: 1;
    min-width: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border: none;
    background: transparent;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    color: var(--text-muted);
    min-height: 44px;
    font-family: inherit;
    text-decoration: none;
}
.type-tab:hover {
    background: var(--bg-main);
    color: #1f2937;
}
.type-tab.active {
    background: var(--pup-maroon);
    color: white;
}
.type-tab.active svg { stroke: white; }
.tab-count {
    background: rgba(0,0,0,0.08);
    color: var(--pup-maroon);
    padding: 0.125rem 0.5rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
}
.type-tab.active .tab-count {
    background: rgba(255,255,255,0.22);
    color: white;
}

/* ── Card Styles ────────────────────────────────────── */
.card { background:#fff; border-radius:var(--radius); border:1px solid var(--border); overflow:hidden; margin-bottom:1.75rem; box-shadow:var(--shadow); }
.card-header {
    display:flex; justify-content:space-between; align-items:center;
    padding:1.25rem 1.5rem; border-bottom:1px solid var(--border);
    flex-wrap:wrap; gap:0.75rem;
}
.card-header h4 { margin:0; font-weight:600; color:var(--pup-maroon); font-size:1.1rem; }
.card-sub { margin:3px 0 0; font-size:0.8rem; color:var(--text-muted); }
.card-badge { font-size:0.75rem; color:var(--text-muted); background:var(--bg-main); padding:0.375rem 0.75rem; border-radius:20px; }
.card-body  { padding:1.5rem; }

.tab-bar { display:flex; gap:0.5rem; margin-bottom:1rem; flex-wrap:wrap; }
.tab-btn {
    display:inline-flex; align-items:center; gap:0.4rem;
    padding:0.6rem 1.2rem; border-radius:8px; border:2px solid var(--border);
    background:#fff; color:var(--text-muted);
    font-weight:600; font-size:0.875rem; cursor:pointer; transition:all 0.2s;
}
.tab-btn.active { background:var(--pup-maroon); color:#fff; border-color:var(--pup-maroon); }
.tab-btn:hover:not(.active) { border-color:var(--pup-maroon); color:var(--pup-maroon); }

.card-search-bar { padding:0.875rem 1.5rem; background:#fafafa; border-bottom:1px solid var(--border); }
.search-wrap { position:relative; display:flex; align-items:center; max-width:380px; }
.search-wrap svg { position:absolute; left:12px; }
.search-wrap input {
    width:100%; padding:0.5rem 1rem 0.5rem 2.25rem;
    border:1px solid var(--border); border-radius:20px;
    font-size:0.875rem; font-family:inherit; outline:none;
}
.search-wrap input:focus { border-color:var(--pup-maroon); }
.search-wrap-sm { flex:1; min-width:180px; max-width:280px; margin-left:auto; }
.search-wrap-sm input { padding:0.45rem 0.75rem 0.45rem 1.8rem; font-size:0.78rem; }

.chip-group { display:flex; gap:4px; background:#e5e7eb; padding:4px; border-radius:20px; flex-shrink:0; }
.chip { padding:5px 14px; border-radius:16px; font-size:0.75rem; font-weight:700; cursor:pointer; border:none; background:transparent; color:#4b5563; font-family:inherit; transition:all 0.15s; }
.chip.active { background:var(--pup-maroon); color:#fff; }

.table-responsive { overflow-x:auto; }
.data-table { width:100%; border-collapse:collapse; min-width:640px; }
.data-table th { background:var(--pup-maroon); color:#fff; padding:0.875rem 1rem; text-align:left; font-size:0.78rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
.data-table th.col-center { text-align:center; }
.data-table td { padding:0.875rem 1rem; border-bottom:1px solid var(--border); vertical-align:middle; font-size:0.875rem; }
.data-table td.col-center { text-align:center; }
.data-table tbody tr:hover { background:#FEFCE8; }
.data-table tbody tr:last-child td { border-bottom:none; }

.text-muted-sm { color:var(--text-muted); font-size:0.82rem; }
.text-maroon   { color:var(--pup-maroon); }

.badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:0.72rem; font-weight:600; }
.badge-blue   { background:#eff6ff; color:#1d4ed8; }
.badge-amber  { background:#fef3c7; color:#92400e; }
.badge-green  { background:#d1fae5; color:#065f46; }
.badge-red    { background:#fee2e2; color:#991b1b; }
.badge-yellow { background:#fef3c7; color:#92400e; }
.badge-gray   { background:#f3f4f6; color:#6b7280; }

.actions-wrapper { display:flex; justify-content:center; gap:0.4rem; flex-wrap:wrap; }
.action-btn { padding:0.35rem 0.85rem; border-radius:6px; font-size:0.75rem; font-weight:600; cursor:pointer; transition:all 0.2s; border:1.5px solid var(--border); background:#fff; color:#374151; font-family:inherit; }
.action-btn:hover { border-color:var(--pup-maroon); color:var(--pup-maroon); }
.action-btn.approve { background:#D1FAE5; color:#065F46; border-color:#A7F3D0; }
.action-btn.approve:hover { background:#10B981; color:#fff; border-color:#10B981; }
.action-btn.reject  { background:#FEE2E2; color:#991B1B; border-color:#FECACA; }
.action-btn.reject:hover  { background:#EF4444; color:#fff; border-color:#EF4444; }

.expand-panel { background:#fdf9f9; padding:16px 20px !important; }
.expand-panel-light { background:#f9fafb !important; }
.expand-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-bottom:12px; }
.expand-footer { display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
.expand-inline { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }

.form-group { display:flex; flex-direction:column; gap:5px; }
.form-group label { font-size:0.72rem; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.4px; }
.form-group input, .form-group select, .form-group textarea {
    padding:8px 10px; border:1px solid var(--border); border-radius:8px;
    font-size:0.875rem; font-family:inherit; outline:none;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:var(--pup-maroon); }
.form-group textarea { resize:vertical; }
.required { color:var(--pup-maroon); }
.form-section-label { font-size:0.7rem; font-weight:700; color:#9ca3af; margin:0 0 12px; text-transform:uppercase; letter-spacing:0.8px; }
.checkbox-label { display:flex; align-items:center; gap:8px; font-size:0.82rem; font-weight:600; cursor:pointer; }
.checkbox-label input { width:16px; height:16px; accent-color:var(--pup-maroon); }

.cfg-grid  { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-bottom:1.5rem; }
.cfg-checks { display:flex; flex-direction:column; gap:12px; }
.check-card { padding:14px 16px; border-radius:10px; }
.check-card-blue  { background:#eef2ff; border:1px solid #a5b4fc; }
.check-card-amber { background:#fef9ec; border:1px solid #f59e0b; }
.check-row { display:flex; align-items:center; gap:10px; margin-bottom:4px; }
.check-row input { width:16px; height:16px; accent-color:var(--pup-maroon); cursor:pointer; }
.check-row label { font-size:0.875rem; font-weight:700; color:#374151; cursor:pointer; margin:0; }
.check-card p { margin:0 0 0 26px; font-size:0.78rem; color:var(--text-muted); }

.btn-maroon { padding:10px 24px; border:none; border-radius:8px; background:var(--pup-maroon); color:#fff; font-size:0.875rem; font-weight:700; cursor:pointer; font-family:inherit; transition:all 0.2s; }
.btn-maroon:hover { background:var(--pup-maroon-dark, #5a0000); }
.btn-outline { padding:8px 18px; border:1.5px solid var(--border); border-radius:8px; background:#fff; color:#374151; font-size:0.82rem; font-weight:600; cursor:pointer; font-family:inherit; }
.btn-danger { padding:8px 22px; border:none; border-radius:8px; background:#991b1b; color:#fff; font-size:0.82rem; font-weight:700; cursor:pointer; font-family:inherit; }
.btn-green  { padding:8px 22px; border:none; border-radius:8px; background:#059669; color:#fff; font-size:0.82rem; font-weight:700; cursor:pointer; font-family:inherit; }

.empty-state-content { padding:2.5rem; text-align:center; color:var(--text-muted); }
.empty-state-content svg { opacity:0.4; display:block; margin:0 auto 0.75rem; }
.empty-state-content p { margin:0; font-size:0.95rem; }

.ops-dialog { border:none; border-radius:16px; width:calc(100% - 32px); max-width:520px; padding:0; box-shadow:0 24px 64px rgba(0,0,0,0.28); overflow:hidden; margin:auto; }
.ops-dialog::backdrop { background:rgba(0,0,0,0.4); }
.ops-dialog-header { background: linear-gradient(135deg, var(--pup-maroon-dark, #5a0000) 0%, var(--pup-maroon) 100%); padding:28px 28px 24px; position:relative; }
.ops-dialog-green .ops-dialog-header { background: linear-gradient(135deg, #065f46 0%, #047857 100%); }
.dialog-close-btn { position:absolute; top:14px; right:14px; background:rgba(255,255,255,0.15); border:none; border-radius:50%; width:34px; height:34px; cursor:pointer; color:#fff; display:flex; align-items:center; justify-content:center; }
.dialog-eyebrow { margin:0 0 4px; font-size:0.68rem; font-weight:600; color:rgba(255,255,255,0.65); text-transform:uppercase; }
.dialog-title { margin:0; font-size:1.15rem; font-weight:700; color:#fff; padding-right:40px; line-height:1.3; }
.dialog-body { padding:24px 28px; }
.dialog-footer { padding:0 28px 24px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #f3f4f6; padding-top:16px; }

.dmg-tile { cursor:pointer; display:block; }
.dmg-tile input { position:absolute; opacity:0; width:0; height:0; }
.dmg-tile-inner { border:2px solid var(--border); border-radius:12px; padding:14px 16px; background:#f9fafb; transition:all 0.15s; display:flex; align-items:center; gap:10px; }
.dmg-tile.selected .dmg-tile-inner { border-color:var(--pup-maroon); background:#fff5f5; }
.dmg-tile-label { font-size:0.88rem; font-weight:600; color:#374151; }

@media (max-width:768px) {
    .type-tabs { flex-direction:column; }
    .card-header { flex-direction:column; align-items:flex-start; }
    .data-table th, .data-table td { padding:0.625rem 0.5rem; font-size:0.75rem; }
    .cfg-grid { grid-template-columns:1fr; }
    .expand-grid { grid-template-columns:1fr; }
}
</style>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- SCRIPTS                                                            --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<script>
// ── Tab Switcher ───────────────────────────────────────────────────
function switchOpTab(tabName, btn) {
    document.querySelectorAll('.op-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.type-tab').forEach(b => b.classList.remove('active'));
    
    const target = document.getElementById('op-panel-' + tabName);
    if (target) target.style.display = 'block';
    
    if (btn) {
        btn.classList.add('active');
    } else {
        const matchBtn = document.querySelector(`.type-tab[data-tab="${tabName}"]`);
        if (matchBtn) matchBtn.classList.add('active');
    }
    
    if (window.history && window.history.pushState) {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({ tab: tabName }, '', url);
    }
}

function checkInitialTab() {
    const sessionTab = '{{ session('active_tab') }}';
    const params = new URLSearchParams(window.location.search);
    const tab = sessionTab || params.get('tab');
    if (tab && document.getElementById('op-panel-' + tab)) {
        switchOpTab(tab);
    }
}
document.addEventListener('DOMContentLoaded', checkInitialTab);
window.addEventListener('popstate', checkInitialTab);

// ── Filter Pending Damage ───────────────────────────────────────────
let dmgSearchTimer = null;
function debouncedFilterPendingDamage() {
    clearTimeout(dmgSearchTimer);
    dmgSearchTimer = setTimeout(filterPendingDamage, 300);
}

let penSearchTimer = null;
function debouncedFilterPendingPenalties() {
    clearTimeout(penSearchTimer);
    penSearchTimer = setTimeout(filterPendingPenalties, 300);
}

let restSearchTimer = null;
function debouncedFilterRestrictedPatrons() {
    clearTimeout(restSearchTimer);
    restSearchTimer = setTimeout(filterRestrictedPatrons, 300);
}

function filterPendingDamage() {
    const q = (document.getElementById('pendingDamageSearch').value || '').toLowerCase();
    const rows = document.querySelectorAll('#pendingDamageBody tr[data-book]');
    let visible = 0;
    rows.forEach(tr => {
        const ok = (tr.dataset.book || '').includes(q) || (tr.dataset.patron || '').includes(q);
        const siblings = [tr];
        let next = tr.nextElementSibling;
        while (next && !next.hasAttribute('data-book')) {
            siblings.push(next);
            next = next.nextElementSibling;
        }
        siblings.forEach(s => s.style.display = ok ? '' : 'none');
        if (ok) visible++;
    });
    const noRes = document.getElementById('pendingDamageNoResults');
    if (noRes) noRes.style.display = (visible === 0 && rows.length > 0) ? 'block' : 'none';
}

// ── Filter Pending Penalties ─────────────────────────────────────────
function filterPendingPenalties() {
    const q = (document.getElementById('pendingPenaltySearch').value || '').toLowerCase();
    const rows = document.querySelectorAll('#pendingPenaltyBody tr[data-patron]');
    let visible = 0;
    rows.forEach(tr => {
        const ok = (tr.dataset.patron || '').includes(q) 
                || (tr.dataset.book || '').includes(q) 
                || (tr.dataset.type || '').includes(q);
        tr.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });
    const noRes = document.getElementById('pendingPenaltyNoResults');
    if (noRes) noRes.style.display = (visible === 0 && rows.length > 0) ? 'block' : 'none';
}

// ── Filter Restricted Patrons Table (Banned / Suspended / Warned) ─────
let currentPatronKindFilter = 'all';

function filterRestrictedByKind(kind, btn) {
    currentPatronKindFilter = kind;
    document.querySelectorAll('.chip-group .chip').forEach(c => c.classList.remove('active'));
    if (btn) btn.classList.add('active');
    filterRestrictedPatrons();
}

function filterRestrictedPatrons() {
    const q = (document.getElementById('restrictedPatronsSearch')?.value || '').toLowerCase();
    const rows = document.querySelectorAll('#restrictedPatronsBody tr');
    rows.forEach(tr => {
        const name = (tr.dataset.name || '').toLowerCase();
        const kind = tr.dataset.kind || '';
        const matchKind = (currentPatronKindFilter === 'all' || kind === currentPatronKindFilter);
        const matchSearch = !q || name.includes(q);
        tr.style.display = (matchKind && matchSearch) ? '' : 'none';
    });
}

// ── Resolve Penalty Modal ────────────────────────────────────────────
function openResolvePenaltyModal(actionUrl, patronName, bookTitle, amount, penaltyType, existingNote) {
    document.getElementById('resolve-penalty-form').action = actionUrl;
    document.getElementById('resolve-patron-name').textContent = patronName || '—';
    document.getElementById('resolve-book-title').textContent = bookTitle || '—';
    document.getElementById('resolve-penalty-type').textContent = penaltyType || 'Penalty';
    document.getElementById('resolve-amount').textContent = '₱' + amount;
    const remarksInput = document.getElementById('resolve-remarks-input');
    if (remarksInput) {
        remarksInput.value = '';
        remarksInput.placeholder = existingNote ? ('Current note: ' + existingNote + '. Enter payment receipt / remarks...') : 'Enter Official Receipt (OR) number, cashier reference, or resolution notes...';
    }
    const modal = document.getElementById('resolve-penalty-modal');
    if (modal) modal.showModal();
}

// ── Resolve / Dismiss panels ─────────────────────────────────────────
function toggleDmgActions(id) {
    const panel  = document.getElementById('dmg-actions-' + id);
    const dismiss = document.getElementById('dmg-dismiss-' + id);
    if (!panel) return;
    const showing = panel.style.display !== 'none';
    panel.style.display = showing ? 'none' : '';
    if (dismiss) dismiss.style.display = 'none';
}
function toggleDmgDismiss(id) {
    const panel  = document.getElementById('dmg-dismiss-' + id);
    const resolve = document.getElementById('dmg-actions-' + id);
    if (!panel) return;
    const showing = panel.style.display !== 'none';
    panel.style.display = showing ? 'none' : '';
    if (resolve) resolve.style.display = 'none';
}
function updateDmgFee(sel, id) {
    const opt  = sel.options[sel.selectedIndex];
    const fee  = opt ? opt.dataset.fee : '';
    const inp  = document.getElementById('dmg-fee-' + id);
    if (inp && fee) inp.value = fee;
}

// ── Kiosk Return AJAX & Modal ──────────────────────────────────────
async function acknowledgeKioskAllGood(btn, url) {
    btn.disabled = true;
    const origText = btn.textContent;
    btn.textContent = 'Processing...';
    const card = btn.closest('.kiosk-return-card');
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: 'ok' })
        });
        if (res.ok) {
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    card.remove();
                    const remaining = document.querySelectorAll('.kiosk-return-card').length;
                    const badge = document.getElementById('kiosk-pending-badge');
                    if (badge) badge.textContent = remaining + ' awaiting check';
                    if (remaining === 0) {
                        const sec = document.getElementById('op-panel-kiosk') || card.closest('.card');
                        if (sec && !document.querySelector('.kiosk-return-card')) sec.style.display = 'none';
                    }
                }, 300);
            }
        } else {
            alert('Failed to acknowledge return. Please refresh.');
            btn.disabled = false;
            btn.textContent = origText;
        }
    } catch(e) {
        console.error(e);
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.textContent = origText;
    }
}

function openIssueModal(actionUrl, bookTitle) {
    document.getElementById('issue-modal-form').action = actionUrl;
    document.getElementById('issue-modal-title').textContent = bookTitle;
    document.getElementById('issue-modal').showModal();
}
function toggleDmgTile(chk) {
    const tile = chk.closest('.dmg-tile');
    if (tile) tile.classList.toggle('selected', chk.checked);
}
function toggleOthersNote(chk) {
    document.getElementById('issue-notes-wrap').style.display = chk.checked ? 'block' : 'none';
}

// ── Ban / Unban Modals ───────────────────────────────────────────────
function openBanModal(type, id, name) {
    document.getElementById('ban-patron-type').value = type;
    document.getElementById('ban-patron-id').value   = id;
    document.getElementById('ban-modal-subtitle').textContent = ucType(type) + ': ' + name;
    document.getElementById('ban-modal').showModal();
}
function openUnbanModal(type, id, name, warnCount) {
    document.getElementById('unban-patron-type').value = type;
    document.getElementById('unban-patron-id').value   = id;
    document.getElementById('unban-modal-subtitle').textContent = ucType(type) + ': ' + name;
    const countDisplay = document.getElementById('unban-warn-count-display');
    if (countDisplay) countDisplay.textContent = warnCount || 0;
    document.getElementById('unban-modal').showModal();
}
function ucType(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
</script>
@endsection