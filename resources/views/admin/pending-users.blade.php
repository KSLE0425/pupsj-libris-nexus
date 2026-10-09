@extends('layouts.admin')

@section('content')
<div class="user-approvals-page">

    <h2>User Approval Requests</h2>
    <p class="page-subtitle">Review and approve pending student and faculty registrations.</p>

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stats-card" onclick="switchTab('students', document.querySelector('.tab-btn'))" role="button" tabindex="0" aria-label="Go to Students" style="cursor:pointer;transition:transform 0.15s,box-shadow 0.15s,filter 0.15s;" onmouseenter="this.style.transform='translateY(-2px)';this.style.filter='brightness(1.08)';" onmouseleave="this.style.transform='';this.style.filter='';">
            <div class="stats-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stats-info">
                <h3>{{ $students->count() }}</h3>
                <p>Pending Students</p>
            </div>
        </div>
        <div class="stats-card" onclick="switchTab('faculties', document.querySelectorAll('.tab-btn')[1])" role="button" tabindex="0" aria-label="Go to Faculty" style="cursor:pointer;transition:transform 0.15s,box-shadow 0.15s,filter 0.15s;" onmouseenter="this.style.transform='translateY(-2px)';this.style.filter='brightness(1.08)';" onmouseleave="this.style.transform='';this.style.filter='';">
            <div class="stats-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div class="stats-info">
                <h3>{{ $faculties->count() }}</h3>
                <p>Pending Faculty</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tab-bar">
        <button class="tab-btn active" onclick="switchTab('students', this)">
            Students
            @if($students->count()) <span class="tab-badge">{{ $students->count() }}</span> @endif
        </button>
        <button class="tab-btn" onclick="switchTab('faculties', this)">
            Faculty
            @if($faculties->count()) <span class="tab-badge">{{ $faculties->count() }}</span> @endif
        </button>
    </div>

    {{-- Students Table --}}
    <div id="tab-students" class="tab-panel card">
        <div class="card-header">
            <h4>Pending Student Registrations</h4>
            <span class="card-badge">{{ $students->count() }} waiting approval</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student No</th>
                        <th>Name</th>
                        <th>Program</th>
                        <th>Email</th>
                        <th class="col-center">COR</th>
                        <th class="col-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td class="monospace">{{ $student->student_number }}</td>
                        <td><strong>{{ $student->first_name }} {{ $student->last_name }}</strong></td>
                        <td>{{ $student->program }} - {{ $student->year_level }}</td>
                        <td>
                            {{ $student->email }}<br>
                            <small class="pup-email">{{ $student->pup_email }}</small>
                        </td>
                        <td class="col-center">
                            @if($student->cor_file_path)
                                <button type="button" class="doc-link" onclick="viewDocument('{{ asset('storage/' . $student->cor_file_path) }}', '{{ $student->first_name }} {{ $student->last_name }} — COR')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    View COR
                                </button>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td class="col-center">
                            <div class="actions-wrapper">
                                <form method="POST" action="{{ route('admin.account.approve', $student->id) }}" style="display:inline;">
                                    @csrf
                                    <button class="action-btn approve">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.account.reject', $student->id) }}" style="display:inline;">
                                    @csrf
                                    <button class="action-btn reject" onclick="return confirm('Reject this student?')">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><div class="empty-state-content">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p>No pending student requests</p>
                    </div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Faculty Table --}}
    <div id="tab-faculties" class="tab-panel card" style="display:none;">
        <div class="card-header">
            <h4>Pending Faculty Registrations</h4>
            <span class="card-badge">{{ $faculties->count() }} waiting approval</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th class="col-center">Document</th>
                        <th class="col-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faculties as $faculty)
                    <tr>
                        <td class="monospace">{{ $faculty->employee_id }}</td>
                        <td><strong>{{ $faculty->first_name }} {{ $faculty->last_name }}</strong></td>
                        <td>{{ $faculty->department ?? 'N/A' }}</td>
                        <td>{{ $faculty->email }}</td>
                        <td class="col-center">
                            @if($faculty->verification_doc_path)
                                <button type="button" class="doc-link" onclick="viewDocument('{{ asset('storage/' . $faculty->verification_doc_path) }}', '{{ $faculty->first_name }} {{ $faculty->last_name }} — Verification Doc')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    View Doc
                                </button>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td class="col-center">
                            <div class="actions-wrapper">
                                <form method="POST" action="{{ route('admin.faculty.approve', $faculty->id) }}" style="display:inline;">
                                    @csrf
                                    <button class="action-btn approve">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.faculty.reject', $faculty->id) }}" style="display:inline;">
                                    @csrf
                                    <button class="action-btn reject" onclick="return confirm('Reject this faculty?')">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><div class="empty-state-content">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p>No pending faculty requests</p>
                    </div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


</div>

<style>
.user-approvals-page {
    --pup-maroon: #800000;
    --pup-maroon-dark: #5a0000;
    --pup-gold: #FFC72C;
    --bg-main: #f5f5f5;
    --text-muted: #6b7280;
    --border: #e5e7eb;
    --radius: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,0.1);
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}

.user-approvals-page h2 {
    margin: 0 0 8px;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
}

.page-subtitle { color: var(--text-muted); font-size: 0.9375rem; margin-bottom: 1.75rem; }

.alert-success {
    background: #ECFDF5; color: #065F46;
    padding: 1rem 1.25rem; border-radius: 10px;
    margin-bottom: 1.5rem; border-left: 4px solid #10B981;
}

/* Stats row */
.stats-row { display: flex; gap: 1rem; margin-bottom: 1.75rem; flex-wrap: wrap; }

.stats-card {
    flex: 1; min-width: 200px;
    background: linear-gradient(135deg, var(--pup-maroon) 0%, var(--pup-maroon-dark) 100%);
    border-radius: var(--radius); padding: 1.25rem;
    display: flex; align-items: center; gap: 1rem;
    color: white; box-shadow: var(--shadow);
}

.stats-icon {
    background: rgba(255,255,255,0.15); border-radius: 50%;
    width: 56px; height: 56px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

.stats-info h3 { margin: 0; font-size: 2rem; font-weight: 700; line-height: 1.2; }
.stats-info p  { margin: 0; opacity: 0.85; font-size: 0.875rem; }

/* Tabs */
.tab-bar { display: flex; gap: 0.5rem; margin-bottom: 1rem; }

.tab-btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.625rem 1.25rem; border-radius: 8px; border: 2px solid var(--border);
    background: white; color: var(--text-muted);
    font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.2s;
}

.tab-btn.active { background: var(--pup-maroon); color: white; border-color: var(--pup-maroon); }
.tab-btn:hover:not(.active) { border-color: var(--pup-maroon); color: var(--pup-maroon); }

.tab-badge {
    background: var(--pup-gold); color: var(--pup-maroon);
    border-radius: 20px; font-size: 0.7rem; font-weight: 700;
    padding: 0.1rem 0.5rem;
}

/* Card */
.card { background: white; border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; margin-bottom: 1.75rem; box-shadow: var(--shadow); }

.card-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border);
    flex-wrap: wrap; gap: 0.75rem;
}

.card-header h4 { margin: 0; font-weight: 600; color: var(--pup-maroon); font-size: 1.1rem; }

.card-badge {
    font-size: 0.75rem; color: var(--text-muted);
    background: var(--bg-main); padding: 0.375rem 0.75rem; border-radius: 20px;
}

/* Table */
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; min-width: 680px; }

.data-table th {
    background: var(--pup-maroon); color: white;
    padding: 0.875rem 1rem; text-align: left;
    font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
}

.data-table th.col-center { text-align: center; }

.data-table td {
    padding: 0.875rem 1rem; border-bottom: 1px solid var(--border);
    vertical-align: middle; font-size: 0.875rem;
}

.data-table td.col-center { text-align: center; }
.data-table tbody tr:hover { background: #FEFCE8; }
.data-table tbody tr:last-child td { border-bottom: none; }

.monospace { font-family: monospace; color: var(--pup-maroon); font-weight: 600; }
.pup-email  { color: var(--pup-maroon); font-size: 0.7rem; font-weight: 500; }
.text-muted { color: var(--text-muted); }

/* Doc link */
.doc-link {
    display: inline-flex; align-items: center; gap: 0.35rem;
    background: #FEF3C7; color: #92400E;
    padding: 0.3rem 0.75rem; border-radius: 20px;
    text-decoration: none; font-size: 0.75rem; font-weight: 600; transition: all 0.2s;
}
.doc-link:hover { background: #F59E0B; color: white; }

/* Action buttons */
.actions-wrapper { display: flex; justify-content: center; gap: 0.4rem; flex-wrap: wrap; }

.action-btn {
    padding: 0.35rem 0.8rem; border-radius: 6px;
    font-size: 0.75rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s; border: none;
}
.action-btn.approve { background: #D1FAE5; color: #065F46; }
.action-btn.approve:hover { background: #10B981; color: white; }
.action-btn.reject  { background: #FEE2E2; color: #991B1B; }
.action-btn.reject:hover  { background: #EF4444; color: white; }


/* Empty state */
.empty-state-content { padding: 2.5rem; text-align: center; color: var(--text-muted); }
.empty-state-content svg { opacity: 0.4; margin-bottom: 0.75rem; }
.empty-state-content p  { margin: 0; font-size: 0.95rem; }

@media (max-width: 768px) {
    .stats-row { flex-direction: column; }
    .stats-card { padding: 1rem; }
    .card-header { flex-direction: column; text-align: center; }
    .data-table th, .data-table td { padding: 0.625rem 0.5rem; font-size: 0.75rem; }
}
</style>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).style.display = 'block';
    if (btn) btn.classList.add('active');
    // Soft-scroll to the table card
    const card = document.getElementById('tab-' + tab);
    if (card) {
        const top = card.getBoundingClientRect().top + window.scrollY - 80;
        window.scrollTo({ top, behavior: 'smooth' });
    }
}
</script>
@endsection

{{-- ── COR / Document Viewer Modal ── --}}
<div id="docViewerModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.72);backdrop-filter:blur(4px);animation:fadeInModal 0.2s ease;" onclick="if(event.target===this)closeDocViewer()">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:18px;width:90%;max-width:860px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 24px 64px rgba(0,0,0,0.5);overflow:hidden;">
        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 22px;background:linear-gradient(135deg,#800000,#5a0000);border-bottom:3px solid #FFC72C;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFC72C" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span id="docViewerTitle" style="color:#fff;font-weight:700;font-size:0.95rem;">Document Viewer</span>
            </div>
            <div style="display:flex;gap:8px;">
                <a id="docViewerOpenLink" href="#" target="_blank" style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);padding:6px 14px;border-radius:7px;font-size:0.8rem;font-weight:600;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Open in Tab
                </a>
                <button onclick="closeDocViewer()" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;border-radius:7px;width:34px;height:34px;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center;">×</button>
            </div>
        </div>
        {{-- Content --}}
        <div style="flex:1;overflow:auto;min-height:400px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;" id="docViewerBody">
            <div style="text-align:center;color:#9ca3af;padding:40px;">
                <div style="width:40px;height:40px;border:3px solid #800000;border-top-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 12px;"></div>
                <p style="font-size:0.9rem;">Loading document…</p>
            </div>
        </div>
    </div>
</div>
<style>
@keyframes fadeInModal { from{opacity:0} to{opacity:1} }
@keyframes spin { to{transform:rotate(360deg)} }
</style>
<script>
function viewDocument(url, title) {
    const modal = document.getElementById('docViewerModal');
    const titleEl = document.getElementById('docViewerTitle');
    const openLink = document.getElementById('docViewerOpenLink');
    const body = document.getElementById('docViewerBody');
    titleEl.textContent = title || 'Document Viewer';
    openLink.href = url;
    // Show loading state
    body.innerHTML = '<div style="text-align:center;color:#9ca3af;padding:40px;"><div style="width:40px;height:40px;border:3px solid #800000;border-top-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 12px;"></div><p style="font-size:0.9rem;">Loading document…</p></div>';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    // Detect file type by extension
    const isPdf = url.toLowerCase().includes('.pdf');
    if (isPdf) {
        body.innerHTML = '<iframe src="' + url + '" style="width:100%;height:600px;border:none;display:block;"></iframe>';
    } else {
        // Image (jpg, png, jpeg)
        body.innerHTML = '<img src="' + url + '" style="max-width:100%;max-height:70vh;display:block;margin:auto;padding:16px;" onerror="this.parentNode.innerHTML=\'<p style=\'padding:40px;color:#800000;\'>Could not load image. <a href=\''+url+'\' target=\'_blank\'\'>Open in new tab</a></p>\'"/>';
    }
}
function closeDocViewer() {
    const modal = document.getElementById('docViewerModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('docViewerBody').innerHTML = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDocViewer();
});
</script>