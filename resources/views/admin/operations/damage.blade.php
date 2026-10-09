@extends('layouts.admin')

@section('content')
<div class="ops-page">
    {{-- Header --}}
    <div class="ops-page-header">
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                <a href="{{ route('admin.operations.index') }}" class="ops-pill-link" style="padding:4px 12px;font-size:0.75rem;">
                    ← Back to Transactions
                </a>
            </div>
            <h2>Pending Damage Reports</h2>
            <p class="page-subtitle">Inspect reported damages and apply resolutions or fines.</p>
        </div>
        <a href="{{ route('admin.book.suggestions') }}" class="ops-pill-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            Book Recommendations
        </a>
    </div>

    {{-- KIOSK RETURNS CHECK --}}
    @if(isset($pendingKioskReturns) && $pendingKioskReturns->count())
    <div id="kiosk-returns-section" class="card" style="margin-bottom:1.75rem;border-top:4px solid #16a34a;">
        <div class="card-header">
            <div>
                <h4>Kiosk Returns — Condition Check</h4>
                <p class="card-sub">These books were returned at the kiosk. Verify condition before shelving.</p>
            </div>
            <span id="kiosk-pending-badge" class="card-badge" style="background:#dcfce7;color:#166534;font-weight:700;">{{ $pendingKioskReturns->count() }} pending</span>
        </div>
        <div id="kiosk-returns-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;padding:20px;">
            @foreach($pendingKioskReturns as $usage)
            @php $patron = $usage->student ?? $usage->faculty; @endphp
            <div class="kiosk-return-card" style="background:#fff;border:1.5px solid #e5e7eb;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;">
                <div style="padding:20px 20px 14px;text-align:center;border-bottom:1px solid #f0f0f0;">
                    <div style="width:52px;height:52px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div style="font-weight:700;font-size:0.92rem;color:#111;margin-bottom:4px;line-height:1.3;">
                        {{ $usage->book->title ?? 'Unknown Book' }}
                    </div>
                    <div style="font-size:0.78rem;color:#6b7280;">
                        {{ $patron ? $patron->first_name . ' ' . $patron->last_name : 'Unknown patron' }}
                    </div>
                </div>
                <div style="padding:14px 16px 14px;display:flex;gap:8px;">
                    <button type="button" onclick="acknowledgeKioskAllGood(this, '{{ route('admin.operations.return.acknowledge', $usage) }}')" class="action-btn approve" style="flex:1;justify-content:center;">All Good</button>
                    <button type="button" onclick="openIssueModal('{{ route('admin.operations.return.acknowledge', $usage) }}', '{{ addslashes($usage->book->title ?? 'Unknown Book') }}')" class="action-btn reject" style="flex:1;justify-content:center;">Report Issue</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- DAMAGE REPORTS CARD --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h4>Pending Damage Reports</h4>
                <p class="card-sub">Ordered by return time — resolve the earliest first.</p>
            </div>
            <span class="card-badge">{{ $damageReports->count() }} waiting resolution</span>
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
                        <th>Patron Note</th>
                        <th class="col-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="pendingDamageBody">
                    @forelse($damageReports as $report)
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
                        <td class="text-muted-sm">{{ $report->patron_note ?? '—' }}</td>
                        <td class="col-center">
                            <div class="actions-wrapper">
                                <button type="button" class="action-btn reject" onclick="toggleDmgActions({{ $report->id }})">Resolve</button>
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
                                        <label>Admin Notes</label>
                                        <input type="text" name="admin_notes" placeholder="Optional">
                                    </div>
                                </div>
                                <div class="expand-footer">
                                    <label class="checkbox-label text-maroon">
                                        <input type="checkbox" name="condemn_book" value="1"> Condemn this book
                                    </label>
                                    <label class="checkbox-label text-amber">
                                        <input type="checkbox" name="record_warning" value="1" {{ $isBanned ? 'disabled' : 'checked' }}> ⚠ Record warning
                                    </label>
                                    <button type="submit" class="btn-maroon" style="margin-left:auto;">Apply Penalty</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <tr id="dmg-dismiss-{{ $report->id }}" style="display:none;">
                        <td colspan="6" class="expand-panel expand-panel-light">
                            <form method="POST" action="{{ route('admin.operations.damage.dismiss', $report) }}" class="expand-inline">
                                @csrf
                                <input type="text" name="admin_notes" placeholder="Dismiss reason (optional)" style="flex:1;">
                                <button type="submit" class="action-btn">Confirm Dismiss</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state-content">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="20 6 9 17 4 12"/></svg>
                                <p>No pending damage reports.</p>
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

    {{-- PENDING PENALTIES CARD --}}
    <div class="card" style="margin-top:1.5rem;border-top:4px solid #800000;">
        <div class="card-header">
            <div>
                <h4>Pending Penalties &amp; Fines</h4>
                <p class="card-sub">Assessed damages, manual penalties, and overdue fines awaiting payment or resolution.</p>
            </div>
            <span class="card-badge" style="background:#fee2e2;color:#991b1b;font-weight:700;">{{ ($pendingPenalties ?? collect())->count() }} pending payment</span>
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
                    @forelse($pendingPenalties ?? [] as $penalty)
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
            <p style="font-size:0.75rem;font-weight:700;color:#9ca3af;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.8px;">Select all issue / damage types that apply</p>
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

<style>
.ops-page { max-width: 1400px; margin: 0 auto; padding: 0 1rem; }
.ops-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.ops-page h2 { margin: 0 0 6px; font-size: 1.75rem; font-weight: 700; color: var(--pup-maroon); }
.page-subtitle { color: var(--text-muted); font-size: 0.9375rem; margin: 0; }
.ops-pill-link { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border: 1.5px solid var(--border); border-radius: 20px; background: #fff; color: var(--pup-maroon); font-size: 0.82rem; font-weight: 600; text-decoration: none; box-shadow: var(--shadow); transition: all 0.2s; }
.ops-pill-link:hover { background: var(--pup-maroon); color: #fff; border-color: var(--pup-maroon); }
.card { background:#fff; border-radius:var(--radius); border:1px solid var(--border); overflow:hidden; margin-bottom:1.75rem; box-shadow:var(--shadow); }
.card-header { display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); flex-wrap:wrap; gap:0.75rem; }
.card-header h4 { margin:0; font-weight:600; color:var(--pup-maroon); font-size:1.1rem; }
.card-sub { margin:3px 0 0; font-size:0.8rem; color:var(--text-muted); }
.card-badge { font-size:0.75rem; color:var(--text-muted); background:var(--bg-main); padding:0.375rem 0.75rem; border-radius:20px; }
.card-search-bar { padding:0.875rem 1.5rem; background:#fafafa; border-bottom:1px solid var(--border); }
.search-wrap { position:relative; display:flex; align-items:center; max-width:380px; }
.search-wrap svg { position:absolute; left:12px; }
.search-wrap input { width:100%; padding:0.5rem 1rem 0.5rem 2.25rem; border:1px solid var(--border); border-radius:20px; font-size:0.875rem; font-family:inherit; outline:none; }
.table-responsive { overflow-x:auto; }
.data-table { width:100%; border-collapse:collapse; min-width:640px; }
.data-table th { background:var(--pup-maroon); color:#fff; padding:0.875rem 1rem; text-align:left; font-size:0.78rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
.data-table th.col-center { text-align:center; }
.data-table td { padding:0.875rem 1rem; border-bottom:1px solid var(--border); vertical-align:middle; font-size:0.875rem; }
.data-table td.col-center { text-align:center; }
.data-table tbody tr:hover { background:#FEFCE8; }
.badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:0.72rem; font-weight:600; }
.badge-blue { background:#eff6ff; color:#1d4ed8; }
.badge-amber { background:#fef3c7; color:#92400e; }
.badge-red { background:#fee2e2; color:#991b1b; }
.badge-yellow { background:#fef3c7; color:#92400e; }
.actions-wrapper { display:flex; justify-content:center; gap:0.4rem; flex-wrap:wrap; }
.action-btn { padding:0.35rem 0.85rem; border-radius:6px; font-size:0.75rem; font-weight:600; cursor:pointer; transition:all 0.2s; border:1.5px solid var(--border); background:#fff; color:#374151; font-family:inherit; }
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
.form-group label { font-size:0.72rem; font-weight:700; color:#374151; text-transform:uppercase; }
.form-group input, .form-group select, .form-group textarea { padding:8px 10px; border:1px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:inherit; }
.btn-maroon { padding:10px 24px; border:none; border-radius:8px; background:var(--pup-maroon); color:#fff; font-size:0.875rem; font-weight:700; cursor:pointer; }
.btn-outline { padding:8px 18px; border:1.5px solid var(--border); border-radius:8px; background:#fff; color:#374151; font-size:0.82rem; font-weight:600; cursor:pointer; font-family:inherit; }
.empty-state-content { padding:2.5rem; text-align:center; color:var(--text-muted); }
.empty-state-content svg { opacity:0.4; display:block; margin:0 auto 0.75rem; }
.checkbox-label { display:flex; align-items:center; gap:8px; font-size:0.82rem; font-weight:600; cursor:pointer; }
.text-maroon { color:var(--pup-maroon); }
.text-amber { color:#92400e; }
.required { color:var(--pup-maroon); }

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
</style>

<script>
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
                    if (badge) badge.textContent = remaining + ' pending';
                    if (remaining === 0) {
                        const sec = document.getElementById('kiosk-returns-section');
                        if (sec) sec.style.display = 'none';
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
    const wrap = document.getElementById('issue-notes-wrap');
    if (wrap) wrap.style.display = chk.checked ? 'block' : 'none';
}

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
</script>
@endsection
