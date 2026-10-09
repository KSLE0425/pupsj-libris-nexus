@extends('layouts.admin')

@section('content')
<style>
.docs-page { max-width: 900px; margin: 0 auto; }
.docs-header { border-left: 4px solid #FFC72C; padding-left: 16px; margin-bottom: 28px; }
.docs-header h1 { margin: 0 0 4px; font-size: 1.6rem; font-weight: 700; color: #800000; }
.docs-header p { margin: 0; color: #6b7280; }
.doc-section { background: white; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 24px; overflow: hidden; }
.doc-section-header { padding: 1.125rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 12px; background: linear-gradient(135deg, #800000 0%, #5a0000 100%); }
.doc-section-icon { background: rgba(255,255,255,0.15); border-radius: 8px; padding: 8px; display: flex; align-items: center; justify-content: center; color: white; }
.doc-section-header h2 { margin: 0; font-size: 1.05rem; font-weight: 700; color: white; }
.doc-section-header p { margin: 0; font-size: 0.8rem; color: rgba(255,255,255,0.75); }
.doc-body { padding: 1.5rem; }
.doc-step { display: flex; gap: 16px; margin-bottom: 20px; }
.doc-step:last-child { margin-bottom: 0; }
.step-num { flex-shrink: 0; width: 30px; height: 30px; border-radius: 50%; background: #800000; color: white; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; margin-top: 1px; }
.step-content h4 { margin: 0 0 4px; font-size: 0.9rem; font-weight: 600; color: #1f2937; }
.step-content p { margin: 0; font-size: 0.85rem; color: #4b5563; line-height: 1.55; }
.step-content code { background: #f3f4f6; border-radius: 4px; padding: 1px 6px; font-size: 0.82rem; color: #374151; }
.doc-divider { border: none; border-top: 1px solid #e5e7eb; margin: 16px 0; }
.doc-note { background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; padding: 10px 14px; font-size: 0.82rem; color: #92400E; margin-top: 12px; }
.doc-note strong { display: block; margin-bottom: 3px; }
.doc-flow { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin: 12px 0; font-size: 0.82rem; }
.flow-box { background: #EEF2FF; color: #3730A3; padding: 5px 12px; border-radius: 6px; font-weight: 600; }
.flow-arrow { color: #9CA3AF; font-size: 1rem; }
.flow-box.gold { background: #FEF3C7; color: #92400E; }
.flow-box.green { background: #ECFDF5; color: #065F46; }
.flow-box.red { background: #FEF2F2; color: #991B1B; }
</style>
<div class="docs-page">
    <div class="docs-header">
        <h1>Process Documentation</h1>
        <p>Technical overview of the AI recommendation and barcode/QR code systems used in PUPSJ Libris Nexus.</p>
    </div>

    {{-- AI Recommendation Process --}}
    <div class="doc-section">
        <div class="doc-section-header">
            <div class="doc-section-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div>
                <h2>AI Book Recommendation Process</h2>
                <p>How the system generates personalised book suggestions for borrowers</p>
            </div>
        </div>
        <div class="doc-body">
            <div class="doc-flow">
                <span class="flow-box">Borrowing History</span>
                <span class="flow-arrow">→</span>
                <span class="flow-box">TF-IDF Scoring</span>
                <span class="flow-arrow">→</span>
                <span class="flow-box gold">Open Library API</span>
                <span class="flow-arrow">→</span>
                <span class="flow-box green">Ranked Suggestions</span>
            </div>
            <hr class="doc-divider">
            <div class="doc-step">
                <div class="step-num">1</div>
                <div class="step-content">
                    <h4>Collect Borrowing History</h4>
                    <p>The system retrieves all completed <code>BookUsage</code> records for the current borrower (student or faculty), extracting the titles, authors, and collection types of previously borrowed books.</p>
                </div>
            </div>
            <div class="doc-step">
                <div class="step-num">2</div>
                <div class="step-content">
                    <h4>TF-IDF Text Similarity Scoring</h4>
                    <p><code>AISuggestionService</code> builds a term-frequency / inverse-document-frequency (TF-IDF) vector from the borrower's history corpus. Each book in the catalog is scored against this vector. Books with higher semantic overlap with past reads rank higher.</p>
                </div>
            </div>
            <div class="doc-step">
                <div class="step-num">3</div>
                <div class="step-content">
                    <h4>Open Library Metadata Enrichment</h4>
                    <p>For top-ranked candidates, the service queries the Open Library API (<code>openlibrary.org/api/books</code>) using the book's ISBN to fetch cover images, subjects, and descriptions not stored locally. This enriches the suggestion card shown to the user.</p>
                </div>
            </div>
            <div class="doc-step">
                <div class="step-num">4</div>
                <div class="step-content">
                    <h4>Ranked Suggestion Display</h4>
                    <p>The top N suggestions are returned sorted by score and rendered on the borrower's dashboard. Books already borrowed, condemned, or archived are excluded from suggestions. The borrower can borrow directly from the suggestion card if copies are available.</p>
                </div>
            </div>
            <div class="doc-note">
                <strong>Note for administrators</strong>
                The AI suggestions are entirely local — no personal data is sent to a third-party AI service. Open Library API is queried only for metadata enrichment by ISBN. Suggestions improve as borrowers build a longer history.
            </div>
        </div>
    </div>

    {{-- Barcode/QR Process --}}
    <div class="doc-section">
        <div class="doc-section-header">
            <div class="doc-section-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            </div>
            <div>
                <h2>Barcode &amp; QR Code Process</h2>
                <p>How book labels are generated, scanned, and resolved during borrow/return</p>
            </div>
        </div>
        <div class="doc-body">
            <div class="doc-flow">
                <span class="flow-box">Book Record Created</span>
                <span class="flow-arrow">→</span>
                <span class="flow-box">SHA-256 Hash → qr_hash</span>
                <span class="flow-arrow">→</span>
                <span class="flow-box gold">QR SVG Generated</span>
                <span class="flow-arrow">→</span>
                <span class="flow-box green">Scan → Borrow/Return</span>
            </div>
            <hr class="doc-divider">
            <div class="doc-step">
                <div class="step-num">1</div>
                <div class="step-content">
                    <h4>QR Hash Generation</h4>
                    <p>When a book is added, <code>BookController</code> (or the model observer) computes <code>qr_hash = SHA256(barcode)</code> and stores it on the <code>books</code> table. This hash is the canonical identifier embedded in the QR code, not the raw barcode.</p>
                </div>
            </div>
            <div class="doc-step">
                <div class="step-num">2</div>
                <div class="step-content">
                    <h4>QR Code SVG Rendering</h4>
                    <p><code>QrCodeController@show</code> calls the <code>simplesoftwareio/simple-qrcode</code> package to produce an SVG image encoding the string <code>BOOK:{book_id}</code>. The label is printed via the Print Label button in the admin catalog and attached to the physical book.</p>
                </div>
            </div>
            <div class="doc-step">
                <div class="step-num">3</div>
                <div class="step-content">
                    <h4>Scan at Borrow/Return Station</h4>
                    <p>The librarian's scanner decodes the QR to <code>BOOK:{book_id}</code>. The Archive Scanner page (<code>/admin/archive-scanner</code>) and the borrow/return endpoints both parse this prefix and perform a <code>Book::findOrFail($id)</code> lookup to identify the book.</p>
                </div>
            </div>
            <div class="doc-step">
                <div class="step-num">4</div>
                <div class="step-content">
                    <h4>Borrow &amp; Return Resolution</h4>
                    <p>Once the book is identified, <code>StudentBorrowController</code> or <code>FacultyBorrowController</code> checks borrower eligibility via <code>BorrowEligibilityService</code>, creates a <code>BookUsage</code> record with <code>status = active</code> and <code>time_in = now()</code>, and sets the book's <code>status</code> to <code>borrowed</code>. On return, <code>time_out</code> is stamped and <code>PenaltyCalculationService</code> is invoked if the book is overdue.</p>
                </div>
            </div>
            <div class="doc-note">
                <strong>Archive Scanner usage</strong>
                The Archive Scanner page allows a librarian to scan a book QR code and immediately archive it without navigating the full catalog. It resolves the same <code>BOOK:{id}</code> payload and calls <code>BookController@archive</code>.
            </div>
        </div>
    </div>
</div>
@endsection
