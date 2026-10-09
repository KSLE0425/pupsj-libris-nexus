<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: #800000;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #fff;
            font-size: 24px;
        }
        .body-content {
            padding: 30px;
            color: #333;
            line-height: 1.6;
        }
        .reset-btn {
            display: inline-block;
            background: #800000;
            color: #fff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
        }
        .reset-btn:hover {
            background: #5a0000;
        }
        .footer {
            padding: 20px 30px;
            text-align: center;
            color: #888;
            font-size: 12px;
            border-top: 1px solid #eee;
        }
        .expire-note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Password Reset</h1>
        </div>
        <div class="body-content">
            <p>Hello,</p>
            <p>You recently requested to reset your password for your PUPSJ Libris account.</p>
            <p style="text-align: center;">
                <a href="{{ $resetUrl }}" class="reset-btn">Reset Password</a>
            </p>
            <p>If you did not request a password reset, please ignore this email — no changes have been made to your account.</p>
            
            <div class="expire-note">
                ⏰ This password reset link will expire in <strong>60 minutes</strong>.
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} PUPSJ Libris. All rights reserved.</p>
            <p>This is an automated message, please do not reply directly.</p>
        </div>
    </div>
</body>
</html>