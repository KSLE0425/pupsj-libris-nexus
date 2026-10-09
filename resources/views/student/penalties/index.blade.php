@extends('layouts.student')

@section('title', 'Penalties & Fines')

@push('styles')
<style>
    .pen-page { max-width: 900px; margin: 0 auto; }
    .pen-page h1 { font-size: 1.6rem; font-weight: 800; color: var(--maroon); margin-bottom: 4px; }
    .pen-page .page-sub { font-size: 0.85rem; color: #666; margin: 0 0 20px; }
    .total-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 20px 24px; margin-bottom: 20px; display: flex; align-items: center; gap: 16px; border-left: 4px solid #800000; }
    .total-label { font-size: 0.78rem; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 4px; }
    .total-amount { font-size: 1.5rem; font-weight: 800; color: var(--maroon); }
    .pen-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden; }
    .pen-table { width: 100%; border-collapse: collapse; font-size: 0.87rem; }
    .pen-table th { text-align: left; padding: 11px 16px; background: rgba(128,0,0,0.05); font-size: 0.75rem; font-weight: 700; color: #800000; border-bottom: 2px solid rgba(128,0,0,0.12); text-transform: uppercase; letter-spacing: 0.3px; }
    .pen-table td { padding: 13px 16px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; color: #333; }
    .pen-table tr:last-child td { border-bottom: none; }
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.74rem; font-weight: 700; white-space: nowrap; }
    .s-pending  { background: #fee2e2; color: #991b1b; }
    .s-paid, .s-resolved { background: #d1fae5; color: #065f46; }
    .s-recorded_cash { background: #ecfdf5; color: #047857; }
    .s-waived   { background: #eff6ff; color: #1e40af; }
    .s-disputed { background: #fff7ed; color: #c2410c; }
    .type-badge { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 0.71rem; font-weight: 600; background: #f3f4f6; color: #374151; }
    .empty-state { text-align: center; padding: 40px 24px; color: #9ca3af; }
    .empty-state svg { margin: 0 auto 12px; display: block; opacity: 0.35; }

    /* ── MOBILE: table → stacked cards ── */
    @media (max-width: 640px) {
        .pen-page h1 { font-size: 1.35rem; }
        .total-card { padding: 16px 18px; }
        .total-amount { font-size: 1.3rem; }
        .pen-card { background: transparent; box-shadow: none; overflow: visible; }
        .pen-table thead { display: none; }
        .pen-table, .pen-table tbody, .pen-table tr, .pen-table td { display: block; width: 100%; }
        .pen-table tr {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border-left: 4px solid #800000;
            margin-bottom: 12px;
            padding: 6px 14px;
        }
        .pen-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 9px 0;
            border-bottom: 1px solid #f3f3f3;
            max-width: none !important;
            text-align: right;
        }
        .pen-table tr td:last-child { border-bottom: none; }
        .pen-table td::before {
            content: attr(data-label);
            font-size: 0.68rem;
            font-weight: 700;
            color: #800000;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            text-align: left;
            flex-shrink: 0;
        }
        .pen-table td.pen-book { display: block; text-align: left; font-size: 0.95rem; color: #1c1917; padding-top: 10px; }
        .pen-table td.pen-book::before { display: block; margin-bottom: 2px; }
    }
</style>
@endpush

@section('content')
<div class="pen-page">
    <h1>Penalties & Fines</h1>
    <p class="page-sub">Amounts due are settled with the administrator at the library.</p>

    {{-- Account Flags --}}
    @if(($suspendedUntil ?? null) && $suspendedUntil->isFuture() || ($pendingDamageCount ?? 0) > 0)
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;">
        @if(($suspendedUntil ?? null) && $suspendedUntil->isFuture())
        <div style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:rgba(128,0,0,0.07);border-left:4px solid #800000;border-radius:10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#800000" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
                <div style="font-size:0.82rem;font-weight:700;color:#800000;">Borrowing Suspended</div>
                <div style="font-size:0.78rem;color:#555;margin-top:2px;">Your borrowing privilege is suspended until <strong>{{ $suspendedUntil->format('M d, Y') }}</strong>. Contact the library administrator.</div>
            </div>
        </div>
        @endif
        @if(($pendingDamageCount ?? 0) > 0)
        <div style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:#fff7ed;border-left:4px solid #f59e0b;border-radius:10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>
                <div style="font-size:0.82rem;font-weight:700;color:#b45309;">{{ $pendingDamageCount }} Pending Damage Report{{ $pendingDamageCount > 1 ? 's' : '' }}</div>
                <div style="font-size:0.78rem;color:#555;margin-top:2px;">A damage report is under review by the administrator. Borrowing is currently restricted.</div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <div class="total-card">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#800000" stroke-width="1.5" style="flex-shrink:0; opacity:0.6;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
            <div class="total-label">Total Pending</div>
            <div class="total-amount">₱{{ number_format($pendingTotal, 2) }}</div>
        </div>
    </div>

    <div class="pen-card">
        @if($penalties->isEmpty())
            <div class="empty-state">
                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="20 6 9 17 4 12"/></svg>
                <p>No penalties on your account.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="pen-table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Type</th>
                            <th>Reason</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($penalties as $p)
                        <tr>
                            <td data-label="Book" class="pen-book" style="font-weight:600;">{{ $p->book->title ?? '—' }}</td>
                            <td data-label="Type">
                                @php
                                    $typeLabel = match($p->penalty_type) {
                                        'damage' => 'Damage',
                                        'late'   => 'Overdue',
                                        'lost'   => 'Lost Book',
                                        default  => ucfirst($p->penalty_type),
                                    };
                                @endphp
                                <span class="type-badge">{{ $typeLabel }}</span>
                            </td>
                            <td data-label="Reason" style="font-size:0.81rem; color:#555; max-width:200px;">
                                @php
                                    $patronNote = $p->patron_note ? trim($p->patron_note) : null;
                                    $adminNote  = $p->admin_note  ? trim($p->admin_note)  : null;
                                @endphp
                                @if($patronNote && $adminNote)
                                    <div>
                                        <span style="font-weight:600;color:#800000;">Patron:</span> {{ Str::limit($patronNote, 80) }}<br>
                                        <span style="font-weight:600;color:#800000;">Admin:</span> {{ Str::limit($adminNote, 80) }}
                                    </div>
                                @elseif($patronNote || $adminNote)
                                    <span title="{{ $patronNote ?? $adminNote }}">{{ Str::limit($patronNote ?? $adminNote, 100) }}</span>
                                @else
                                    <span style="color:#bbb;">—</span>
                                @endif
                            </td>
                            <td data-label="Amount" style="font-weight:700; color:#800000;">₱{{ number_format($p->amount, 2) }}</td>
                            <td data-label="Status">
                                <span class="status-badge s-{{ $p->status }}">{{ ucfirst(str_replace('_', ' ', $p->status)) }}</span>
                            </td>
                            <td data-label="Due Date" style="font-size:0.82rem; color:#666;">{{ $p->due_date?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($penalties->hasPages())
                <div style="padding:14px 20px; border-top:1px solid #f0f0f0;">
                    {{ $penalties->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
