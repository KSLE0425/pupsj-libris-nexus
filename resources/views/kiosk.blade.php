<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PUPSJ Libris - Kiosk Mode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --maroon: #800000; --maroon-dark: #5a0000; --yellow: #FFC72C; --yellow-dark: #e6b328; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: #fff;
            min-height: 100vh;
        }
        .kiosk-header {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            padding: 20px 24px;
            text-align: center;
            border-bottom: 3px solid var(--yellow);
        }
        .kiosk-header h1 { font-weight: 800; font-size: 1.8rem; margin-bottom: 4px; }
        .kiosk-header p { font-size: 0.85rem; opacity: 0.8; }
        .kiosk-container { max-width: 900px; margin: 0 auto; padding: 24px; }
        .section-box {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        .section-box h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 16px; color: #fff; }
        .section-box .form-control {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 12px 16px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        .section-box .form-control:focus {
            background: rgba(255,255,255,0.15);
            border-color: var(--yellow);
            box-shadow: 0 0 0 2px rgba(255,199,44,0.3);
            color: #fff;
        }
        .section-box .form-control::placeholder { color: rgba(255,255,255,0.4); }
        .btn-kiosk {
            background: var(--yellow); color: var(--maroon); font-weight: 700;
            border: none; padding: 12px 24px; border-radius: 8px;
            cursor: pointer; font-family: 'Poppins', sans-serif;
            transition: all 0.2s; width: 100%;
        }
        .btn-kiosk:hover { background: var(--yellow-dark); transform: translateY(-1px); }
        .password-wrapper { position: relative; width: 100%; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 48px !important; }
        .toggle-password-btn {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.28); border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 8px; cursor: pointer; padding: 6px; color: rgba(255, 255, 255, 0.95);
            display: inline-flex !important; align-items: center; justify-content: center;
            z-index: 10; transition: all 0.2s ease; user-select: none; width: 34px; height: 34px;
        }
        .toggle-password-btn:hover { color: var(--yellow); background: rgba(0,0,0,0.45); border-color: var(--yellow); }
        .toggle-password-btn svg { width: 18px; height: 18px; pointer-events: none; }
        .btn-kiosk-outline {
            background: transparent; color: var(--yellow);
            border: 2px solid var(--yellow); font-weight: 600;
            padding: 10px 20px; border-radius: 8px;
            cursor: pointer; font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }
        .btn-kiosk-outline:hover { background: var(--yellow); color: var(--maroon); }
        .borrower-info .name { font-size: 1.2rem; font-weight: 700; }
        .borrower-info .role { font-size: 0.8rem; opacity: 0.7; }
        .scan-input {
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 16px 20px;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 1.2rem;
            text-align: center;
            width: 100%;
            letter-spacing: 2px;
        }
        .scan-input:focus {
            background: rgba(255,255,255,0.15);
            border-color: var(--yellow);
            box-shadow: 0 0 0 3px rgba(255,199,44,0.3);
            color: #fff;
            outline: none;
        }
        .scan-input::placeholder { color: rgba(255,255,255,0.3); }
        .result-box {
            margin-top: 12px; padding: 12px; border-radius: 8px;
            font-size: 0.9rem; font-weight: 500;
        }
        .result-box.success { background: rgba(40,167,69,0.2); color: #75b798; border: 1px solid rgba(40,167,69,0.3); }
        .result-box.error { background: rgba(220,53,69,0.2); color: #ea868f; border: 1px solid rgba(220,53,69,0.3); }
        .result-box.info { background: rgba(13,202,240,0.2); color: #6edff6; border: 1px solid rgba(13,202,240,0.3); }
        .status-indicator { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }
        .status-indicator.online { background: #28a745; }
        @media (max-width: 768px) {
            .kiosk-container { padding: 16px; }
            .section-box, .borrower-info { padding: 16px; }
            .kiosk-header h1 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
    <div class="kiosk-header">
        <h1>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-3px; margin-right:6px;"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            PUPSJ Libris Kiosk
        </h1>
        <p>Self-Service Borrowing & Return Station <span class="status-indicator online"></span> Online</p>
    </div>

    <div class="kiosk-container">
        {{-- Login Section --}}
        <div id="loginSection" class="section-box">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:5px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Student / Faculty Login
            </h3>
            <p style="font-size:0.85rem; opacity:0.7; margin-bottom:16px;">Login to borrow books. Scanning books will automatically borrow them.</p>
            <form id="loginForm">
                @csrf
                <div class="mb-3">
                    <input type="text" id="loginId" class="form-control" placeholder="Enter Student ID or Employee ID" required autofocus>
                </div>
                <div class="mb-3">
                    <div class="password-wrapper">
                        <input type="password" id="loginPassword" class="form-control" placeholder="Enter Password" required>
                        <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('loginPassword', this)" title="Show Password" aria-label="Toggle password visibility">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-kiosk" id="loginBtn">Login</button>
            </form>
            <div id="loginError" class="result-box error" style="display:none; margin-top:12px;"></div>
        </div>

        {{-- Borrower Info --}}
        <div id="borrowerSection" class="section-box d-flex justify-content-between align-items-center flex-wrap gap-2" style="display:none !important;">
            <div>
                <div class="name" id="borrowerName"></div>
                <div class="role" id="borrowerRole"></div>
            </div>
            <button id="logoutBtn" class="btn-kiosk-outline" style="width:auto; padding:8px 20px;">Logout</button>
        </div>

        {{-- Borrowing Scan --}}
        <div id="borrowSection" class="section-box" style="display:none;">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:5px;"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                Scan Book to Borrow
            </h3>
            <p style="font-size:0.85rem; opacity:0.7; margin-bottom:12px;">Scan or type the barcode of the book you want to borrow.</p>
            <input type="text" id="borrowBarcode" class="scan-input" placeholder="Scan barcode or type here..." autocomplete="off">
            <div id="borrowResult" class="result-box" style="display:none;"></div>
        </div>

        {{-- Return Section --}}
        <div class="section-box" style="border: 2px solid rgba(255,199,44,0.3);">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:5px;"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                Return a Book
            </h3>
            <p style="font-size:0.85rem; opacity:0.7; margin-bottom:12px;">No login required. Just scan the book barcode.</p>
            <input type="text" id="returnBarcode" class="scan-input" placeholder="Scan or enter book barcode..." autocomplete="off">
            <div id="returnResult" class="result-box" style="display:none;"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let currentUser = null, userType = null;

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const id = document.getElementById('loginId').value.trim();
            const pw = document.getElementById('loginPassword').value;
            const err = document.getElementById('loginError');
            err.style.display = 'none';
            document.getElementById('loginBtn').disabled = true;
            document.getElementById('loginBtn').textContent = 'Logging in...';

            try {
                let r = await fetch('/student/login', {
                    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                    body:JSON.stringify({student_number:id, password:pw})
                });
                let d = await r.json();
                if(d.success) { userType='student'; currentUser=d.user; onLogin(d.user,'Student'); return; }

                r = await fetch('/faculty/login', {
                    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                    body:JSON.stringify({employee_id:id, password:pw})
                });
                d = await r.json();
                if(d.success) { userType='faculty'; currentUser=d.user; onLogin(d.user,'Faculty'); return; }

                err.textContent='Invalid credentials. Please check your ID and password.';
                err.style.display='block';
            } catch(e) { err.textContent='Connection error.'; err.style.display='block'; }
            finally {
                document.getElementById('loginBtn').disabled=false;
                document.getElementById('loginBtn').textContent='Login';
            }
        });

        function onLogin(u, t) {
            document.getElementById('loginSection').style.display='none';
            document.getElementById('borrowerSection').style.display='flex';
            document.getElementById('borrowSection').style.display='block';
            document.getElementById('borrowerName').textContent=u.first_name+' '+u.last_name;
            document.getElementById('borrowerRole').textContent=t+' | ID: '+(u.student_number||u.employee_id||'N/A');
            document.getElementById('borrowBarcode').focus();
        }

        document.getElementById('logoutBtn').addEventListener('click', async function() {
            try { await fetch('/'+userType+'/logout', {method:'POST', headers:{'X-CSRF-TOKEN':csrf}}); } catch(e){}
            currentUser=null; userType=null;
            document.getElementById('loginSection').style.display='block';
            document.getElementById('borrowerSection').style.display='none';
            document.getElementById('borrowSection').style.display='none';
            document.getElementById('loginId').value='';
            document.getElementById('loginPassword').value='';
            document.getElementById('loginId').focus();
        });

        document.getElementById('borrowBarcode').addEventListener('keydown', async function(e) {
            if(e.key!=='Enter') return;
            e.preventDefault();
            const bc=this.value.trim();
            if(!bc) return;
            const res=document.getElementById('borrowResult');
            res.style.display='none'; this.disabled=true;
            try {
                const br=await fetch('/books/barcode/'+encodeURIComponent(bc));
                if(!br.ok) { showResult(res,'Book not found.','error'); this.value=''; this.disabled=false; this.focus(); return; }
                const bk=await br.json();
                const ep=userType==='student'?'/student/borrow':'/faculty/borrow';
                const r2=await fetch(ep,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({book_id:bk.id})});
                const d2=await r2.json();
                showResult(res,r2.ok?'"'+bk.title+'" borrowed successfully!':(d2.message||'Borrow failed. Try again.'),r2.ok?'success':'error');
            } catch(e) { showResult(res,'Network error. Please try again.','error'); }
            finally { this.value=''; this.disabled=false; this.focus(); }
        });

        document.getElementById('returnBarcode').addEventListener('keydown', async function(e) {
            if(e.key!=='Enter') return;
            e.preventDefault();
            const bc=this.value.trim();
            if(!bc) return;
            const res=document.getElementById('returnResult');
            res.style.display='none'; this.disabled=true;
            try {
                const br=await fetch('/books/barcode/'+encodeURIComponent(bc));
                if(!br.ok) { showResult(res,'Book not found.','error'); this.value=''; this.disabled=false; this.focus(); return; }
                const bk=await br.json();
                const r2=await fetch('/return-scanner/return',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({book_id:bk.id})});
                const d2=await r2.json();
                showResult(res,r2.ok?'"'+bk.title+'" returned successfully!':(d2.message||'Return failed. Try again.'),r2.ok?'success':'error');
            } catch(e) { showResult(res,'Network error. Please try again.','error'); }
            finally { this.value=''; this.disabled=false; this.focus(); }
        });

        function showResult(el,msg,type) { el.textContent=msg; el.className='result-box '+type; el.style.display='block'; }

        function togglePasswordVisibility(fieldId, btn) {
            const input = document.getElementById(fieldId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.innerHTML = isPassword ? `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            ` : `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            `;
            btn.title = isPassword ? 'Hide Password' : 'Show Password';
        }
    </script>
</body>
</html>