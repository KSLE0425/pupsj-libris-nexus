<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Book - PUPSJ Libris</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #5a0000;
            --gold: #FFC72C;
            --bg: #f5f5f5;
            --text: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --radius: 16px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: var(--radius);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        .header { padding: 24px; background: white; text-align: center; border-bottom: 1px solid var(--border); }
        .header h2 { font-size: 1.5rem; color: var(--maroon); font-weight: 700; margin-bottom: 4px; }
        .header p { font-size: 0.85rem; color: var(--text-muted); }
        .scanner-main { padding: 24px; }
        .scan-card {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            border-radius: 12px; padding: 20px; color: white; text-align: center; margin-bottom: 20px;
        }
        .scan-card h3 { font-size: 1.1rem; font-weight: 600; margin-bottom: 6px; }
        .scan-card p { font-size: 0.8rem; opacity: 0.85; }
        #scannerInput { position: absolute; top: -100px; left: -100px; width: 1px; height: 1px; opacity: 0; }
        .result-box { padding: 14px 18px; border-radius: 12px; font-size: 0.85rem; font-weight: 500; text-align: center; margin-bottom: 16px; display: none; }
        .result-success { background: #E8F5E9; color: #2E7D32; border-left: 4px solid #4CAF50; }
        .result-error { background: #FFEBEE; color: #C62828; border-left: 4px solid #F44336; }
        .result-info { background: #E3F2FD; color: #1565C0; border-left: 4px solid #2196F3; }
        .report-damage-btn { margin-top: 8px; display: inline-block; background: transparent; border: 1.5px solid #2E7D32; color: #2E7D32; font-size: 0.78rem; font-weight: 600; padding: 4px 12px; border-radius: 6px; cursor: pointer; font-family: inherit; transition: all 0.18s; }
        .report-damage-btn:hover { background: #2E7D32; color: #fff; }
        .links-container { display: flex; justify-content: center; gap: 1rem; margin-top: 20px; flex-wrap: wrap; }
        .back-link { color: var(--maroon); text-decoration: none; font-size: 0.85rem; font-weight: 600; padding: 8px 16px; border: 2px solid var(--maroon); border-radius: 8px; transition: all 0.2s; }
        .back-link:hover { background: var(--maroon); color: white; }
        #history-card { margin-top: 20px; background: #fafafa; border-radius: 12px; padding: 20px; border: 1px solid var(--border); }
        #history-card h4 { font-size: 0.9rem; color: var(--maroon); margin-bottom: 8px; }
        .history-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        .history-table th { background: var(--maroon); color: white; padding: 8px 10px; text-align: left; }
        .history-table td { padding: 8px 10px; border-bottom: 1px solid #ddd; }

        /* ── Condition Checklist Modal ── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.55);
            display: flex; align-items: center; justify-content: center;
            z-index: 1000;
            padding: 20px;
        }
        .modal-overlay.hidden { display: none; }
        .modal-box {
            background: white;
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.35);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }
        .modal-header {
            background: var(--maroon);
            color: white;
            padding: 18px 22px;
        }
        .modal-header h3 { font-size: 1.05rem; font-weight: 700; margin-bottom: 2px; }
        .modal-header p { font-size: 0.78rem; opacity: 0.8; }
        .modal-book-info {
            background: #fdf8f8;
            border-bottom: 1px solid var(--border);
            padding: 14px 22px;
            font-size: 0.88rem;
        }
        .modal-book-info strong { color: var(--maroon); }
        .modal-body { padding: 18px 22px; }
        .modal-body p { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 12px; }
        .condition-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .condition-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.15s;
            font-size: 0.88rem;
        }
        .condition-item:hover { border-color: var(--maroon); background: #fdf5f5; }
        .condition-item input[type="checkbox"] { display: none; }
        .condition-check {
            width: 20px; height: 20px;
            border: 2px solid #ccc;
            border-radius: 5px;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
            font-size: 13px;
            color: white;
        }
        .condition-item:has(input:checked) { border-color: var(--maroon); background: #fdf5f5; }
        .condition-item:has(input:checked) .condition-check { background: var(--maroon); border-color: var(--maroon); }
        .condition-item:has(input:checked) .condition-check::after { content: '✓'; }
        .modal-footer {
            display: flex;
            gap: 10px;
            padding: 16px 22px;
            border-top: 1px solid var(--border);
            justify-content: flex-end;
        }
        .btn-cancel-modal {
            padding: 10px 22px;
            border: 2px solid var(--border);
            border-radius: 10px;
            background: white;
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-cancel-modal:hover { border-color: #999; }
        .btn-confirm-return {
            padding: 10px 22px;
            border: none;
            border-radius: 10px;
            background: #16a34a;
            color: white;
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            display: flex; align-items: center; gap: 6px;
            transition: all 0.2s;
        }
        .btn-confirm-return:hover { background: #15803d; }
        .flag-note {
            font-size: 0.75rem;
            color: #b45309;
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 10px;
            display: none;
        }
        .condition-item svg { flex-shrink: 0; opacity: 0.65; }
        .condition-item:has(input:checked) svg { opacity: 1; color: var(--maroon); }
        @keyframes conditionModalIn {
            from { opacity: 0; transform: translateY(-24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📚 Book Return</h2>
            <p>Scan or enter the barcode of the book you wish to return</p>
        </div>
        <div class="scanner-main">
            <input type="text" id="scannerInput" autofocus />
            <div class="scan-card">
                <h3>Use your hardware scanner</h3>
                <p>Scan the book's barcode to return it instantly</p>
            </div>
            <div id="result" class="result-box"></div>
            <div id="history-card" class="manual-card" style="display:none;">
                <h4>Borrow History for This Book</h4>
                <div id="historyContent"></div>
            </div>
            <div class="links-container">
                <a href="/dashboard" class="back-link">← Back to Admin Dashboard</a>
            </div>
        </div>
    </div>

    {{-- Condition Checklist Modal --}}
    <div class="modal-overlay hidden" id="conditionModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Return Book — Condition Check</h3>
                <p>Select any issues observed with the book before confirming return.</p>
            </div>
            <div class="modal-book-info" id="modalBookInfo">
                <strong id="modalBookTitle">—</strong><br>
                <span id="modalBorrower" style="color:#555; font-size:0.8rem;"></span>
            </div>
            <div class="modal-body">
                <p>Book condition issues (select all that apply, or leave blank if none):</p>
                <div class="condition-list">
                    <label class="condition-item">
                        <input type="checkbox" name="condition_flags[]" value="Warning">
                        <span class="condition-check"></span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Warning (minor note only)
                    </label>
                    <label class="condition-item">
                        <input type="checkbox" name="condition_flags[]" value="Torn pages">
                        <span class="condition-check"></span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                        Torn pages
                    </label>
                    <label class="condition-item">
                        <input type="checkbox" name="condition_flags[]" value="Damaged cover">
                        <span class="condition-check"></span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                        Damaged cover
                    </label>
                    <label class="condition-item">
                        <input type="checkbox" name="condition_flags[]" value="Missing pages">
                        <span class="condition-check"></span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                        Missing pages
                    </label>
                    <label class="condition-item">
                        <input type="checkbox" name="condition_flags[]" value="Water damage">
                        <span class="condition-check"></span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                        Water damage
                    </label>
                    <label class="condition-item">
                        <input type="checkbox" name="condition_flags[]" value="Writing/highlights on pages">
                        <span class="condition-check"></span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        Writing / highlights on pages
                    </label>
                    <label class="condition-item">
                        <input type="checkbox" name="condition_flags[]" value="Physically damaged">
                        <span class="condition-check"></span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        Physically damaged
                    </label>
                </div>
                <div class="flag-note" id="damageNote">
                    A damage report will be created and added to the Transactions queue for admin review.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel-modal" onclick="cancelReturn()">Cancel</button>
                <button class="btn-confirm-return" onclick="confirmReturn()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Confirm Return
                </button>
            </div>
        </div>
    </div>

    <script>
        let isProcessing = false;
        let pendingBookId = null;

        window.addEventListener('load', () => document.getElementById('scannerInput').focus());
        document.getElementById('scannerInput').focus();
        document.getElementById('scannerInput').select();
        document.addEventListener('click', (e) => {
            if (!document.getElementById('conditionModal').classList.contains('hidden')) return;
            document.getElementById('scannerInput').focus();
        });

        document.getElementById('scannerInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const barcode = this.value.trim();
                if (barcode) { returnBook(barcode); this.value = ''; }
            }
        });

        // Show damage note when any condition is checked
        document.querySelectorAll('input[name="condition_flags[]"]').forEach(cb => {
            cb.addEventListener('change', updateDamageNote);
        });

        function updateDamageNote() {
            const anyChecked = [...document.querySelectorAll('input[name="condition_flags[]"]')].some(cb => cb.checked);
            document.getElementById('damageNote').style.display = anyChecked ? 'block' : 'none';
        }

        async function returnBook(identifier) {
            if (isProcessing) return;
            isProcessing = true;
            showResult('info', 'Processing return…');
            try {
                // Look up book by barcode
                let res = await fetch(`/books/barcode/${encodeURIComponent(identifier)}`);
                if (!res.ok) res = await fetch(`/books/json/${identifier}`);
                if (!res.ok) { showResult('error', 'Book not found. Check the barcode and try again.'); isProcessing = false; return; }
                const book = await res.json();
                pendingBookId = book.id;

                // Auto-return immediately — no modal blocking
                const returnRes = await fetch('/return-scanner/return', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ book_id: book.id, condition_flags: [] })
                });
                const data = await returnRes.json();
                if (returnRes.ok) {
                    showResult('success', `✓ "${book.title}" returned successfully.
                        <button onclick="openDamageModal(${book.id}, ${JSON.stringify(book.title)})" class="report-damage-btn">Report Damage</button>`);
                    fetchHistory(book.id);
                } else {
                    showResult('error', data.message || 'Return failed.');
                }
            } catch (err) {
                showResult('error', 'Network error. Please try again.');
            } finally {
                pendingBookId = null;
                isProcessing = false;
                document.getElementById('scannerInput').focus();
            }
        }

        function openDamageModal(bookId, bookTitle) {
            pendingBookId = bookId;
            document.getElementById('modalBookTitle').textContent = bookTitle || `Book #${bookId}`;
            document.getElementById('modalBorrower').textContent = 'Already returned — adding damage report';
            document.querySelectorAll('input[name="condition_flags[]"]').forEach(cb => cb.checked = false);
            updateDamageNote();
            const modalBox = document.querySelector('#conditionModal .modal-box');
            document.getElementById('conditionModal').classList.remove('hidden');
            modalBox.style.animation = 'none';
            void modalBox.offsetWidth;
            modalBox.style.animation = 'conditionModalIn 0.26s cubic-bezier(0.34,1.45,0.64,1) forwards';
        }

        function cancelReturn() {
            pendingBookId = null;
            document.getElementById('conditionModal').classList.add('hidden');
            document.getElementById('scannerInput').focus();
        }

        async function confirmReturn() {
            if (!pendingBookId || isProcessing) return;
            isProcessing = true;

            const conditionFlags = [...document.querySelectorAll('input[name="condition_flags[]"]:checked')]
                .map(cb => cb.value);

            document.getElementById('conditionModal').classList.add('hidden');

            try {
                const res = await fetch('/return-scanner/return', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ book_id: pendingBookId, condition_flags: conditionFlags })
                });
                const data = await res.json();
                if (res.ok) {
                    const extra = conditionFlags.length > 0 ? ` Damage report created: ${conditionFlags.join(', ')}.` : '';
                    showResult('success', data.message + extra);
                    fetchHistory(pendingBookId);
                } else {
                    showResult('error', data.message || 'Error.');
                }
            } catch (err) {
                showResult('error', 'Network error.');
            } finally {
                pendingBookId = null;
                isProcessing = false;
                document.getElementById('scannerInput').focus();
            }
        }

        async function fetchHistory(bookId) {
            try {
                const res = await fetch(`/books/${bookId}/usage-history`);
                if (res.ok) renderHistory(await res.json());
            } catch (err) {}
        }

        function renderHistory(records) {
            const container = document.getElementById('historyContent');
            if (!records || records.length === 0) {
                container.innerHTML = '<p style="font-size:0.8rem;">No borrowing history yet.</p>';
                document.getElementById('history-card').style.display = 'block';
                return;
            }
            let html = `<table class="history-table"><thead><tr><th>Borrower</th><th>Borrowed At</th><th>Returned At</th><th>Status</th></tr></thead><tbody>`;
            records.forEach(r => {
                const borrower = r.student
                    ? `${r.student.first_name} ${r.student.last_name}`
                    : r.faculty
                        ? `Faculty: ${r.faculty.first_name} ${r.faculty.last_name}`
                        : 'Unknown';
                html += `<tr><td>${borrower}</td><td>${r.time_in ? new Date(r.time_in).toLocaleString() : 'N/A'}</td><td>${r.time_out ? new Date(r.time_out).toLocaleString() : '—'}</td><td>${r.status}</td></tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
            document.getElementById('history-card').style.display = 'block';
        }

        function showResult(type, message) {
            const div = document.getElementById('result');
            div.className = `result-box result-${type}`;
            div.textContent = message;
            div.style.display = 'block';
            setTimeout(() => { div.style.display = 'none'; }, 6000);
        }
    </script>
</body>
</html>
