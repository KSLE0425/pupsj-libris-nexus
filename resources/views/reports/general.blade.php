<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>General Library Report</title>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; margin: 40px; color: #1f2937; }
        h1 { color: #800000; border-bottom: 3px solid #FFC72C; padding-bottom: 8px; }
        h2 { color: #800000; border-left: 4px solid #FFC72C; padding-left: 10px; margin: 26px 0 10px; font-size: 1.15rem; }
        .period-line { color: #6b7280; font-size: 0.95rem; margin-bottom: 16px; }
        .summary-box { background: #f8f9fc; padding: 16px 20px; border-radius: 10px; margin: 20px 0; border: 1px solid #e5e7eb; }
        .summary-box ul { list-style: none; padding: 0; columns: 2; margin: 0; }
        .summary-box li { margin-bottom: 8px; font-size: 0.9rem; }
        .summary-box li strong { display: inline-block; min-width: 220px; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0 20px; font-size: 0.88rem; }
        th { background: #800000; color: white; padding: 9px 12px; text-align: left; font-weight: 600; font-size: 0.85rem; }
        td { padding: 9px 12px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .empty-msg { padding: 24px; text-align: center; color: #6b7280; font-style: italic; }
        .footer { margin-top: 40px; font-size: 0.8rem; color: #6b7280; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 16px; }
    </style>
</head>
<body>
<h1>General Library Report</h1>
<p class="period-line"><strong>Period:</strong> {{ $periodLabel ?? 'All Time' }}</p>
<p style="font-size: 0.85rem; color:#6b7280; margin-top:-8px;"><strong>Generated:</strong> {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y h:i A') }}</p>

{{-- 1. Summary Overview --}}
@if(empty($options['custom_options_applied']) || !empty($options['summary']))
<div class="summary-box">
    <ul>
        <li><strong>Total Books (copies):</strong> {{ $totalBooks ?? 0 }}</li>
        <li><strong>Total Registered Students:</strong> {{ $totalStudents ?? 0 }}</li>
        <li><strong>Total Registered Faculty:</strong> {{ $totalFaculty ?? 0 }}</li>
        <li><strong>Total Borrows:</strong> {{ $totalBorrows ?? 0 }}</li>
        <li><strong>Total Returns:</strong> {{ $totalReturns ?? 0 }}</li>
        <li><strong>Currently On Loan:</strong> {{ $currentlyOnLoan ?? 0 }}</li>
        <li><strong>Active Borrowers:</strong> {{ $activeUsers ?? 0 }}</li>
        <li><strong>Archived Books:</strong> {{ $archivedBooks ?? 0 }}</li>
        <li><strong>Never Borrowed Books:</strong> {{ $neverBorrowedCount ?? 0 }}</li>
    </ul>
</div>
@endif

{{-- 2. Collection Distribution --}}
@if((empty($options['custom_options_applied']) || !empty($options['collection_dist'])) && !empty($collectionUsage) && $collectionUsage->isNotEmpty())
<h2>Collection Distribution</h2>
<table>
    <thead><tr><th>Collection Category</th><th>Total Borrows</th><th>Circulation Share</th></tr></thead>
    <tbody>
    @php $collTotal = $collectionUsage->sum('total'); @endphp
    @foreach($collectionUsage as $c)
    <tr>
        <td>{{ $c->collection ?: 'General / Uncategorised' }}</td>
        <td style="font-weight:700;">{{ $c->total }}</td>
        <td style="color:#6b7280;">{{ $collTotal ? round($c->total / $collTotal * 100) : 0 }}%</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- 3. Most Borrowed --}}
@if((empty($options['custom_options_applied']) || !empty($options['most_borrowed'])) && !empty($mostBorrowedBooks) && $mostBorrowedBooks->isNotEmpty())
<h2>Most Borrowed Titles (Top 10)</h2>
<table>
    <thead><tr><th>#</th><th>Accession No.</th><th>Title</th><th>Author</th><th>Collection</th><th style="text-align:right;">Borrows</th></tr></thead>
    <tbody>
    @foreach($mostBorrowedBooks as $i => $b)
    <tr>
        <td style="color:#9ca3af;font-weight:700;text-align:center;">{{ $i+1 }}</td>
        <td style="font-family:monospace;font-weight:700;color:#800000;">{{ $b->book->accession_number ?? '—' }}</td>
        <td style="font-weight:600;">{{ $b->book->title ?? '—' }}</td>
        <td style="color:#6b7280;">{{ $b->book->author ?? '—' }}</td>
        <td style="color:#6b7280;font-size:0.88em;">{{ $b->book->collection ?? '—' }}</td>
        <td style="font-weight:700;color:#166534;text-align:right;">{{ $b->total }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- 4. Least Borrowed --}}
@if((empty($options['custom_options_applied']) || !empty($options['least_borrowed'])) && !empty($leastBorrowedBooks) && $leastBorrowedBooks->isNotEmpty())
<h2>Least Borrowed / Unused Books (Bottom 10)</h2>
<table>
    <thead><tr><th>#</th><th>Accession No.</th><th>Title</th><th>Author</th><th>Collection</th><th style="text-align:right;">Borrows</th></tr></thead>
    <tbody>
    @foreach($leastBorrowedBooks as $i => $b)
    <tr>
        <td style="color:#9ca3af;font-weight:700;text-align:center;">{{ $i+1 }}</td>
        <td style="font-family:monospace;font-weight:700;color:#800000;">{{ $b->accession_number ?? '—' }}</td>
        <td style="font-weight:600;">{{ $b->title }}</td>
        <td style="color:#6b7280;">{{ $b->author ?? '—' }}</td>
        <td style="color:#6b7280;font-size:0.88em;">{{ $b->collection ?? '—' }}</td>
        <td style="font-weight:700;color:{{ $b->total == 0 ? '#92400e' : '#c2410c' }};text-align:right;">
            {{ $b->total == 0 ? 'Never' : $b->total }}
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- 5. Top Subjects / Categories --}}
@if((empty($options['custom_options_applied']) || !empty($options['top_subjects'])) && !empty($topSubjects) && $topSubjects->isNotEmpty())
<h2>Top Subjects / Categories</h2>
<table>
    <thead><tr><th>#</th><th>Subject / Category</th><th style="text-align:right;">Total Borrows</th></tr></thead>
    <tbody>
    @foreach($topSubjects as $i => $subj)
    <tr>
        <td style="color:#9ca3af;font-weight:700;text-align:center;width:40px;">{{ $i+1 }}</td>
        <td style="font-weight:600;">{{ $subj->subject }}</td>
        <td style="font-weight:700;color:#800000;text-align:right;">{{ $subj->total }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- 6. Program / Course Usage --}}
@if((empty($options['custom_options_applied']) || !empty($options['program_usage'])) && !empty($courseBreakdown) && $courseBreakdown->isNotEmpty())
<h2>Program / Course Usage</h2>
<table>
    <thead><tr><th>Academic Program</th><th style="text-align:right;">Registered Patrons</th></tr></thead>
    <tbody>
        @foreach($courseBreakdown as $course)
        <tr>
            <td style="font-weight:600;">{{ $course->program ?: 'Unspecified Program' }}</td>
            <td style="font-weight:700;text-align:right;">{{ $course->total }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- 7. Borrowing Trends Timeline --}}
@if((empty($options['custom_options_applied']) || !empty($options['borrowing_trends'])) && !empty($borrowTrend) && $borrowTrend->isNotEmpty())
<h2>Borrowing Trends Timeline</h2>
<table>
    <thead><tr><th>Date</th><th style="text-align:right;">Borrow Volume</th></tr></thead>
    <tbody>
        @foreach($borrowTrend as $row)
        <tr>
            <td>{{ \Carbon\Carbon::parse($row->day)->format('M d, Y (D)') }}</td>
            <td style="font-weight:700;color:#800000;text-align:right;">{{ $row->total }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- 8. New Acquisitions --}}
@if((empty($options['custom_options_applied']) || !empty($options['acquisitions'])) && !empty($newAcquisitions) && $newAcquisitions->isNotEmpty())
<h2>New Acquisitions</h2>
<table>
    <thead><tr><th>Accession No.</th><th>Title</th><th>Author</th><th>Date Added</th></tr></thead>
    <tbody>
        @foreach($newAcquisitions as $book)
        <tr>
            <td style="font-family:monospace;font-weight:700;color:#800000;">{{ $book->accession_number ?? '—' }}</td>
            <td style="font-weight:600;">{{ $book->title }}</td>
            <td style="color:#6b7280;">{{ $book->author ?? '—' }}</td>
            <td style="color:#4b5563;">{{ $book->created_at ? $book->created_at->format('M d, Y') : '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    Report generated by PUPSJ Libris Nexus &mdash; Polytechnic University of the Philippines San Juan
</div>
</body>
</html>
