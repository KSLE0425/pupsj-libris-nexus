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
            <h2>Library Configuration</h2>
            <p class="page-subtitle">Global penalty rates and borrowing rules.</p>
        </div>
        <a href="{{ route('admin.book.suggestions') }}" class="ops-pill-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            Book Recommendations
        </a>
    </div>

    {{-- CONFIGURATION CARD --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h4>Library Configuration</h4>
                <p class="card-sub">Global penalty rates and borrowing rules.</p>
            </div>
            <span class="card-badge">System Settings</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                <div class="cfg-grid">
                    <div class="form-group">
                        <label for="cfg_damage">Damage Penalty Amount (₱)</label>
                        <input id="cfg_damage" type="number" name="damage_penalty_amount" min="0" step="0.01" value="{{ $libraryConfig['damage_penalty_amount'] }}">
                    </div>
                    <div class="form-group">
                        <label for="cfg_lost">Lost Book Penalty Amount (₱)</label>
                        <input id="cfg_lost" type="number" name="lost_penalty_amount" min="0" step="0.01" value="{{ $libraryConfig['lost_penalty_amount'] }}">
                    </div>
                    <div class="form-group">
                        <label for="cfg_borrow_days">Max Borrow Days</label>
                        <input id="cfg_borrow_days" type="number" name="max_borrow_days_student" min="1" value="{{ $libraryConfig['max_borrow_days_student'] }}">
                    </div>
                    <div class="form-group">
                        <label for="cfg_ban_threshold">Warning Threshold for Auto-Ban</label>
                        <input id="cfg_ban_threshold" type="number" name="warning_ban_threshold" min="1" max="10" value="{{ $libraryConfig['warning_ban_threshold'] ?? 3 }}" required>
                        <span style="font-size:0.75rem;color:var(--text-muted);">Consecutive warnings to trigger automatic ban (Default: 3)</span>
                    </div>
                    <div class="form-group">
                        <label for="cfg_ban_days">Auto-Ban Duration (Days)</label>
                        <input id="cfg_ban_days" type="number" name="ban_duration_days" min="1" max="365" value="{{ $libraryConfig['ban_duration_days'] ?? 30 }}" required>
                        <span style="font-size:0.75rem;color:var(--text-muted);">Number of days a user is banned upon reaching warning threshold</span>
                    </div>
                </div>

                <div class="cfg-checks">
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

                    {{-- Book Requisition Toggle --}}
                    <div class="check-card check-card-maroon" style="border:1.5px solid #fecdd3;background:#fff1f2;border-radius:12px;padding:16px;">
                        <div class="check-row" style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                            <input type="checkbox" id="cfg_book_requests" name="book_requests_enabled" value="1"
                                {{ ($libraryConfig['book_requests_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                                onchange="document.getElementById('bookReqReasonBox').style.display=this.checked?'none':'block'">
                            <label for="cfg_book_requests" style="font-weight:700;color:#9f1239;font-size:0.95rem;cursor:pointer;">
                                Enable Book Acquisition Requests (Requisitions)
                            </label>
                        </div>
                        <p style="font-size:0.82rem;color:#475569;margin:0;line-height:1.45;">
                            When enabled, students and faculty can submit book acquisition requests. When disabled, the submission forms will display your custom pause message.
                        </p>
                        <div id="bookReqReasonBox" style="display:{{ ($libraryConfig['book_requests_enabled'] ?? '1') === '1' ? 'none' : 'block' }};margin-top:12px;">
                            <label for="cfg_book_requests_reason" style="display:block;font-size:0.8rem;font-weight:600;color:#9f1239;margin-bottom:4px;">
                                Pause Reason Message (Shown to users)
                            </label>
                            <input id="cfg_book_requests_reason" type="text" name="book_requests_disabled_reason"
                                   value="{{ $libraryConfig['book_requests_disabled_reason'] ?? 'Book requisitions are temporarily paused by the library administration.' }}"
                                   style="width:100%;padding:8px 12px;border:1px solid #fda4af;border-radius:8px;font-size:0.85rem;">
                        </div>
                    </div>

                    <div class="check-card check-card-amber">
                        <div class="check-row">
                            <input type="checkbox" id="cfg_fines" name="overdue_fines_enabled" value="1"
                                {{ ($libraryConfig['overdue_fines_enabled'] ?? '0') === '1' ? 'checked' : '' }}
                                onchange="document.getElementById('overdueFineRates').style.display=this.checked?'grid':'none'">
                            <label for="cfg_fines">Enable Overdue Fines</label>
                        </div>
                        <p>Automatically calculate and charge daily fines for overdue items. When enabled, kiosk borrows ask patrons to confirm duration.</p>
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
                    <button type="submit" class="btn-maroon">Save Configuration</button>
                </div>
            </form>
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
.alert-success { background:#ECFDF5;color:#065F46;padding:1rem 1.25rem;border-radius:10px;margin-bottom:1.5rem;border-left:4px solid #10B981; }
.alert-error   { background:#FEE2E2;color:#991B1B;padding:1rem 1.25rem;border-radius:10px;margin-bottom:1.5rem;border-left:4px solid #EF4444; }
.card { background:#fff; border-radius:var(--radius); border:1px solid var(--border); overflow:hidden; margin-bottom:1.75rem; box-shadow:var(--shadow); }
.card-header { display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); flex-wrap:wrap; gap:0.75rem; }
.card-header h4 { margin:0; font-weight:600; color:var(--pup-maroon); font-size:1.1rem; }
.card-sub { margin:3px 0 0; font-size:0.8rem; color:var(--text-muted); }
.card-badge { font-size:0.75rem; color:var(--text-muted); background:var(--bg-main); padding:0.375rem 0.75rem; border-radius:20px; }
.card-body { padding:1.5rem; }
.cfg-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-bottom:1.5rem; }
.cfg-checks { display:flex; flex-direction:column; gap:12px; }
.check-card { padding:14px 16px; border-radius:10px; }
.check-card-blue  { background:#eef2ff; border:1px solid #a5b4fc; }
.check-card-amber { background:#fef9ec; border:1px solid #f59e0b; }
.check-row { display:flex; align-items:center; gap:10px; margin-bottom:4px; }
.check-row input { width:16px; height:16px; accent-color:var(--pup-maroon); cursor:pointer; }
.check-row label { font-size:0.875rem; font-weight:700; color:#374151; cursor:pointer; margin:0; }
.check-card p { margin:0 0 0 26px; font-size:0.78rem; color:var(--text-muted); }
.form-group { display:flex; flex-direction:column; gap:5px; }
.form-group label { font-size:0.72rem; font-weight:700; color:#374151; text-transform:uppercase; }
.form-group input { padding:8px 10px; border:1px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:inherit; }
.btn-maroon { padding:10px 24px; border:none; border-radius:8px; background:var(--pup-maroon); color:#fff; font-size:0.875rem; font-weight:700; cursor:pointer; }
</style>
@endsection
