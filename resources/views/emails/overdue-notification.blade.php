<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Books Notification</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.12); }
        .header { background: #800000; padding: 28px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; letter-spacing: .5px; }
        .header p { color: #f5d5d5; margin: 4px 0 0; font-size: 13px; }
        .body { padding: 28px 32px; }
        .alert-box { background: #fff3cd; border-left: 4px solid #FFC72C; border-radius: 4px; padding: 14px 18px; margin-bottom: 24px; }
        .alert-box p { margin: 0; color: #7a5800; font-size: 14px; }
        .stats { display: flex; gap: 16px; margin-bottom: 24px; }
        .stat-card { flex: 1; background: #fdf1f1; border: 1px solid #e8c5c5; border-radius: 6px; padding: 16px; text-align: center; }
        .stat-card .number { font-size: 32px; font-weight: 700; color: #800000; line-height: 1; }
        .stat-card .label { font-size: 12px; color: #666; margin-top: 4px; }
        .action-btn { display: inline-block; background: #800000; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 5px; font-weight: 700; font-size: 14px; margin-top: 8px; }
        .footer { background: #f0f0f0; padding: 16px 32px; text-align: center; }
        .footer p { margin: 0; font-size: 12px; color: #888; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>PUPSJ Libris Nexus</h1>
        <p>Library Management System — Automated Alert</p>
    </div>
    <div class="body">
        <div class="alert-box">
            <p><strong>Action Required:</strong> Overdue borrows have been detected in the library system. Please review the Transactions panel.</p>
        </div>

        <p style="color:#333; font-size:15px; margin-top:0;">The scheduled overdue scan has flagged the following newly overdue borrowers:</p>

        <div class="stats">
            <div class="stat-card">
                <div class="number">{{ $studentCount }}</div>
                <div class="label">Student Borrower(s)</div>
            </div>
            <div class="stat-card">
                <div class="number">{{ $facultyCount }}</div>
                <div class="label">Faculty Borrower(s)</div>
            </div>
        </div>

        <p style="color:#555; font-size:13px;">
            Total newly flagged: <strong>{{ $studentCount + $facultyCount }}</strong> borrow(s) overdue as of {{ now()->format('F j, Y \a\t g:i A') }}.
        </p>

        <p style="color:#555; font-size:13px;">
            Log in to the admin panel and navigate to <strong>Transactions → Overdue Borrows</strong> to review the list and optionally apply fines or mark items resolved.
        </p>

        <a href="{{ url('/admin/operations') }}" class="action-btn">View Overdue Borrows</a>
    </div>
    <div class="footer">
        <p>This is an automated notification from PUPSJ Libris Nexus. Do not reply to this email.</p>
    </div>
</div>
</body>
</html>
