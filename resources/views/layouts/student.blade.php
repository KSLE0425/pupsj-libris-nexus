{{-- resources/views/layouts/student.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#800000">
    <title>@yield('title', 'PUPSJ Libris - Student Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #600000;
            --maroon-light: #9a0000;
            --yellow: #F5E642;
            --yellow-dark: #d4c400;
            --yellow-pale: #fffbe6;
            --sidebar-w: 260px;
            --surface-warm: #FAF9F6;
            --ink: #1C1917;
            --info: #0f766e;
            --warn: #b45309;
            --bg: var(--surface-warm);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background: var(--surface-warm);
            color: var(--ink);
        }

        /* ── LAYOUT SHELL ── */
        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--maroon);
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0; top: 0; bottom: 0;
            z-index: 200;
            transition: transform 0.28s cubic-bezier(.4,0,.2,1), width 0.26s ease;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .sidebar::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }

        /* Desktop collapse */
        .sidebar.collapsed { width: 72px; }
        .sidebar.collapsed .brand-text,
        .sidebar.collapsed .ln-badge { display: none !important; }
        .sidebar.collapsed .sidebar-brand { justify-content: center; padding: 16px 14px; }
        .sidebar.collapsed .s-nav-link { font-size: 0; justify-content: center; padding: 0.75rem; gap: 0; }
        .sidebar.collapsed .s-nav-link svg { flex-shrink: 0; }
        .sidebar.collapsed .sidebar-user { display: none; }
        .sidebar.collapsed .collapse-label { display: none; }
        .sidebar.collapsed .sidebar-collapse-btn svg { transform: rotate(180deg); margin: 0 auto; }
        .sidebar.collapsed .main-wrap { margin-left: 72px; }
        .sidebar-collapse-btn {
            display: flex; align-items: center; gap: 6px;
            width: 100%; background: rgba(255,255,255,0.06);
            border: none; border-bottom: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.55); padding: 6px 20px;
            font-size: 0.7rem; font-family: 'Poppins', sans-serif;
            cursor: pointer; transition: background 0.15s, color 0.15s;
        }
        .sidebar-collapse-btn:hover { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.85); }
        .sidebar-collapse-btn svg { transition: transform 0.26s ease; flex-shrink: 0; }
        .main-wrap { transition: margin-left 0.26s ease; }

        .sidebar-brand {
            padding: 22px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            justify-content: flex-start;
        }
        .brand-logo {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: brightness(1.1);
        }
        .brand-text .brand-name {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.15;
        }
        .brand-text .brand-sub {
            font-size: 0.67rem;
            color: rgba(255,255,255,0.5);
            font-weight: 400;
        }

        /* Nav links */
        .sidebar-nav {
            flex: 1;
            padding: 16px 10px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            overflow-y: auto;
        }

        .s-nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 13px;
            border-radius: 8px;
            color: rgba(255,255,255,0.72);
            font-size: 0.87rem;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.18s, color 0.18s;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
        }
        .s-nav-link svg { flex-shrink: 0; opacity: 0.75; transition: opacity 0.18s; }
        .s-nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .s-nav-link:hover svg { opacity: 1; }
        .s-nav-link.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-weight: 600;
        }
        .s-nav-link.active svg { opacity: 1; }

        /* Sidebar user block */
        .sidebar-user {
            padding: 14px 14px 0;
            border-top: 1px solid rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            gap: 11px;
        }
        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 8px;
            background: var(--yellow);
            color: var(--maroon);
            font-weight: 800;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            letter-spacing: -0.5px;
        }
        .user-meta .u-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }
        .user-meta .u-role {
            font-size: 0.68rem;
            color: rgba(255,255,255,0.48);
        }

        .btn-logout-side {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 10px 10px 14px;
            padding: 9px 13px;
            border-radius: 8px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.72);
            font-size: 0.83rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.18s, color 0.18s;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            width: calc(100% - 20px);
        }
        .btn-logout-side:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        /* Mobile overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 199;
        }
        .sidebar-overlay.show { display: block; }

        /* ── MAIN ── */
        .main-wrap {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top bar (mobile only toggle, desktop shows nothing) */
        .topbar {
            display: none;
            align-items: center;
            gap: 12px;
            background: var(--maroon);
            padding: 12px 16px;
            position: sticky;
            top: 0;
            z-index: 150;
            border-bottom: 2px solid var(--yellow);
        }
        .topbar-toggle {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            color: #fff;
        }
        .topbar-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #fff;
        }

        /* ── PAGE CONTENT ── */
        .page-content {
            padding: 32px 32px 40px;
            flex: 1;
        }

        /* ── ALERTS ── */
        .alert {
            border-radius: 9px;
            border: none;
            font-size: 0.84rem;
            font-weight: 500;
            margin-bottom: 18px;
        }
        .alert-success {
            background: rgba(40,167,69,0.1);
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-danger {
            background: rgba(128,0,0,0.07);
            color: var(--maroon);
            border-left: 4px solid var(--maroon);
        }

        /* ── CARDS (used by child pages) ── */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header {
            background: var(--yellow) !important;
            color: var(--maroon) !important;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 13px 18px;
            border-bottom: 2px solid var(--maroon);
        }
        .card-body { padding: 20px 18px; }
        .card-footer {
            background: rgba(245,230,66,0.08);
            border-top: 1px solid rgba(128,0,0,0.1);
            padding: 11px 18px;
        }
        .card-footer a {
            color: var(--maroon);
            font-weight: 600;
            font-size: 0.83rem;
            text-decoration: none;
        }
        .card-footer a:hover { text-decoration: underline; }

        /* Buttons */
        .btn-primary {
            background: var(--maroon);
            border: 2px solid var(--maroon);
            color: #fff;
            font-weight: 600;
            border-radius: 7px;
            font-family: 'Poppins', sans-serif;
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-primary:hover { background: var(--maroon-dark); border-color: var(--maroon-dark); color: #fff; }

        .btn-warning {
            background: var(--yellow);
            border: 2px solid var(--yellow-dark);
            color: var(--maroon);
            font-weight: 700;
            border-radius: 7px;
            font-family: 'Poppins', sans-serif;
        }
        .btn-warning:hover { background: var(--yellow-dark); color: var(--maroon); }

        /* List group */
        .list-group-item {
            border: none;
            border-bottom: 1px solid rgba(128,0,0,0.07);
            padding: 13px 18px;
            transition: background 0.15s;
        }
        .list-group-item:last-child { border-bottom: none; }
        .list-group-item:hover { background: rgba(245,230,66,0.06); }

        .attention-banner {
            background: linear-gradient(90deg, rgba(180,83,9,0.12), rgba(15,118,110,0.08));
            border: 1px solid rgba(128,0,0,0.15);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 18px;
            font-size: 0.88rem;
            color: var(--ink);
        }
        .attention-banner strong { color: var(--maroon); }
        .badge-success {
            background: #28a745;
            color: #fff;
            padding: 3px 10px;
            font-size: 0.71rem;
            font-weight: 600;
            border-radius: 20px;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-wrap {
                margin-left: 0;
            }
            .topbar {
                display: flex;
            }
            .page-content {
                padding: 20px 16px 32px;
            }
        }

        /* Touch responsiveness — removes 300ms tap delay on all interactive elements */
        a, button,
        .s-nav-link,
        .topbar-toggle,
        .btn-primary, .btn-warning,
        .btn-logout-side {
            touch-action: manipulation;
        }
        .ln-badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            flex-shrink: 0;
            opacity: 0.85;
        }
        .ln-badge-text {
            font-size: 0.5rem;
            font-weight: 700;
            color: rgba(255,255,255,0.65);
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        /* Global Pagination & SVG Sizing Fix */
        nav[role="navigation"] svg,
        .pagination svg {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 1rem !important;
            max-height: 1rem !important;
            display: inline-block;
            vertical-align: middle;
        }

        .pagination {
            margin: 0;
            gap: 4px;
            align-items: center;
        }

        .pagination .page-item .page-link {
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 8px !important;
            padding: 0.38rem 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
            background: #fff;
            transition: all 0.15s ease-in-out;
            box-shadow: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            min-height: 34px;
        }

        .pagination .page-item .page-link:hover {
            background: #f8fafc;
            color: var(--maroon);
            border-color: #cbd5e1;
        }

        .pagination .page-item.active .page-link {
            background: var(--maroon) !important;
            border-color: var(--maroon) !important;
            color: #ffffff !important;
            font-weight: 700;
        }

        .pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background: #f8fafc;
            border-color: #e2e8f0;
        }
    </style>
    @stack('styles')
    @include('partials.portal-mobile-styles')
</head>
<body>

<div class="app-shell">

    {{-- ── SIDEBAR ── --}}
    <aside class="sidebar" id="sidebar">

        <a href="{{ route('student.dashboard') }}" class="sidebar-brand">
            <div class="brand-logo">
                <img src="{{ asset('images/pup-logo.png') }}" 
                     alt="PUP Logo" 
                     onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2280%22>📚</text></svg>'">
            </div>
            <div class="brand-text">
                <div class="brand-name">PUPSJ Libris</div>
                <div class="brand-sub">Library Management System</div>
            </div>
        </a>

        <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Toggle sidebar">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
            <span class="collapse-label">Collapse</span>
        </button>

        <nav class="sidebar-nav">
            <a href="{{ route('student.dashboard') }}"
               class="s-nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('student.borrow') }}"
               class="s-nav-link {{ (request()->routeIs('student.borrow') || request()->routeIs('student.book.show')) ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Browse Books
            </a>

            <a href="{{ route('student.penalties.index') }}"
               class="s-nav-link {{ request()->routeIs('student.penalties.*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Penalties & fines
            </a>


            <a href="{{ route('student.requisitions.index') }}"
               class="s-nav-link {{ request()->routeIs('student.requisitions.*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Requisitions
            </a>


            <a href="{{ route('student.history') }}"
               class="s-nav-link {{ request()->routeIs('student.history') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Borrow History
            </a>

            <a href="{{ route('student.profile') }}"
               class="s-nav-link {{ request()->routeIs('student.profile') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                My Profile
            </a>
        </nav>

        {{-- User info --}}
        @auth('student')
        <div class="sidebar-user">
            <div class="user-avatar">
                {{ strtoupper(substr(Auth::guard('student')->user()->first_name ?? 'S', 0, 1)) }}{{ strtoupper(substr(Auth::guard('student')->user()->last_name ?? '', 0, 1)) }}
            </div>
            <div class="user-meta">
                <div class="u-name">{{ Auth::guard('student')->user()->first_name }} {{ Auth::guard('student')->user()->last_name ?? '' }}</div>
                <div class="u-role">Student</div>
            </div>
        </div>

        <form method="POST" action="{{ route('student.logout') }}">
            @csrf
            <button type="submit" class="btn-logout-side">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </button>
        </form>
        @endauth

    </aside>

    {{-- Mobile sidebar overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()" ontouchstart="closeSidebar()"></div>

    {{-- ── MAIN ── --}}
    <div class="main-wrap">

        <header class="portal-header">
            @if(session('impersonated_by') || session('impersonate_admin_id'))
            @php $impName = trim((Auth::guard('student')->user()->first_name ?? 'Student') . ' ' . (Auth::guard('student')->user()->last_name ?? '')); @endphp
            <div class="imp-bar">
                <div class="imp-left">
                    <span class="imp-badge">Admin View</span>
                    <span class="imp-text imp-text-full">You are currently previewing as <strong>{{ $impName }}</strong>.</span>
                    <span class="imp-text imp-text-short"><strong>{{ $impName }}</strong></span>
                </div>
                <form method="POST" action="{{ route('admin.impersonate.stop') }}" style="margin:0;flex-shrink:0;">
                    @csrf
                    <button type="submit" class="imp-exit">
                        <span class="imp-exit-full">Exit to Admin Dashboard &rarr;</span>
                        <span class="imp-exit-short">Exit &rarr;</span>
                    </button>
                </form>
            </div>
            @endif

            {{-- Mobile topbar --}}
            <div class="topbar">
                <button class="topbar-toggle" onclick="openSidebar()" aria-label="Open menu">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <img src="{{ asset('images/pup-logo.png') }}" alt="PUP Logo" class="topbar-logo" onerror="this.style.display='none'">
                <div class="topbar-titles">
                    <span class="topbar-brand">PUPSJ Libris</span>
                    <span class="topbar-title">@yield('title', 'PUPSJ Libris')</span>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="page-content">
            @auth('student')
                @if(\Illuminate\Support\Facades\Schema::hasTable('library_penalties') && !request()->routeIs('student.penalties.*'))
                @php
                    $pendingPenaltiesNav = \App\Models\LibraryPenalty::where('student_id', Auth::guard('student')->id())->where('status', 'pending')->count();
                    $suspNav = Auth::guard('student')->user()->borrowing_suspended_until ?? null;
                @endphp
                @if($pendingPenaltiesNav > 0 || ($suspNav && $suspNav->isFuture()))
                    <div class="attention-banner">
                        @if($pendingPenaltiesNav > 0)
                            <strong>Action needed:</strong> You have {{ $pendingPenaltiesNav }} pending penalty fee(s). Visit <a href="{{ route('student.penalties.index') }}">Penalties & fines</a> and see the administrator.
                        @endif
                        @if($suspNav && $suspNav->isFuture())
                            @if($pendingPenaltiesNav > 0)<br>@endif
                            <strong>Borrowing suspended</strong> until {{ $suspNav->format('M d, Y') }}.
                        @endif
                    </div>
                @endif
                @endif
            @endauth

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>

    </div>{{-- /.main-wrap --}}

    @include('partials.portal-bottom-nav', ['guard' => 'student'])

</div>{{-- /.app-shell --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('show');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }
    (function() {
        var sidebar = document.getElementById('sidebar');
        var btn     = document.getElementById('sidebarCollapseBtn');
        var KEY     = 'sidebar-collapsed';
        function applyCollapse(collapsed) {
            sidebar.classList.toggle('collapsed', collapsed);
            var main = document.querySelector('.main-wrap');
            if (main) main.style.marginLeft = collapsed ? '72px' : '';
        }
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var collapsed = !sidebar.classList.contains('collapsed');
                localStorage.setItem(KEY, collapsed ? '1' : '0');
                applyCollapse(collapsed);
            });
        }
        if (window.innerWidth > 991 && localStorage.getItem(KEY) === '1') {
            applyCollapse(true);
        }
    })();
</script>
@stack('scripts')
</body>
</html>