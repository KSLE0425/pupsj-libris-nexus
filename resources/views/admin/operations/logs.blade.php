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
            <h2>Historical Audit Logs</h2>
            <p class="page-subtitle">Complete records of damage reports and penalties issued.</p>
        </div>
        <a href="{{ route('admin.book.suggestions') }}" class="ops-pill-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            Book Recommendations
        </a>
    </div>

    {{-- CARD WITH FILTERS & TABLES --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h4>Historical Audit Logs</h4>
                <p class="card-sub">Review complete audit trail of transactions, damage reports, and penalty records.</p>
            </div>
        </div>

        <div class="filter-bar">
            <div class="tab-bar" style="margin-bottom:0;flex-shrink:0;">
                <button class="tab-btn active" onclick="switchHistTab('activity',this)">Transactions Activity Logs</button>
                <button class="tab-btn" onclick="switchHistTab('damage',this)">Damage Report History</button>
                <button class="tab-btn" onclick="switchHistTab('penalties',this)">Penalties &amp; Fines History</button>
            </div>

            {{-- Activity Filters --}}
            <div id="hist-activity-filters" class="filter-controls">
                <select id="actActionFilter" onchange="filterActivityLogs()" class="filter-select">
                    <option value="all">All Actions</option>
                    <option value="damage">Damage Assessments &amp; Dismissals</option>
                    <option value="penalty">Penalty Resolutions &amp; Payments</option>
                    <option value="overdue">Overdue Fines &amp; Flags</option>
                    <option value="ban">Bans &amp; Unbans</option>
                    <option value="suspend">Suspensions</option>
                    <option value="config">Configuration Changes</option>
                    <option value="return">Kiosk Return Checks</option>
                </select>
                <select id="actSort" onchange="filterActivityLogs()" class="filter-select">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
                <div class="search-wrap search-wrap-sm">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="actSearch" placeholder="Search action, patron, book, or admin..." oninput="filterActivityLogs()">
                </div>
            </div>

            {{-- Damage Filters --}}
            <div id="hist-damage-filters" class="filter-controls" style="display:none;">
                <div class="chip-group">
                    <button data-type="all"     onclick="setDHType('all')"     class="chip active">All</button>
                    <button data-type="student" onclick="setDHType('student')" class="chip">Students</button>
                    <button data-type="faculty" onclick="setDHType('faculty')" class="chip">Faculty</button>
                </div>
                <select id="dhStatus" onchange="filterDH()" class="filter-select">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processed">Processed</option>
                    <option value="dismissed">Dismissed</option>
                </select>
                <select id="dhSort" onchange="filterDH()" class="filter-select">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
                <div class="search-wrap search-wrap-sm">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="dhSearch" placeholder="Search title or patron..." oninput="filterDH()">
                </div>
            </div>

            {{-- Penalty Filters --}}
            <div id="hist-penalty-filters" class="filter-controls" style="display:none;">
                <div class="chip-group">
                    <button data-type="all"     onclick="setPHType('all')"     class="chip active">All</button>
                    <button data-type="student" onclick="setPHType('student')" class="chip">Students</button>
                    <button data-type="faculty" onclick="setPHType('faculty')" class="chip">Faculty</button>
                </div>
                <select id="phStatus" onchange="filterPH()" class="filter-select">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="waived">Waived</option>
                    <option value="recorded_cash">Recorded Cash</option>
                    <option value="disputed">Disputed</option>
                </select>
                <select id="phSort" onchange="filterPH()" class="filter-select">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="amount_desc">Highest Amount</option>
                    <option value="amount_asc">Lowest Amount</option>
                </select>
                <div class="search-wrap search-wrap-sm">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="phSearch" placeholder="Search patron or book..." oninput="filterPH()">
                </div>
            </div>
        </div>

        {{-- Activity Panel --}}
        <div id="hist-activity" class="hist-panel">
            @if(!isset($activityLogs) || $activityLogs->isEmpty())
                <div class="empty-state-content">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <p>No activity logs recorded yet.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Performed By</th>
                            <th>Affected User / Book</th>
                            <th>Description &amp; Remarks</th>
                            <th>Before / After Details</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody id="actBody">
                        @foreach($activityLogs as $log)
                        @php
                            $meta = $log->metadata ?? [];
                            $actionBadge = 'badge-blue';
                            if (str_contains($log->action, 'ban') || str_contains($log->action, 'damage')) $actionBadge = 'badge-red';
                            elseif (str_contains($log->action, 'paid') || str_contains($log->action, 'resolved') || str_contains($log->action, 'unban') || str_contains($log->action, 'unsuspend')) $actionBadge = 'badge-green';
                            elseif (str_contains($log->action, 'warning') || str_contains($log->action, 'overdue') || str_contains($log->action, 'fine')) $actionBadge = 'badge-amber';

                            $affectedUser = $meta['affected_user'] ?? $meta['patron'] ?? null;
                            $affectedBook = $meta['affected_book'] ?? $meta['book_title'] ?? null;
                            $remarks = $meta['remarks'] ?? $meta['reason'] ?? $meta['notes'] ?? null;
                            $before = $meta['before'] ?? null;
                            $after = $meta['after'] ?? null;
                            $searchTarget = strtolower($log->action . ' ' . $log->description . ' ' . $log->performer_name . ' ' . ($affectedUser ?? '') . ' ' . ($affectedBook ?? '') . ' ' . ($remarks ?? ''));
                        @endphp
                        <tr data-action="{{ $log->action }}" data-search="{{ $searchTarget }}" data-ts="{{ $log->created_at?->timestamp ?? 0 }}">
                            <td>
                                <span class="badge {{ $actionBadge }}" style="font-weight:700;">
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td>
                                <strong style="color:#1f2937;">{{ $log->performer_name }}</strong>
                                <div class="text-muted-sm">{{ ucfirst($log->performed_by_type ?? 'Admin') }}</div>
                            </td>
                            <td>
                                @if($affectedUser)
                                    <div style="font-weight:600;color:#111827;">{{ $affectedUser }}</div>
                                @endif
                                @if($affectedBook)
                                    <div class="text-muted-sm" style="font-style:italic;">{{ $affectedBook }}</div>
                                @endif
                                @if(!$affectedUser && !$affectedBook)
                                    <span class="text-muted-sm">—</span>
                                @endif
                            </td>
                            <td style="max-width:320px;white-space:normal;">
                                <div style="font-size:0.84rem;color:#374151;">{{ $log->description }}</div>
                                @if($remarks && !str_contains($log->description, $remarks))
                                    <div style="margin-top:3px;font-size:0.78rem;color:#6b7280;background:#f3f4f6;padding:2px 6px;border-radius:4px;display:inline-block;">
                                        Note: {{ $remarks }}
                                    </div>
                                @endif
                            </td>
                            <td style="font-size:0.78rem;color:#4b5563;max-width:220px;white-space:normal;">
                                @if($before || $after)
                                    @if($before)
                                        <div><span style="color:#991b1b;font-weight:600;">Before:</span> {{ is_array($before) ? json_encode($before, JSON_UNESCAPED_SLASHES) : $before }}</div>
                                    @endif
                                    @if($after)
                                        <div><span style="color:#166534;font-weight:600;">After:</span> {{ is_array($after) ? json_encode($after, JSON_UNESCAPED_SLASHES) : $after }}</div>
                                    @endif
                                @elseif(isset($meta['amount']))
                                    <div><strong class="text-maroon">₱{{ number_format((float)$meta['amount'], 2) }}</strong></div>
                                @else
                                    <span class="text-muted-sm">—</span>
                                @endif
                            </td>
                            <td class="text-muted-sm" style="white-space:nowrap;">
                                {{ $log->created_at?->format('M d, Y g:i A') ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Damage Panel --}}
        <div id="hist-damage" class="hist-panel" style="display:none;">
            @if($damageHistory->isEmpty())
                <div class="empty-state-content">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <p>No damage report records found.</p>
                </div>
            @else
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
                    <tbody id="dhBody">
                        @foreach($damageHistory as $dh)
                        @php
                            $dhP    = $dh->student ?? $dh->faculty;
                            $dhN    = $dhP ? ($dhP->first_name.' '.$dhP->last_name) : '—';
                            $dhType = $dh->student_id ? 'student' : 'faculty';
                            $dhSC   = ['pending'=>'badge-yellow','processed'=>'badge-green','dismissed'=>'badge-gray'][$dh->status] ?? 'badge-gray';
                        @endphp
                        <tr data-book="{{ strtolower($dh->book->title??'') }}" data-patron="{{ strtolower($dhN) }}" data-status="{{ $dh->status }}" data-type="{{ $dhType }}" data-ts="{{ $dh->updated_at?->timestamp ?? 0 }}">
                            <td><strong>{{ $dh->book->title ?? '—' }}</strong></td>
                            <td>{{ $dhN }}</td>
                            <td><span class="badge {{ $dhType==='student'?'badge-blue':'badge-amber' }}">{{ ucfirst($dhType) }}</span></td>
                            <td><span class="badge {{ $dhSC }}">{{ ucfirst($dh->status) }}</span></td>
                            <td>{{ $dh->damage_level ? 'Level '.$dh->damage_level : '—' }}</td>
                            <td class="text-muted-sm">{{ $dh->admin_notes ?? '—' }}</td>
                            <td class="text-muted-sm">{{ $dh->adminUser?->name ?? '—' }}</td>
                            <td class="text-muted-sm">{{ $dh->updated_at?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Penalty Panel --}}
        <div id="hist-penalties" class="hist-panel" style="display:none;">
            @if($penaltyHistory->isEmpty())
                <div class="empty-state-content">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <p>No penalty records found.</p>
                </div>
            @else
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
                    <tbody id="phBody">
                        @foreach($penaltyHistory as $ph)
                        @php
                            $phP    = $ph->student ?? $ph->faculty;
                            $phN    = $phP ? ($phP->first_name.' '.$phP->last_name) : '—';
                            $phType = $ph->student_id ? 'student' : 'faculty';
                            $phSC   = ['pending'=>'badge-yellow','paid'=>'badge-green','recorded_cash'=>'badge-green','waived'=>'badge-blue','disputed'=>'badge-red'][$ph->status] ?? 'badge-gray';
                            $phLabel= ['recorded_cash'=>'Recorded Cash','paid'=>'Paid','waived'=>'Waived','pending'=>'Pending','disputed'=>'Disputed'][$ph->status] ?? ucfirst($ph->status);
                        @endphp
                        <tr data-patron="{{ strtolower($phN) }}" data-book="{{ strtolower($ph->book->title??'') }}" data-status="{{ $ph->status }}" data-type="{{ $phType }}" data-ts="{{ $ph->created_at?->timestamp ?? 0 }}" data-amount="{{ $ph->amount }}">
                            <td><strong>{{ $phN }}</strong></td>
                            <td><span class="badge {{ $phType==='student'?'badge-blue':'badge-amber' }}">{{ ucfirst($phType) }}</span></td>
                            <td>{{ $ph->book->title ?? '—' }}</td>
                            <td class="text-muted-sm">{{ ucfirst(str_replace('_',' ',$ph->penalty_type ?? '—')) }}</td>
                            <td><strong class="text-maroon">₱{{ number_format($ph->amount,2) }}</strong></td>
                            <td><span class="badge {{ $phSC }}">{{ $phLabel }}</span></td>
                            <td class="text-muted-sm">{{ $ph->admin_note ?? '—' }}</td>
                            <td class="text-muted-sm">{{ $ph->created_at?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

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
.filter-bar { padding:1rem 1.5rem; background:#fafafa; border-bottom:1px solid var(--border); display:flex; flex-direction:column; gap:0.875rem; }
.filter-controls { display:flex; align-items:center; gap:0.625rem; flex-wrap:wrap; }
.chip-group { display:flex; gap:2px; background:#e5e7eb; padding:3px; border-radius:20px; flex-shrink:0; }
.chip { padding:4px 12px; border-radius:16px; font-size:0.75rem; font-weight:600; cursor:pointer; border:none; background:transparent; color:#4b5563; }
.chip.active { background:var(--pup-maroon); color:#fff; }
.filter-select { padding:6px 10px; border:1px solid var(--border); border-radius:20px; font-size:0.78rem; font-family:inherit; background:#fff; color:#374151; outline:none; }
.search-wrap { position:relative; display:flex; align-items:center; }
.search-wrap svg { position:absolute; left:10px; }
.search-wrap-sm { flex:1; min-width:180px; max-width:280px; margin-left:auto; }
.search-wrap-sm input { width:100%; padding:0.45rem 0.75rem 0.45rem 1.8rem; font-size:0.78rem; border:1px solid var(--border); border-radius:20px; font-family:inherit; outline:none; }
.tab-bar { display:flex; gap:0.5rem; }
.tab-btn { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.2rem; border-radius:8px; border:2px solid var(--border); background:#fff; color:var(--text-muted); font-weight:600; font-size:0.875rem; cursor:pointer; }
.tab-btn.active { background:var(--pup-maroon); color:#fff; border-color:var(--pup-maroon); }
.table-responsive { overflow-x:auto; }
.data-table { width:100%; border-collapse:collapse; min-width:640px; }
.data-table th { background:var(--pup-maroon); color:#fff; padding:0.875rem 1rem; text-align:left; font-size:0.78rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
.data-table td { padding:0.875rem 1rem; border-bottom:1px solid var(--border); vertical-align:middle; font-size:0.875rem; }
.data-table tbody tr:hover { background:#FEFCE8; }
.text-muted-sm { color:var(--text-muted); font-size:0.82rem; }
.text-maroon { color:var(--pup-maroon); }
.badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:0.72rem; font-weight:600; }
.badge-blue { background:#eff6ff; color:#1d4ed8; }
.badge-amber { background:#fef3c7; color:#92400e; }
.badge-green { background:#d1fae5; color:#065f46; }
.badge-red { background:#fee2e2; color:#991b1b; }
.badge-yellow { background:#fef3c7; color:#92400e; }
.badge-gray { background:#f3f4f6; color:#6b7280; }
.empty-state-content { padding:2.5rem; text-align:center; color:var(--text-muted); }
.empty-state-content svg { opacity:0.4; display:block; margin:0 auto 0.75rem; }
</style>

<script>
function switchHistTab(id, btn) {
    document.querySelectorAll('.hist-panel').forEach(p => p.style.display = 'none');
    const actFilter = document.getElementById('hist-activity-filters');
    const dmgFilter = document.getElementById('hist-damage-filters');
    const penFilter = document.getElementById('hist-penalty-filters');
    if (actFilter) actFilter.style.display = 'none';
    if (dmgFilter) dmgFilter.style.display = 'none';
    if (penFilter) penFilter.style.display = 'none';

    const panel = document.getElementById('hist-' + id);
    if (panel) panel.style.display = 'block';

    const filterBar = document.getElementById('hist-' + id + '-filters');
    if (filterBar) filterBar.style.display = 'flex';

    if (btn) {
        btn.closest('.tab-bar').querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
}

function filterActivityLogs() {
    const actionFilter = (document.getElementById('actActionFilter')?.value || 'all');
    const sort = (document.getElementById('actSort')?.value || 'newest');
    const q = (document.getElementById('actSearch')?.value || '').toLowerCase();
    const rows = Array.from(document.querySelectorAll('#actBody tr'));

    rows.forEach(tr => {
        const action = (tr.dataset.action || '').toLowerCase();
        const search = (tr.dataset.search || '').toLowerCase();

        let matchAction = (actionFilter === 'all');
        if (actionFilter === 'damage') matchAction = action.includes('damage');
        else if (actionFilter === 'penalty') matchAction = action.includes('penalty');
        else if (actionFilter === 'overdue') matchAction = action.includes('overdue') || action.includes('fine');
        else if (actionFilter === 'ban') matchAction = action.includes('ban');
        else if (actionFilter === 'suspend') matchAction = action.includes('suspend');
        else if (actionFilter === 'config') matchAction = action.includes('config');
        else if (actionFilter === 'return') matchAction = action.includes('return') || action.includes('kiosk');

        const matchSearch = !q || search.includes(q);
        tr.style.display = (matchAction && matchSearch) ? '' : 'none';
    });

    sortRows('actBody', sort === 'newest' ? (a,b) => b.dataset.ts - a.dataset.ts : (a,b) => a.dataset.ts - b.dataset.ts);
}

let dhType = 'all';
function setDHType(t) {
    dhType = t;
    document.querySelectorAll('#hist-damage-filters .chip').forEach(b => {
        b.classList.toggle('active', b.dataset.type === t);
    });
    filterDH();
}
function filterDH() {
    const status = document.getElementById('dhStatus').value;
    const sort   = document.getElementById('dhSort').value;
    const q      = (document.getElementById('dhSearch').value || '').toLowerCase();
    const rows   = Array.from(document.querySelectorAll('#dhBody tr'));
    rows.forEach(tr => {
        const ok = (dhType === 'all' || tr.dataset.type === dhType)
                && (status === 'all' || tr.dataset.status === status)
                && (!q || (tr.dataset.book||'').includes(q) || (tr.dataset.patron||'').includes(q));
        tr.style.display = ok ? '' : 'none';
    });
    sortRows('dhBody', sort === 'newest' ? (a,b) => b.dataset.ts - a.dataset.ts : (a,b) => a.dataset.ts - b.dataset.ts);
}

let phType = 'all';
function setPHType(t) {
    phType = t;
    document.querySelectorAll('#hist-penalty-filters .chip').forEach(b => {
        b.classList.toggle('active', b.dataset.type === t);
    });
    filterPH();
}
function filterPH() {
    const status = document.getElementById('phStatus').value;
    const sort   = document.getElementById('phSort').value;
    const q      = (document.getElementById('phSearch').value || '').toLowerCase();
    const rows   = Array.from(document.querySelectorAll('#phBody tr'));
    rows.forEach(tr => {
        const ok = (phType === 'all' || tr.dataset.type === phType)
                && (status === 'all' || tr.dataset.status === status)
                && (!q || (tr.dataset.book||'').includes(q) || (tr.dataset.patron||'').includes(q));
        tr.style.display = ok ? '' : 'none';
    });
    let cmp;
    if (sort === 'amount_desc') cmp = (a,b) => b.dataset.amount - a.dataset.amount;
    else if (sort === 'amount_asc') cmp = (a,b) => a.dataset.amount - b.dataset.amount;
    else if (sort === 'oldest') cmp = (a,b) => a.dataset.ts - b.dataset.ts;
    else cmp = (a,b) => b.dataset.ts - a.dataset.ts;
    sortRows('phBody', cmp);
}

function sortRows(tbodyId, cmpFn) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    const rows = Array.from(tbody.querySelectorAll('tr')).sort(cmpFn);
    rows.forEach(r => tbody.appendChild(r));
}
</script>
@endsection
