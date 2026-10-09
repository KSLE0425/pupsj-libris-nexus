<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Activity Report</title>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; margin: 40px; color: #1f2937; }
        h1 { color: #800000; border-bottom: 3px solid #FFC72C; padding-bottom: 8px; margin-bottom: 4px; }
        h2 { color: #800000; font-size: 1rem; margin: 28px 0 8px; }
        .date-line { color: #6b7280; font-size: 0.92rem; margin-bottom: 24px; }
        .stats-grid { display: flex; gap: 16px; flex-wrap: wrap; margin: 20px 0; }
        .stat-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 20px; min-width: 120px; text-align: center; }
        .stat-num { font-size: 2rem; font-weight: 700; color: #800000; line-height: 1; }
        .stat-lbl { font-size: 0.78rem; color: #6b7280; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 20px; }
        th { background: #800000; color: white; padding: 9px 12px; text-align: left; font-weight: 600; font-size: 0.86rem; }
        td { padding: 9px 12px; border-bottom: 1px solid #e5e7eb; font-size: 0.85rem; }
        tr:nth-child(even) { background: #f9fafb; }
        .empty-msg { padding: 16px; text-align: center; color: #6b7280; font-style: italic; font-size: 0.85rem; }
        .footer { margin-top: 40px; font-size: 0.78rem; color: #6b7280; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 14px; }
    </style>
</head>
<body>
<h1>Daily Activity Report</h1>
<p class="date-line">
    <strong>Date:</strong> {{ $generated_at->format('F d, Y') }} &nbsp;|&nbsp;
    <strong>Generated:</strong> {{ $generated_at->format('h:i A') }}
</p>

{{-- Summary Stats --}}
<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-num">{{ $borrows_today }}</div>
        <div class="stat-lbl">Borrows Today</div>
    </div>
    <div class="stat-box">
        <div class="stat-num">{{ $returns_today }}</div>
        <div class="stat-lbl">Returns Today</div>
    </div>
    <div class="stat-box">
        <div class="stat-num">{{ $active_borrows }}</div>
        <div class="stat-lbl">Currently Active</div>
    </div>
    <div class="stat-box">
        <div class="stat-num">{{ $new_books_today }}</div>
        <div class="stat-lbl">New Books Added</div>
    </div>
    <div class="stat-box">
        <div class="stat-num">{{ $new_donations }}</div>
        <div class="stat-lbl">New Donations</div>
    </div>
    <div class="stat-box">
        <div class="stat-num">{{ $penalties_today }}</div>
        <div class="stat-lbl">Penalties Issued</div>
    </div>
</div>

{{-- Today's Borrows --}}
<h2>Today's Borrows ({{ $borrows_today }})</h2>
@if($borrows_list->isEmpty())
    <p class="empty-msg">No borrowing activity today.</p>
@else
<table>
    <thead>
        <tr>
            <th>Patron</th>
            <th>Book Title</th>
            <th>Author</th>
            <th>Publisher</th>
            <th>Time Borrowed</th>
        </tr>
    </thead>
    <tbody>
        @foreach($borrows_list as $usage)
        <tr>
            <td>
                @if($usage->student)
                    {{ $usage->student->first_name }} {{ $usage->student->last_name }}
                    <br><small style="color:#6b7280;">Student</small>
                @elseif($usage->faculty)
                    {{ $usage->faculty->first_name }} {{ $usage->faculty->last_name }}
                    <br><small style="color:#6b7280;">Faculty</small>
                @else
                    N/A
                @endif
            </td>
            <td>{{ $usage->book->title ?? 'N/A' }}</td>
            <td>{{ $usage->book->author ?? 'N/A' }}</td>
            <td>{{ $usage->book->publisher ?? 'N/A' }}</td>
            <td>{{ $usage->time_in ? $usage->time_in->format('h:i A') : 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Today's Returns --}}
<h2>Today's Returns ({{ $returns_today }})</h2>
@if($returns_list->isEmpty())
    <p class="empty-msg">No return activity today.</p>
@else
<table>
    <thead>
        <tr>
            <th>Patron</th>
            <th>Book Title</th>
            <th>Author</th>
            <th>Publisher</th>
            <th>Time Returned</th>
        </tr>
    </thead>
    <tbody>
        @foreach($returns_list as $usage)
        <tr>
            <td>
                @if($usage->student)
                    {{ $usage->student->first_name }} {{ $usage->student->last_name }}
                    <br><small style="color:#6b7280;">Student</small>
                @elseif($usage->faculty)
                    {{ $usage->faculty->first_name }} {{ $usage->faculty->last_name }}
                    <br><small style="color:#6b7280;">Faculty</small>
                @else
                    N/A
                @endif
            </td>
            <td>{{ $usage->book->title ?? 'N/A' }}</td>
            <td>{{ $usage->book->author ?? 'N/A' }}</td>
            <td>{{ $usage->book->publisher ?? 'N/A' }}</td>
            <td>{{ $usage->time_out ? $usage->time_out->format('h:i A') : 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    PUPSJ Libris Nexus — Daily Report generated {{ $generated_at->format('F d, Y \a\t h:i A') }}
</div>
</body>
</html>
