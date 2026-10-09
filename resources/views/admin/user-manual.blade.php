@extends('layouts.admin')

@section('title', 'System User Manual')

@section('content')
<style>
.manual-container {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 1rem 3rem;
}

.manual-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.manual-header-bar {
    background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
    padding: 24px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #FFC72C;
}

.manual-header-info {
    display: flex;
    align-items: center;
    gap: 14px;
}

.manual-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: rgba(255, 199, 44, 0.2);
    color: #FFC72C;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.manual-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.01em;
}

.manual-subtitle {
    margin: 3px 0 0;
    font-size: 0.82rem;
    color: rgba(255,255,255,0.85);
}

.manual-btn-back {
    background: rgba(255,255,255,0.15);
    color: #ffffff;
    border: 1px solid rgba(255,255,255,0.3);
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.manual-btn-back:hover {
    background: #FFC72C;
    color: #800000;
    border-color: #FFC72C;
}

.manual-body {
    padding: 28px 32px;
    font-size: 0.9rem;
    line-height: 1.65;
    color: #374151;
}

.manual-section {
    background: #fdfbf7;
    border: 1px solid #f3ece1;
    border-left: 4px solid #800000;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 24px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.manual-section:hover {
    box-shadow: 0 4px 12px rgba(128,0,0,0.05);
}

.manual-section-title {
    color: #800000;
    margin: 0 0 10px;
    font-size: 1.05rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
}

.manual-section-tag {
    background: #FFC72C;
    color: #800000;
    font-size: 0.7rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.manual-list {
    margin: 8px 0 0 20px;
    padding: 0;
}

.manual-list li {
    margin-bottom: 8px;
}

.manual-list li:last-child {
    margin-bottom: 0;
}

.manual-list strong {
    color: #111827;
}

.manual-footer {
    padding: 16px 32px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-understood {
    background: #800000;
    color: #ffffff;
    padding: 10px 24px;
    border-radius: 8px;
    border: none;
    font-weight: 700;
    font-size: 0.88rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.18s, transform 0.15s;
}

.btn-understood:hover {
    background: #5a0000;
    color: #fff;
    transform: translateY(-1px);
}
</style>

<div class="manual-container">
    <div class="manual-card">
        <div class="manual-header-bar">
            <div class="manual-header-info">
                <div class="manual-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="manual-title">PUPSJ Libris Nexus — System User Manual</h3>
                    <p class="manual-subtitle">A comprehensive and easy guide for Students, Faculty, Librarians/Admins, and Kiosk Terminals</p>
                </div>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="manual-btn-back">
                &times; Close
            </a>
        </div>

        <div class="manual-body">
            {{-- Section 1: Student Portal --}}
            <div class="manual-section">
                <div class="manual-section-title">
                    <span>1. Student Portal Guide</span>
                    <span class="manual-section-tag">Students</span>
                </div>
                <p>The Student Portal gives learners instant access to academic books, reservation tracking, and history.</p>
                <ul class="manual-list">
                    <li><strong>Registration &amp; Login:</strong> Sign up using your Student Number (e.g., <code>2024-00001-SJ-0</code>), upload your COR, and log in once approved by the librarian.</li>
                    <li><strong>Browse Catalog:</strong> Search the 3-column book catalog with instant live search by title, author, subject, or ISBN.</li>
                    <li><strong>Reserve a Book:</strong> Place a reservation on available or in-demand books to secure your copy for pick-up.</li>
                    <li><strong>Borrowing &amp; Returns:</strong> View active borrows and due dates on your dashboard. Return books at the counter or kiosk.</li>
                    <li><strong>Book Requisitions:</strong> Request new titles or textbooks for library acquisition when accepting requests is open.</li>
                    <li><strong>History &amp; Penalties:</strong> Monitor completed loans, overdue statuses, or damage condition notes.</li>
                </ul>
            </div>

            {{-- Section 2: Faculty Portal --}}
            <div class="manual-section">
                <div class="manual-section-title">
                    <span>2. Faculty Portal Guide</span>
                    <span class="manual-section-tag">Faculty</span>
                </div>
                <p>Designed for instructors and professors with tailored research tools and course-specific literature.</p>
                <ul class="manual-list">
                    <li><strong>Account Setup:</strong> Register with your Employee ID and department specialties.</li>
                    <li><strong>Departmental Catalog:</strong> Filter by subject specialty, Program, and Library of Congress classification.</li>
                    <li><strong>Book Borrowing &amp; Extensions:</strong> Check out instructional references and request borrow period extensions when needed.</li>
                    <li><strong>Curriculum Requisitions:</strong> Submit book purchase requests to support new subject syllabi and research tracks.</li>
                    <li><strong>Borrowing History:</strong> Keep a full record of all teaching references and research volumes.</li>
                </ul>
            </div>

            {{-- Section 3: Admin & Librarian Management --}}
            <div class="manual-section">
                <div class="manual-section-title">
                    <span>3. Librarian &amp; Administrator Portal</span>
                    <span class="manual-section-tag">Admin / Staff</span>
                </div>
                <p>The central hub for managing inventory, patron accounts, circulation operations, and reporting.</p>
                <ul class="manual-list">
                    <li><strong>Books Management:</strong> Add and edit books with Cover Pages, Table of Contents multi-image previews, optional Accession numbers, A-Z classifications, and QR codes.</li>
                    <li><strong>Batch QR Printing:</strong> Select multiple books with checkboxes and click <em>Print Selected QR Codes</em> to generate thermal labels.</li>
                    <li><strong>Unified Returns &amp; Condition Management:</strong> Review kiosk returns, inspect condition flags, issue warnings, apply custom damage penalties, and track fine payments.</li>
                    <li><strong>User Approvals:</strong> Review incoming student COR uploads and faculty registrations from the Account Requests center.</li>
                    <li><strong>Dashboard &amp; Reports:</strong> View real-time analytics and export PDFs, CSVs, XLSX, and JSON files via the Export Reports menu.</li>
                    <li><strong>Audit Log:</strong> Review detailed system activity logs filtered by module, date range, and action type.</li>
                </ul>
            </div>

            {{-- Section 4: Self-Service Kiosk Terminal --}}
            <div class="manual-section">
                <div class="manual-section-title">
                    <span>4. Self-Service Kiosk Terminal Guide</span>
                    <span class="manual-section-tag">Kiosk</span>
                </div>
                <p>An on-site touchscreen station for autonomous patron self-checkout and quick book returns.</p>
                <ul class="manual-list">
                    <li><strong>Touchless Public Return:</strong> Scan the book barcode at the terminal to register an immediate return without logging in.</li>
                    <li><strong>Condition Check:</strong> Patrons can flag book issues (damaged cover, torn pages, missing notes) during return.</li>
                    <li><strong>Sign In &amp; Borrow:</strong> Scan your student/faculty ID or enter credentials to self-borrow books with barcode scanning.</li>
                    <li><strong>Account Creation:</strong> New patrons can quickly register directly from the kiosk terminal.</li>
                </ul>
            </div>
        </div>

        <div class="manual-footer">
            <span style="font-size:0.8rem; color:#6b7280;">Need extra help? Contact the PUPSJ Library Staff at the main desk.</span>
            <a href="{{ route('admin.dashboard') }}" class="btn-understood">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Understood
            </a>
        </div>
    </div>
</div>
@endsection
