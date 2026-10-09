{{-- resources/views/layouts/opac.blade.php --}}
{{-- Unified OPAC layout for Student and Faculty users --}}
@php
    $isStudent = auth('student')->check();
    $isFaculty = auth('faculty')->check();
    $opacUser  = $isStudent ? auth('student')->user() : ($isFaculty ? auth('faculty')->user() : null);
    $roleLabel = $isStudent ? 'Student' : ($isFaculty ? 'Faculty' : 'Guest');

    // Route helpers
    $homeRoute        = $isStudent ? route('student.dashboard')             : ($isFaculty ? route('faculty.dashboard')             : '/');
    $borrowRoute      = $isStudent ? route('student.borrow')                : ($isFaculty ? route('faculty.borrow')                : '/');
    $penaltyRoute     = $isStudent ? route('student.penalties.index')       : ($isFaculty ? route('faculty.penalties.index')       : null);
    $requisitionRoute = $isStudent ? route('student.requisitions.index')    : ($isFaculty ? route('faculty.requisitions.index')    : null);
    $profileRoute     = $isStudent ? route('student.profile')               : ($isFaculty ? route('faculty.profile')               : null);
    $logoutRoute      = $isStudent ? route('student.logout')                : ($isFaculty ? route('faculty.logout')                : null);

    $initials = $opacUser
        ? strtoupper(substr($opacUser->first_name ?? 'U', 0, 1) . substr($opacUser->last_name ?? '', 0, 1))
        : '--';
    $fullName = $opacUser ? (($opacUser->first_name ?? '') . ' ' . ($opacUser->last_name ?? '')) : 'Guest';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#800000">
    <title>@yield('title', 'PUPSJ Libris')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #600000;
            --yellow: #F5E642;
            --yellow-dark: #d4c400;
            --sidebar-w: 260px;
            --surface-warm: #FAF9F6;
            --ink: #1C1917;
            --text-muted: #6b7280;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: 'Poppins', sans-serif; background: var(--surface-warm); color: var(--ink); }
        .app-shell { display: flex; min-height: 100vh; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w); background: var(--maroon); color: white;
            display: flex; flex-direction: column;
            overflow-x: hidden;
            transition: transform 0.28s cubic-bezier(.4,0,.2,1), width 0.26s ease !important;
            position: fixed; top: 0; left: 0; height: 100vh;
            z-index: 200; transition: transform 0.28s cubic-bezier(.4,0,.2,1);
            overflow-y: auto; overflow-x: hidden;
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: 11px;
            padding: 22px 20px 18px; text-decoration: none; color: white;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            justify-content: space-between;
        }
        .brand-logo { width: 38px; height: 38px; flex-shrink: 0; }
        .brand-logo img { width: 100%; height: 100%; object-fit: contain; filter: brightness(1.1); }
        .brand-text { flex: 1; }
        .brand-name { font-size: 1.05rem; font-weight: 800; letter-spacing: -0.3px; line-height: 1.1; }
        .brand-sub  { font-size: 0.65rem; opacity: 0.65; margin-top: 2px; }
        .ln-badge { display: flex; flex-direction: column; align-items: center; gap: 2px; flex-shrink: 0; opacity: 0.85; }
        .ln-badge-text { font-size: 0.5rem; font-weight: 700; color: rgba(255,255,255,0.65); letter-spacing: 0.8px; text-transform: uppercase; }

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

        .sidebar-nav { flex: 1; padding: 16px 10px; display: flex; flex-direction: column; gap: 2px; }
        .s-nav-link {
            display: flex; align-items: center; gap: 11px;
            padding: 10px 13px; border-radius: 9px;
            color: rgba(255,255,255,0.88); text-decoration: none;
            font-weight: 500; font-size: 0.9rem; transition: background 0.18s;
        }
        .s-nav-link:hover { background: rgba(255,255,255,0.09); color: white; }
        .s-nav-link.active { background: var(--yellow); color: var(--maroon); font-weight: 700; }
        .s-nav-link.active svg { color: var(--maroon); }
        .role-chip {
            display: inline-block; font-size: 0.6rem; font-weight: 700;
            padding: 1px 7px; border-radius: 10px; margin-left: auto;
            background: rgba(245,230,66,0.2); color: var(--yellow); letter-spacing: 0.04em;
        }
        .sidebar-user {
            padding: 14px 18px 6px; border-top: 1px solid rgba(255,255,255,0.1);
            display: flex; align-items: center; gap: 10px;
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 10px;
            background: var(--yellow); color: var(--maroon);
            font-weight: 800; font-size: 0.8rem;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .u-name { font-size: 0.82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .u-role { font-size: 0.65rem; opacity: 0.65; }
        .btn-logout-side {
            width: calc(100% - 36px); margin: 8px 18px 18px;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.85); border-radius: 8px;
            padding: 9px 14px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; font-weight: 500;
            cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.18s;
        }
        .btn-logout-side:hover { background: rgba(220,38,38,0.5); color: white; }

        /* ── MAIN WRAP ── */
        .main-wrap { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar {
            display: none; align-items: center; gap: 12px;
            padding: 14px 18px; background: var(--maroon); color: white;
        }
        .topbar-toggle { background: none; border: none; color: white; cursor: pointer; padding: 4px; }
        .topbar-title { font-weight: 700; font-size: 1rem; }
        .page-content { padding: 28px 32px 48px; flex: 1; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 199; }

        .attention-banner {
            background: linear-gradient(90deg, rgba(180,83,9,0.12), rgba(15,118,110,0.08));
            border: 1px solid rgba(128,0,0,0.15); border-radius: 12px;
            padding: 12px 16px; margin-bottom: 18px; font-size: 0.88rem; color: var(--ink);
        }
        .attention-banner strong { color: var(--maroon); }
        .attention-banner a { color: var(--maroon); font-weight: 600; }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .topbar { display: flex; }
            .page-content { padding: 20px 16px 32px; }
            .sidebar-overlay { display: block; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-shell">

    <aside class="sidebar" id="sidebar">
        <a href="{{ $homeRoute }}" class="sidebar-brand">
            <div class="brand-logo">
                <img src="{{ asset('images/pup-logo.png') }}" alt="PUP Logo"
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
            {{-- Dashboard --}}
            <a href="{{ $homeRoute }}"
               class="s-nav-link {{ (request()->routeIs('student.dashboard') || request()->routeIs('faculty.dashboard')) ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
                <span class="role-chip">{{ strtoupper($roleLabel) }}</span>
            </a>

            {{-- Browse Catalog --}}
            <a href="{{ $borrowRoute }}"
               class="s-nav-link {{ (request()->routeIs('student.borrow') || request()->routeIs('faculty.borrow') || request()->routeIs('student.book.show') || request()->routeIs('faculty.book.show')) ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Browse Catalog
            </a>

            {{-- Penalties --}}
            @if($penaltyRoute)
            <a href="{{ $penaltyRoute }}"
               class="s-nav-link {{ (request()->routeIs('student.penalties.*') || request()->routeIs('faculty.penalties.*')) ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Penalties &amp; Fines
            </a>
            @endif

            {{-- Requisitions --}}
            @if($requisitionRoute)
            <a href="{{ $requisitionRoute }}"
               class="s-nav-link {{ (request()->routeIs('student.requisitions.*') || request()->routeIs('faculty.requisitions.*')) ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Requisitions
            </a>
            @endif

            {{-- Profile --}}
            @if($profileRoute)
            <a href="{{ $profileRoute }}"
               class="s-nav-link {{ (request()->routeIs('student.profile') || request()->routeIs('faculty.profile')) ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                My Profile
            </a>
            @endif
        </nav>

        @if($opacUser)
        <div class="sidebar-user">
            <div class="user-avatar">{{ $initials }}</div>
            <div class="user-meta">
                <div class="u-name">{{ $fullName }}</div>
                <div class="u-role">{{ $roleLabel }}</div>
            </div>
        </div>
        <form method="POST" action="{{ $logoutRoute }}">
            @csrf
            <button type="submit" class="btn-logout-side">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </button>
        </form>
        @endif
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main-wrap">
        <div class="topbar">
            <button class="topbar-toggle" onclick="openSidebar()" aria-label="Open menu">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="topbar-title">PUPSJ Libris</span>
        </div>

        <div class="page-content">
            {{-- Penalty / suspension banners --}}
            @php
                $pendingPenaltiesNav = 0;
                $suspNav = null;
                try {
                    if ($isStudent && \Illuminate\Support\Facades\Schema::hasTable('library_penalties')) {
                        $pendingPenaltiesNav = \App\Models\LibraryPenalty::where('student_id', auth('student')->id())->where('status', 'pending')->count();
                        $suspNav = auth('student')->user()->borrowing_suspended_until ?? null;
                    }
                } catch (\Exception $e) {}
            @endphp
            @if(($pendingPenaltiesNav > 0 || ($suspNav && \Carbon\Carbon::parse($suspNav)->isFuture())) && !request()->routeIs('*.penalties.*'))
                <div class="attention-banner">
                    @if($pendingPenaltiesNav > 0)
                        <strong>Action needed:</strong> You have {{ $pendingPenaltiesNav }} pending penalty fee(s). Visit <a href="{{ $penaltyRoute }}">Penalties &amp; fines</a> and see the administrator.
                    @endif
                    @if($suspNav && \Carbon\Carbon::parse($suspNav)->isFuture())
                        @if($pendingPenaltiesNav > 0)<br>@endif
                        <strong>Borrowing suspended</strong> until {{ \Carbon\Carbon::parse($suspNav)->format('M d, Y') }}.
                    @endif
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script>
function openSidebar()  { document.getElementById('sidebar').classList.add('open'); document.getElementById('sidebarOverlay').style.opacity='1'; }
function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').style.opacity=''; }
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
