<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 – Forbidden | PUPSJ Libris</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #FAF9F6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .error-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.09);
            padding: 48px 40px;
            text-align: center;
            max-width: 480px;
            width: 100%;
        }
        .logo-wrap { margin-bottom: 24px; }
        .logo-wrap img { height: 56px; width: auto; object-fit: contain; }
        .error-code {
            font-size: 7rem;
            font-weight: 800;
            color: #800000;
            line-height: 1;
            margin-bottom: 8px;
        }
        .error-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1C1917;
            margin-bottom: 10px;
        }
        .error-desc {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.6;
        }
        .btn-row { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #F5E642;
            color: #800000;
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            padding: 11px 22px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-back:hover { background: #e8d800; transform: translateY(-1px); }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #800000;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            padding: 11px 22px;
            border-radius: 10px;
            text-decoration: none;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-home:hover { background: #600000; transform: translateY(-1px); }
        .divider {
            width: 48px;
            height: 4px;
            background: #800000;
            border-radius: 4px;
            margin: 16px auto 24px;
            opacity: 0.2;
        }
    </style>
</head>
<body>
<div class="error-card">
    <div class="logo-wrap">
        <img src="/images/pup-logo.png"
             alt="PUP Logo"
             onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 56 56%22><rect width=%2256%22 height=%2256%22 rx=%228%22 fill=%22%23800000%22/><text x=%2250%25%22 y=%2255%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2228%22>📚</text></svg>'">
    </div>
    <div class="error-code">403</div>
    <div class="divider"></div>
    <div class="error-title">Access Forbidden</div>
    <div class="error-desc">You don't have permission to access this page. Please contact the library administrator if you believe this is an error.</div>
    <div class="btn-row">
        <button class="btn-back" onclick="history.back()">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Go Back
        </button>
        <a href="/" class="btn-home">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>
    </div>
</div>
</body>
</html>
