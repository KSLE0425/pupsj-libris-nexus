<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Circulation Report</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 36px; color: #1f2937; font-size: 13px; }

        h1 { color: #800000; border-bottom: 4px solid #FFC72C; padding-bottom: 8px; margin-bottom: 4px; font-size: 22px; }
        h2 { color: #800000; border-left: 5px solid #FFC72C; padding-left: 12px; margin: 28px 0 12px; font-size: 15px; }
        h3 { color: #374151; margin: 18px 0 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }

        .meta { color: #6b7280; font-size: 12px; margin-bottom: 20px; }

        /* Summary stats */
        .stats-row { display: flex; gap: 12px; margin: 16px 0 24px; flex-wrap: wrap; }
        .stat-box { flex: 1; min-width: 120px; background: #f8f9fc; border-radius: 8px; padding: 14px 16px; border-left: 4px solid #800000; }
        .stat-box.warn { border-left-color: #f59e0b; }
        .stat-box.success { border-left-color: #10b981; }
        .stat-box .val { font-size: 26px; font-weight: 800; color: #800000; line-height: 1; }
        .stat-box.warn .val { color: #b45309; }
        .stat-box.success .val { color: #065f46; }
        .stat-box .lbl { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }

        /* Collection bar */
        .coll-row { display: flex; align-items: center; gap: 10px; margin-bottom: 7px; }
        .coll-name { width: 160px; font-size: 12px; font-weight: 600; color: #374151; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .coll-bar-wrap { flex: 1; background: #f3f4f6; border-radius: 4px; height: 10px; }
        .coll-bar { height: 10px; border-radius: 4px; background: linear-gradient(90deg, #800000, #c0392b); }
        .coll-count { width: 36px; text-align: right; font-size: 12px; font-weight: 700; color: #374151; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin: 8px 0 20px; }
        th { background: #800000; color: #fff; padding: 9px 12px; text-align: left; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 9px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }

        .rank { color: #9ca3af; font-weight: 700; text-align: center; }
        .badge-high  { background: #dcfce7; color: #166534; padding: 2px 9px; border-radius: 20px; font-weight: 700; font-size: 11px; }
        .badge-low   { background: #fff7ed; color: #c2410c; padding: 2px 9px; border-radius: 20px; font-weight: 700; font-size: 11px; }
        .badge-never { background: #fef3c7; color: #92400e; padding: 2px 9px; border-radius: 20px; font-weight: 700; font-size: 11px; }

        /* Trend table */
        .trend-table td { font-size: 11px; }

        .alert-box { background: #fefce8; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 6px; margin: 16px 0; font-size: 12px; color: #92400e; }
        .two-col { display: flex; gap: 20px; }
        .two-col > div { flex: 1; min-width: 0; }

        .footer { margin-top: 40px; font-size: 11px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 14px; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

<h1>Circulation Report</h1>
<p class="meta">
    <strong>Period:</strong> {{ $periodLabel ?? 'All Time' }} &nbsp;|&nbsp;
    <strong>Generated:</strong> {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y h:i A') }}
</p>

@if(empty($options['custom_options_applied']) || !empty($options['summary']))
{{-- Summary Stat Boxes --}}
<div class="stats-row">
    <div class="stat-box">
        <div class="val">{{ $totalBorrows }}</div>
        <div class="lbl">Total Borrows</div>
    </div>
    <div class="stat-box success">
        <div class="val">{{ $totalReturns }}</div>
        <div class="lbl">Total Returns</div>
    </div>
    <div class="stat-box">
        <div class="val">{{ $currentlyOnLoan }}</div>
        <div class="lbl">Currently On Loan</div>
    </div>
    <div class="stat-box warn">
        <div class="val">{{ $neverBorrowedCount }}</div>
        <div class="lbl">Never Borrowed</div>
    </div>
</div>

@if($neverBorrowedCount > 0 && (empty($options['custom_options_applied']) || !empty($options['least_borrowed'])))
<div class="alert-box">
    <strong>Note:</strong> {{ $neverBorrowedCount }} book{{ $neverBorrowedCount !== 1 ? 's' : '' }}
    in the active catalog {{ $neverBorrowedCount !== 1 ? 'have' : 'has' }} never been borrowed.
    Consider reviewing these titles for relevance or promotion to increase circulation.
</div>
@endif
@endif

{{-- Borrows by Collection --}}
@if((empty($options['custom_options_applied']) || !empty($options['collection_dist'])) && !empty($collectionUsage) && $collectionUsage->isNotEmpty())
<h2>Borrows by Collection Type</h2>
    @php $collMax = $collectionUsage->max('total') ?: 1; $collTotal = $collectionUsage->sum('total'); @endphp
    @foreach($collectionUsage as $c)
    <div class="coll-row">
        <div class="coll-name">{{ $c->collection ?: 'Uncategorised' }}</div>
        <div class="coll-bar-wrap">
            <div class="coll-bar" style="width:{{ round($c->total / $collMax * 100) }}%;"></div>
        </div>
        <div class="coll-count">{{ $c->total }}</div>
        <div style="width:40px;font-size:11px;color:#9ca3af;">{{ $collTotal ? round($c->total / $collTotal * 100) : 0 }}%</div>
    </div>
    @endforeach
@endif

{{-- Borrow Trend --}}
@if((empty($options['custom_options_applied']) || !empty($options['borrowing_trends'])) && !empty($borrowTrend) && $borrowTrend->isNotEmpty())
<h2>Daily Borrow Trend</h2>
<table class="trend-table">
    <thead><tr><th>Date</th><th style="text-align:right;">Borrows</th></tr></thead>
    <tbody>
    @foreach($borrowTrend as $row)
    <tr>
        <td>{{ \Carbon\Carbon::parse($row->day)->format('M d, Y') }}</td>
        <td style="text-align:right;font-weight:600;">{{ $row->total }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- Most Borrowed --}}
@if(empty($options['custom_options_applied']) || !empty($options['most_borrowed']))
<h2>Most Borrowed Books (Top 20)</h2>
@if($mostBorrowedBooks->isEmpty())
    <p style="color:#9ca3af;">No borrow data for this period.</p>
@else
<table>
    <thead><tr>
        <th style="width:30px;">#</th>
        <th>Accession No.</th>
        <th>Title</th>
        <th>Author</th>
        <th>Collection</th>
        <th style="text-align:right;">Borrows</th>
    </tr></thead>
    <tbody>
    @foreach($mostBorrowedBooks as $i => $b)
    <tr>
        <td class="rank">{{ $i + 1 }}</td>
        <td style="font-family:monospace;font-weight:700;color:#800000;">{{ $b->book->accession_number ?? '—' }}</td>
        <td style="font-weight:600;">{{ $b->book->title ?? '—' }}</td>
        <td style="color:#6b7280;">{{ $b->book->author ?? '—' }}</td>
        <td style="color:#6b7280;font-size:11px;">{{ $b->book->collection ?? '—' }}</td>
        <td style="text-align:right;"><span class="badge-high">{{ $b->total }}</span></td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif
@endif

{{-- Least Borrowed --}}
@if(empty($options['custom_options_applied']) || !empty($options['least_borrowed']))
<div class="page-break"></div>
<h2>Least Borrowed / Unused Books (Bottom 20)</h2>
<p style="font-size:12px;color:#6b7280;margin:-8px 0 12px;">
    Books with fewest borrows in the selected period, including those never borrowed (marked "Never").
    These may need promotion, relocation, or review.
</p>
@if($leastBorrowedBooks->isEmpty())
    <p style="color:#9ca3af;">No books found.</p>
@else
<table>
    <thead><tr>
        <th style="width:30px;">#</th>
        <th>Accession No.</th>
        <th>Title</th>
        <th>Author</th>
        <th>Collection</th>
        <th style="text-align:right;">Borrows</th>
    </tr></thead>
    <tbody>
    @foreach($leastBorrowedBooks as $i => $b)
    <tr>
        <td class="rank">{{ $i + 1 }}</td>
        <td style="font-family:monospace;font-weight:700;color:#800000;">{{ $b->accession_number ?? '—' }}</td>
        <td style="font-weight:600;">{{ $b->title }}</td>
        <td style="color:#6b7280;">{{ $b->author ?? '—' }}</td>
        <td style="color:#6b7280;font-size:11px;">{{ $b->collection ?? '—' }}</td>
        <td style="text-align:right;">
            @if($b->total == 0)
                <span class="badge-never">Never</span>
            @else
                <span class="badge-low">{{ $b->total }}</span>
            @endif
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif
@endif

<div class="footer">
    PUPSJ Libris Nexus — Circulation Report &nbsp;|&nbsp; {{ $periodLabel ?? 'All Time' }}
</div>
</body>
</html>
