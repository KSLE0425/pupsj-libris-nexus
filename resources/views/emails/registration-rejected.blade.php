<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Update — PUPSJ Libris</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .wrapper { max-width: 520px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.12); }
        .header { background: #800000; padding: 24px 32px; border-bottom: 3px solid #FFC72C; }
        .header h1 { color: #fff; margin: 0; font-size: 18px; font-weight: 700; }
        .header p { color: rgba(255,255,255,0.75); margin: 4px 0 0; font-size: 12px; }
        .body { padding: 28px 32px; }
        .greeting { font-size: 15px; color: #333; margin-bottom: 16px; }
        .status-box { background: #fef2f2; border: 2px solid #fca5a5; border-left: 4px solid #800000; border-radius: 8px; text-align: center; padding: 20px; margin: 20px 0; }
        .status-icon { font-size: 36px; margin-bottom: 6px; }
        .status-title { font-size: 18px; font-weight: 700; color: #800000; margin: 0; }
        .status-sub { font-size: 12px; color: #6b7280; margin: 4px 0 0; }
        .info-row { display: flex; justify-content: space-between; background: #f9f9f9; border-radius: 6px; padding: 10px 14px; margin: 6px 0; font-size: 13px; color: #555; }
        .info-row span { font-weight: 700; color: #333; }
        .note { font-size: 13px; color: #666; background: #f9f9f9; border-left: 3px solid #ccc; padding: 10px 14px; border-radius: 4px; margin-top: 18px; }
        .footer { background: #f0f0f0; padding: 14px 32px; text-align: center; border-top: 1px solid #e0e0e0; }
        .footer p { margin: 0; font-size: 11px; color: #999; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>PUPSJ Libris Nexus</h1>
        <p>Account Registration Update</p>
    </div>
    <div class="body">
        <p class="greeting">Dear <strong>{{ $firstName }}</strong>,</p>
        <p style="font-size:14px; color:#555;">Thank you for registering at <strong>PUPSJ Libris</strong>. After reviewing your registration, we regret to inform you of the following:</p>

        <div class="status-box">
            <div class="status-icon">❌</div>
            <p class="status-title">Account Not Approved</p>
            <p class="status-sub">Your registration request has been declined at this time.</p>
        </div>

        <div class="info-row">Account Type: <span>{{ ucfirst($accountType) }}</span></div>
        <div class="info-row">Status: <span>Rejected</span></div>

        <p class="note">If you believe this was a mistake or would like to appeal, please visit the library in person and speak with the library staff. You are welcome to register again once any issues have been resolved.</p>
    </div>
    <div class="footer">
        <p>Automated message from PUPSJ Libris Nexus &mdash; Do not reply to this email.</p>
    </div>
</div>
</body>
</html>
