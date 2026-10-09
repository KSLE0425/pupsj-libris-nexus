<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Approved — PUPSJ Libris</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .wrapper { max-width: 520px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.12); }
        .header { background: #800000; padding: 24px 32px; border-bottom: 3px solid #FFC72C; }
        .header h1 { color: #fff; margin: 0; font-size: 18px; font-weight: 700; }
        .header p { color: rgba(255,255,255,0.75); margin: 4px 0 0; font-size: 12px; }
        .body { padding: 28px 32px; }
        .greeting { font-size: 15px; color: #333; margin-bottom: 16px; }
        .status-box { background: #ecfdf5; border: 2px solid #6ee7b7; border-left: 4px solid #10b981; border-radius: 8px; text-align: center; padding: 20px; margin: 20px 0; }
        .status-icon { font-size: 36px; margin-bottom: 6px; }
        .status-title { font-size: 18px; font-weight: 700; color: #065f46; margin: 0; }
        .status-sub { font-size: 12px; color: #6b7280; margin: 4px 0 0; }
        .btn-wrap { text-align: center; margin: 24px 0 8px; }
        .btn { display: inline-block; background: #800000; color: #fff; text-decoration: none; padding: 12px 32px; border-radius: 6px; font-size: 14px; font-weight: 700; }
        .note { font-size: 13px; color: #666; background: #f9f9f9; border-left: 3px solid #FFC72C; padding: 10px 14px; border-radius: 4px; margin-top: 18px; }
        .footer { background: #f0f0f0; padding: 14px 32px; text-align: center; border-top: 1px solid #e0e0e0; }
        .footer p { margin: 0; font-size: 11px; color: #999; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>PUPSJ Libris Nexus</h1>
        <p>Account Approval Notification</p>
    </div>
    <div class="body">
        <p class="greeting">Dear <strong>{{ $faculty->first_name }}</strong>,</p>
        <p style="font-size:14px; color:#555;">Great news! Your faculty account for <strong>PUPSJ Libris</strong> has been reviewed and approved by the library admin.</p>

        <div class="status-box">
            <div class="status-icon">✅</div>
            <p class="status-title">Account Approved</p>
            <p class="status-sub">You can now log in using your registered email and password.</p>
        </div>

        <div class="btn-wrap">
            <a href="{{ route('faculty.login') }}" class="btn">Log in to your Account</a>
        </div>

        <p class="note">If you have any questions or concerns, please visit the library or contact the library staff directly.</p>
    </div>
    <div class="footer">
        <p>Automated message from PUPSJ Libris Nexus &mdash; Do not reply to this email.</p>
    </div>
</div>
</body>
</html>
