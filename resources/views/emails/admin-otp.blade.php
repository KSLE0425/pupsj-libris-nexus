<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login Code</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .wrapper { max-width: 520px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.12); }
        .header { background: #800000; padding: 24px 32px; border-bottom: 4px solid #FFC72C; }
        .header h1 { color: #fff; margin: 0; font-size: 18px; }
        .header p { color: #f5d5d5; margin: 4px 0 0; font-size: 12px; }
        .body { padding: 28px 32px; }
        .greeting { font-size: 15px; color: #333; margin-bottom: 16px; }
        .code-box { background: #fdf8f0; border: 2px dashed #FFC72C; border-radius: 8px; text-align: center; padding: 24px; margin: 20px 0; }
        .code-box .code { font-size: 42px; font-weight: 900; letter-spacing: 12px; color: #800000; }
        .code-box .validity { font-size: 12px; color: #888; margin-top: 8px; }
        .note { font-size: 13px; color: #666; background: #f9f9f9; border-left: 3px solid #800000; padding: 10px 14px; border-radius: 4px; }
        .footer { background: #f0f0f0; padding: 14px 32px; text-align: center; }
        .footer p { margin: 0; font-size: 11px; color: #999; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>PUPSJ Libris Nexus — Administration</h1>
        <p>Admin Security Passcode</p>
    </div>
    <div class="body">
        <p class="greeting">Hello, <strong>{{ $adminName }}</strong>!</p>
        <p style="font-size:14px; color:#555;">A login attempt was made to the admin panel. Use the one-time code below to complete your login:</p>

        <div class="code-box">
            <div class="code">{{ $code }}</div>
            <div class="validity">Valid for 10 minutes</div>
        </div>

        <p class="note"><strong>Security notice:</strong> If you did not attempt to log in, your account credentials may be compromised. Change your password immediately and contact the system administrator.</p>
    </div>
    <div class="footer">
        <p>Automated security message from PUPSJ Libris Nexus. Do not reply.</p>
    </div>
</div>
</body>
</html>
