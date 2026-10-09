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
            <h2>Overdue &amp; Ban Management</h2>
            <p class="page-subtitle">Active overdue transactions, warning thresholds, and patron restrictions.</p>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <form method="POST" action="{{ route('admin.operations.simulate-overdue') }}" style="display:inline;">
                @csrf
                <button type="submit" class="ops-pill-link" style="background:#fff1f2;border-color:#fecdd3;color:#be123c;cursor:pointer;" title="Simulate an active borrow becoming overdue for testing and fine processing">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    ⚡ Simulate Overdue Loan (Test)
                </button>
            </form>
            <a href="{{ route('admin.book.suggestions') }}" class="ops-pill-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                Book Recommendations
            </a>
        </div>
    </div>

    {{-- CARD WITH TABS --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h4>Overdue &amp; Ban Management</h4>
                <p class="card-sub">Active overdue transactions, warning thresholds, and patron restrictions.</p>
            </div>
        </div>

        <div class="tab-bar" style="padding:1rem 1.5rem 0;margin-bottom:0;border-bottom:1px solid var(--border);">
            <button class="tab-btn active" id="s2-btn-overdue" onclick="switchS2Tab('overdue',this)">
                Overdue Borrows
                @if($overdueBorrows->count()) <span class="tab-badge">{{ $overdueBorrows->count() }}</span> @endif
            </button>
            <button class="tab-btn" id="s2-btn-bannable" onclick="switchS2Tab('bannable',this)">
                Banned &amp; Suspended
                @if(($restrictedPatrons ?? $bannedPatrons)->count()) <span class="tab-badge tab-badge-red">{{ ($restrictedPatrons ?? $bannedPatrons)->count() }} restricted</span> @endif
            </button>
            <button class="tab-btn" id="s2-btn-ban-history" onclick="switchS2Tab('ban-history',this)">
                Ban History
                @if($banHistory->count()) <span class="tab-badge">{{ $banHistory->count() }}</span> @endif
            </button>
        </div>

        {{-- Tab 1: Overdue Borrows --}}
        <div id="s2-overdue" class="s2-panel">
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

        {{-- Tab 2: Banned & Suspended --}}
        <div id="s2-bannable" class="s2-panel" style="display:none;padding:1.5rem;">
            @php $mixedPatrons = $restrictedPatrons ?? $bannedPatrons; @endphp
            @if($mixedPatrons->isEmpty())
                <div class="empty-state-content">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <p>No currently banned, suspended, or warned patrons.</p>
                </div>
            @else
                <p class="section-label">Restricted &amp; Warned Patrons</p>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>Patron Name</th><th>Type</th><th>Status / Warnings</th><th>Details</th><th class="col-center">Action</th></tr></thead>
                        <tbody>
                            @foreach($mixedPatrons as $bnp)
                            @php 
                                $kind = $bnp['kind'] ?? 'banned'; 
                                $warningCount = $bnp['model']->damage_warning_count ?? 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $bnp['model']->first_name }} {{ $bnp['model']->last_name }}</strong></td>
                                <td><span class="badge badge-blue">{{ ucfirst($bnp['type']) }}</span></td>
                                <td>
                                    @if($kind === 'suspended')
                                        <span class="badge badge-amber">Suspended</span>
                                    @elseif($kind === 'warned')
                                        <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;">
                                            ⚠️ {{ $warningCount }} Warning{{ $warningCount > 1 ? 's' : '' }}
                                        </span>
                                    @else
                                        <span class="badge badge-red">Banned</span>
                                        @if($warningCount > 0)
                                            <span class="badge" style="background:#fee2e2;color:#991b1b;margin-left:4px;">{{ $warningCount }} Warn</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-muted-sm">
                                    @if($kind === 'suspended')
                                        Until {{ optional($bnp['model']->borrowing_suspended_until)->format('M d, Y g:i A') ?? '—' }}
                                    @elseif($kind === 'warned')
                                        Active Warnings: {{ $warningCount }} (Auto-ban at {{ $libraryConfig['warning_ban_threshold'] ?? 3 }})
                                    @else
                                        {{ $bnp['ban']?->banned_at?->format('M d, Y') ?? '—' }}
                                        — {{ $bnp['ban']?->reason ?? 'Automatic threshold ban' }}
                                    @endif
                                </td>
                                <td class="col-center">
                                    @if($kind === 'suspended')
                                        <form method="POST" action="{{ route('admin.operations.unsuspend') }}" style="display:inline;" onsubmit="return confirm('Unsuspend this patron?')">
                                            @csrf
                                            <input type="hidden" name="user_type" value="{{ $bnp['type'] }}">
                                            <input type="hidden" name="user_id" value="{{ $bnp['model']->id }}">
                                            <button type="submit" class="action-btn approve">Unsuspend</button>
                                        </form>
                                    @elseif($kind === 'warned')
                                        <span style="font-size:0.75rem;color:var(--text-muted);">Active (Not Banned)</span>
                                    @else
                                        <button type="button" class="action-btn approve" onclick="openUnbanModal('{{ $bnp['type'] }}', {{ $bnp['model']->id }}, '{{ addslashes($bnp['model']->first_name.' '.$bnp['model']->last_name) }}', {{ $warningCount }})">Unban</button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Tab 3: Ban History --}}
        <div id="s2-ban-history" class="s2-panel" style="display:none;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patron</th>
                            <th>Type</th>
                            <th>Banned At</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Unbanned At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banHistory as $bh)
                        @php $bhP = $bh->student ?? $bh->faculty; @endphp
                        <tr>
                            <td><strong>{{ $bhP ? ($bhP->first_name.' '.$bhP->last_name) : '—' }}</strong></td>
                            <td><span class="badge {{ $bh->student_id ? 'badge-blue' : 'badge-amber' }}">{{ $bh->student_id ? 'Student' : 'Faculty' }}</span></td>
                            <td class="text-muted-sm">{{ $bh->banned_at?->format('M d, Y g:i A') ?? '—' }}</td>
                            <td class="text-muted-sm">{{ $bh->reason }}</td>
                            <td>
                                @if($bh->unbanned_at)
                                    <span class="badge badge-green">Unbanned</span>
                                @else
                                    <span class="badge badge-red">Active Ban</span>
                                @endif
                            </td>
                            <td class="text-muted-sm">{{ $bh->unbanned_at?->format('M d, Y g:i A') ?? 'Active' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6">
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
</div>

{{-- Ban Modal --}}
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

{{-- Unban Modal --}}
<dialog id="unban-modal" class="ops-dialog ops-dialog-green">
    <div class="ops-dialog-header">
        <button type="button" onclick="document.getElementById('unban-modal').close()" class="dialog-close-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <p class="dialog-eyebrow">Patron Unban</p>
        <p id="unban-modal-subtitle" class="dialog-title"></p>
    </div>
    <form method="POST" action="{{ route('admin.operations.unban') }}">
        @csrf
        <input type="hidden" name="patron_type" id="unban-patron-type">
        <input type="hidden" name="patron_id"   id="unban-patron-id">
        <div class="dialog-body">
            <div id="unban-warning-info" style="margin-bottom:12px;padding:10px 14px;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;font-size:0.82rem;color:#92400e;line-height:1.4;">
                ⚠️ <strong>Warning Count Note:</strong> This user's warning count (<span id="unban-warning-count-val">0</span>) will remain unchanged upon unbanning. Any future warning will immediately trigger an automatic ban.
            </div>
            <div class="form-group">
                <label>Reason for Unbanning (e.g. Appeal, Letter, Settlement) <span class="required">*</span></label>
                <textarea name="unban_notes" rows="3" required placeholder="Specify the reason or appeal reference for unbanning this patron..."></textarea>
            </div>
        </div>
        <div class="dialog-footer">
            <button type="button" onclick="document.getElementById('unban-modal').close()" class="btn-outline">Cancel</button>
            <button type="submit" class="btn-green">Confirm Unban</button>
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
.alert-success { background:#ECFDF5;color:#065F46;padding:1rem 1.25rem;border-radius:10px;margin-bottom:1.5rem;border-left:4px solid #10B981; }
.alert-error   { background:#FEE2E2;color:#991B1B;padding:1rem 1.25rem;border-radius:10px;margin-bottom:1.5rem;border-left:4px solid #EF4444; }
.card { background:#fff; border-radius:var(--radius); border:1px solid var(--border); overflow:hidden; margin-bottom:1.75rem; box-shadow:var(--shadow); }
.card-header { display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); flex-wrap:wrap; gap:0.75rem; }
.card-header h4 { margin:0; font-weight:600; color:var(--pup-maroon); font-size:1.1rem; }
.card-sub { margin:3px 0 0; font-size:0.8rem; color:var(--text-muted); }
.tab-bar { display:flex; gap:0.5rem; margin-bottom:1rem; flex-wrap:wrap; }
.tab-btn { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.2rem; border-radius:8px; border:2px solid var(--border); background:#fff; color:var(--text-muted); font-weight:600; font-size:0.875rem; cursor:pointer; transition:all 0.2s; }
.tab-btn.active { background:var(--pup-maroon); color:#fff; border-color:var(--pup-maroon); }
.tab-badge { background:var(--pup-gold); color:var(--pup-maroon); border-radius:20px; font-size:0.7rem; font-weight:700; padding:0.1rem 0.5rem; }
.tab-badge-red { background:#fee2e2 !important; color:#991b1b !important; }
.table-responsive { overflow-x:auto; }
.data-table { width:100%; border-collapse:collapse; min-width:640px; }
.data-table th { background:var(--pup-maroon); color:#fff; padding:0.875rem 1rem; text-align:left; font-size:0.78rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
.data-table th.col-center { text-align:center; }
.data-table td { padding:0.875rem 1rem; border-bottom:1px solid var(--border); vertical-align:middle; font-size:0.875rem; }
.data-table td.col-center { text-align:center; }
.data-table tbody tr:hover { background:#FEFCE8; }
.text-muted-sm { color:var(--text-muted); font-size:0.82rem; }
.badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:0.72rem; font-weight:600; }
.badge-blue { background:#eff6ff; color:#1d4ed8; }
.badge-amber { background:#fef3c7; color:#92400e; }
.badge-green { background:#d1fae5; color:#065f46; }
.badge-red { background:#fee2e2; color:#991b1b; }
.actions-wrapper { display:flex; justify-content:center; gap:0.4rem; flex-wrap:wrap; }
.action-btn { padding:0.35rem 0.85rem; border-radius:6px; font-size:0.75rem; font-weight:600; cursor:pointer; transition:all 0.2s; border:1.5px solid var(--border); background:#fff; color:#374151; font-family:inherit; }
.action-btn.approve { background:#D1FAE5; color:#065F46; border-color:#A7F3D0; }
.action-btn.reject  { background:#FEE2E2; color:#991B1B; border-color:#FECACA; }
.empty-state-content { padding:2.5rem; text-align:center; color:var(--text-muted); }
.empty-state-content svg { opacity:0.4; display:block; margin:0 auto 0.75rem; }
.section-label { font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--pup-maroon); margin:0 0 10px; }
.ops-dialog { border:none; border-radius:16px; width:calc(100% - 32px); max-width:520px; padding:0; box-shadow:0 24px 64px rgba(0,0,0,0.28); overflow:hidden; margin:auto; }
.ops-dialog-header { background: linear-gradient(135deg, var(--pup-maroon-dark, #5a0000) 0%, var(--pup-maroon) 100%); padding:28px 28px 24px; position:relative; }
.ops-dialog-green .ops-dialog-header { background: linear-gradient(135deg, #065f46 0%, #047857 100%); }
.dialog-close-btn { position:absolute; top:14px; right:14px; background:rgba(255,255,255,0.15); border:none; border-radius:50%; width:34px; height:34px; cursor:pointer; color:#fff; display:flex; align-items:center; justify-content:center; }
.dialog-eyebrow { margin:0 0 4px; font-size:0.68rem; font-weight:600; color:rgba(255,255,255,0.65); text-transform:uppercase; }
.dialog-title { margin:0; font-size:1.15rem; font-weight:700; color:#fff; }
.dialog-body { padding:24px 28px; }
.dialog-footer { padding:0 28px 24px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #f3f4f6; padding-top:16px; }
.form-group { display:flex; flex-direction:column; gap:5px; }
.form-group label { font-size:0.72rem; font-weight:700; color:#374151; text-transform:uppercase; }
.form-group textarea { padding:8px 10px; border:1px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:inherit; resize:vertical; }
.btn-outline { padding:8px 18px; border:1.5px solid var(--border); border-radius:8px; background:#fff; color:#374151; font-size:0.82rem; font-weight:600; cursor:pointer; }
.btn-danger { padding:8px 22px; border:none; border-radius:8px; background:#991b1b; color:#fff; font-size:0.82rem; font-weight:700; cursor:pointer; }
.btn-green { padding:8px 22px; border:none; border-radius:8px; background:#059669; color:#fff; font-size:0.82rem; font-weight:700; cursor:pointer; }
.required { color:var(--pup-maroon); }
</style>

<script>
function switchS2Tab(id, btn) {
    document.querySelectorAll('.s2-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('#s2-btn-overdue, #s2-btn-bannable, #s2-btn-ban-history').forEach(b => b.classList.remove('active'));
    document.getElementById('s2-' + id).style.display = 'block';
    btn.classList.add('active');
}
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
    var warnVal = warnCount !== undefined ? warnCount : 0;
    var warnCountSpan = document.getElementById('unban-warning-count-val');
    if (warnCountSpan) warnCountSpan.textContent = warnVal;
    document.getElementById('unban-modal').showModal();
}
function ucType(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
</script>
@endsection
