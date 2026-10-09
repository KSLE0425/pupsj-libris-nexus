@extends('layouts.student')

@section('title', 'New Acquisition Request')

@push('styles')
<style>
    .req-create-page { max-width: 700px; margin: 0 auto; }

    /* ── Page header ── */
    .req-create-header { border-left: 4px solid #FFC72C; padding-left: 14px; margin-bottom: 24px; }
    .req-create-header h1 { font-size: 1.5rem; font-weight: 800; color: #800000; margin: 0 0 3px; }
    .req-create-header p  { font-size: 0.83rem; color: #666; margin: 0; }

    /* ── Card ── */
    .req-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 20px; }
    .req-card-head {
        display: flex; align-items: center; gap: 10px;
        padding: 15px 22px;
        background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
        border-bottom: 3px solid #FFC72C;
    }
    .req-card-head h3 { font-size: 0.92rem; font-weight: 700; color: #fff; margin: 0; }
    .req-card-body { padding: 22px; }

    /* ── Form ── */
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group label { display: block; font-size: 0.83rem; font-weight: 600; color: #333; margin-bottom: 6px; }
    .form-control {
        width: 100%; padding: 10px 13px;
        border: 1.5px solid #e0e0e0; border-radius: 10px;
        font-size: 0.88rem; font-family: 'Poppins', sans-serif;
        transition: border-color 0.2s; outline: none; box-sizing: border-box;
    }
    .form-control:focus { border-color: #800000; box-shadow: 0 0 0 3px rgba(128,0,0,0.1); }
    .form-control::placeholder { color: #bbb; }
    textarea.form-control { min-height: 90px; resize: vertical; }
    .form-hint { font-size: 0.73rem; color: #999; margin-top: 4px; display: block; }
    .req-mark { color: #dc2626; }

    /* ── AI Lookup button ── */
/* ── Reason tiles ── */
    .reasons-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 8px; }
    .reason-tile {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 12px; border: 1.5px solid #e0e0e0; border-radius: 10px;
        cursor: pointer; transition: all 0.15s; font-size: 0.82rem; background: #fff;
        user-select: none;
    }
    .reason-tile:hover { border-color: #800000; background: rgba(128,0,0,0.03); }
    .reason-tile input[type="checkbox"] { display: none; }
    .reason-tile:has(input:checked) { border-color: #800000; background: rgba(128,0,0,0.06); }
    .tile-check {
        width: 18px; height: 18px; border: 2px solid #ccc; border-radius: 4px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s;
    }
    .reason-tile:has(input:checked) .tile-check { background: #800000; border-color: #800000; }
    .reason-tile:has(input:checked) .tile-check::after { content: '✓'; color: #fff; font-size: 11px; font-weight: 700; }
    .reason-tile:has(input:checked) span { color: #800000; font-weight: 600; }

    /* ── Submit row ── */
    .form-actions { display: flex; align-items: center; gap: 14px; justify-content: center; margin-top: 4px; }
    .btn-submit {
        display: inline-flex; align-items: center; gap: 8px;
        background: #FFC72C; color: #800000;
        font-family: 'Poppins', sans-serif; font-size: 0.9rem; font-weight: 700;
        padding: 12px 32px; border-radius: 10px; border: none; cursor: pointer; transition: all 0.2s;
    }
    .btn-submit:hover { background: #e8b800; transform: translateY(-2px); }
    .btn-back {
        display: inline-flex; align-items: center; gap: 6px;
        color: #800000; font-size: 0.84rem; font-weight: 600; text-decoration: none;
    }
    .btn-back:hover { text-decoration: underline; }

    /* ── Errors ── */
    .error-box { background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; }
    .error-box ul { margin: 0; padding-left: 18px; }

    @media (max-width: 600px) {
        .form-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="req-create-page">

    <div class="req-create-header">
        <h1>New Acquisition Request</h1>
        <p>Request a book to be added to the library collection</p>
    </div>

    @if($errors->any())
        <div class="error-box">
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

@if(!($enabled ?? true))
    <div style="background:#fff1f2;border:1.5px solid #fecdd3;border-radius:16px;padding:32px 24px;text-align:center;margin-top:20px;">
        <div style="width:56px;height:56px;background:#ffe4e6;color:#e11d48;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h3 style="color:#9f1239;font-size:1.15rem;font-weight:700;margin-bottom:8px;">Book Requests Currently Unavailable</h3>
        <p style="color:#475569;font-size:0.9rem;max-width:500px;margin:0 auto 20px;">
            {{ $disabledReason ?? 'Book requisitions are temporarily paused by the library administration.' }}
        </p>
        <a href="{{ route('student.requisitions.index') }}" class="btn-submit" style="text-decoration:none;display:inline-flex;">
            View My Requests
        </a>
    </div>
@else
    <form method="POST" action="{{ route('student.requisitions.store') }}" id="reqForm" onsubmit="return handleFormSubmit(event)">
        @csrf
        <input type="hidden" name="justification" id="justificationHidden">

        {{-- Book Information --}}
        <div class="req-card">
            <div class="req-card-head">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FFC72C" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                <h3>Book Information</h3>
            </div>
            <div class="req-card-body">
                {{-- Title + AI --}}
                <div class="form-group">
                    <label for="title">Title <span class="req-mark">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required
                           value="{{ old('title') }}" placeholder="Enter book title">
                </div>

                <div class="form-group">
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" class="form-control"
                           value="{{ old('author') }}" placeholder="Author name">
                </div>

                <div class="form-group">
                    <label for="publisher">Publisher</label>
                    <input type="text" id="publisher" name="publisher" class="form-control"
                           value="{{ old('publisher') }}" placeholder="Publisher name">
                </div>
            </div>
        </div>

        {{-- Justification --}}
        <div class="req-card">
            <div class="req-card-head">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FFC72C" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <h3>Justification</h3>
            </div>
            <div class="req-card-body">
                <div class="form-group">
                    <p style="font-size:0.83rem;font-weight:600;color:#333;margin:0 0 10px;">Reason(s) for Request</p>
                    <div class="reasons-grid">
                        @foreach(['Course requirement','Research material','Reference book','Updated edition needed','Multiple copies needed','Others'] as $reason)
                        <label class="reason-tile">
                            <input type="checkbox" name="reasons[]" value="{{ $reason }}">
                            <div class="tile-check"></div>
                            <span>{{ $reason }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="form-group" style="margin-top:14px;">
                    <label for="justification_text">Additional Details</label>
                    <textarea id="justification_text" class="form-control" rows="3"
                              placeholder="Explain why this book would be valuable for the library…">{{ old('justification') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Submit Request
            </button>
            <a href="{{ route('student.requisitions.index') }}" class="btn-back">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                Back
            </a>
        </div>
    </form>

    {{-- Acknowledgement Modal --}}
    <div id="ackModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;">
        <div style="background:#fff;border-radius:16px;max-width:480px;width:100%;overflow:hidden;box-shadow:0 20px 40px rgba(0,0,0,0.3);animation:dropFade 0.2s ease;">
            <div style="background:linear-gradient(135deg, #800000, #5a0000);padding:20px 24px;color:#fff;border-bottom:3px solid #FFC72C;display:flex;align-items:center;gap:12px;">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#FFC72C" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                <h4 style="margin:0;font-size:1.05rem;font-weight:700;color:#fff;">Request Acknowledgement</h4>
            </div>
            <div style="padding:24px;color:#333;">
                <p style="font-size:0.92rem;line-height:1.5;margin-bottom:14px;color:#1e293b;">
                    Thank you for submitting your book requisition to the <strong>PUP San Juan Library</strong>.
                </p>
                <div style="background:#fef9c3;border:1px solid #fef08a;border-radius:10px;padding:14px 16px;margin-bottom:18px;display:flex;gap:12px;align-items:flex-start;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#854d0e" stroke-width="2" style="flex-shrink:0;margin-top:2px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <div style="font-size:0.84rem;color:#854d0e;line-height:1.45;">
                        <strong>Estimated Processing Time: 1–2 Weeks</strong><br>
                        Our acquisitions committee reviews requests periodically. You will be notified regarding the approval status.
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" onclick="closeAckModal()" style="padding:10px 18px;border:1px solid #cbd5e1;background:#fff;border-radius:8px;font-weight:600;font-size:0.85rem;cursor:pointer;">Review Details</button>
                    <button type="button" onclick="confirmSubmit()" style="padding:10px 22px;background:#800000;color:#FFC72C;border:none;border-radius:8px;font-weight:700;font-size:0.85rem;cursor:pointer;">Confirm &amp; Submit</button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>

<script>
let formReadyToSubmit = false;

function handleFormSubmit(e) {
    if (!formReadyToSubmit) {
        e.preventDefault();
        const reasons = [...document.querySelectorAll('input[name="reasons[]"]:checked')].map(cb => cb.value);
        const extra   = document.getElementById('justification_text').value.trim();
        const parts   = [...reasons];
        if (extra) parts.push(extra);
        document.getElementById('justificationHidden').value = parts.join('; ');
        
        document.getElementById('ackModal').style.display = 'flex';
        return false;
    }
    return true;
}

function closeAckModal() {
    document.getElementById('ackModal').style.display = 'none';
}

function confirmSubmit() {
    formReadyToSubmit = true;
    document.getElementById('reqForm').submit();
}
</script>
@endsection
