<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
<title>PUPSJ Libris Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/htmx.org@1.9.10"></script>
<style>
:root {
    --pup-maroon: #800000;
    --pup-maroon-dark: #5a0000;
    --pup-maroon-light: #9a1a1a;
    --pup-gold: #FFC72C;
    --pup-gold-dark: #e6b328;
    --pup-gold-light: #ffd966;
    --bg-main: #FAF9F6;
    --text: #1C1917;
    --text-muted: #6b7280;
    --info-accent: #0f766e;
    --border: #e5e7eb;
    --radius: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --sidebar-width: 280px;
    --sidebar-width-collapsed: 72px;
}

* { box-sizing: border-box; }
body { 
    margin: 0; 
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; 
    display: flex; 
    min-height: 100vh; 
    color: var(--text); 
    background: var(--bg-main);
    overflow-x: hidden;
}

/* === SIDEBAR === */
.sidebar {
    width: var(--sidebar-width);
    background: var(--pup-maroon);
    color: white;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    z-index: 1000;
    box-shadow: 2px 0 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, width 0.3s ease;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE/Edge */
}
.sidebar::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
    width: 0;
    height: 0;
}

/* Mobile menu toggle button (hidden on desktop) */
.mobile-menu-toggle {
    display: none;
    position: fixed;
    top: 1rem;
    left: 1rem;
    z-index: 1001;
    background: var(--pup-maroon);
    border: none;
    border-radius: 8px;
    padding: 0.75rem;
    cursor: pointer;
    box-shadow: var(--shadow-md);
}

.mobile-menu-toggle svg {
    width: 24px;
    height: 24px;
    stroke: white;
}

/* Sidebar Header */
.sidebar-header {
    padding: 2rem 1.5rem 1.5rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.logo-area {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: space-between;
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

.logo-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.logo-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: brightness(1.1);
}

.logo-text {
    flex: 1;
}

.logo-area h2 {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
    letter-spacing: -0.5px;
    white-space: nowrap;
    color: white;
}

.logo-area p {
    font-size: 0.7rem;
    opacity: 0.7;
    margin: 0.15rem 0 0 0;
    white-space: nowrap;
    color: white;
}

/* Sidebar Navigation */
.sidebar-nav {
    flex: 1;
    padding: 1.5rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9375rem;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.nav-link:hover {
    background: var(--pup-maroon-dark);
    color: white;
}

.nav-link.active {
    background: var(--pup-gold);
    color: var(--pup-maroon);
}

.nav-link.active svg {
    color: var(--pup-maroon);
}

/* Collapsible nav groups */
.nav-group { margin-bottom: 2px; }

.nav-group-toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 1rem;
    background: none;
    border: none;
    color: rgba(255,255,255,0.5);
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    border-radius: 6px;
    transition: color 0.15s;
}
.nav-group-toggle:hover { color: rgba(255,255,255,0.8); }
.nav-group-toggle .chevron {
    transition: transform 0.25s;
    flex-shrink: 0;
    stroke: currentColor;
}
.nav-group.open .chevron { transform: rotate(0deg); }
.nav-group:not(.open) .chevron { transform: rotate(-90deg); }

.nav-group-items {
    overflow: hidden;
    max-height: 500px;
    transition: max-height 0.3s ease;
}
.nav-group:not(.open) .nav-group-items { max-height: 0; }


/* Sidebar Footer */
.sidebar-footer {
    padding: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.avatar {
    width: 48px;
    height: 48px;
    background: var(--pup-gold);
    color: var(--pup-maroon);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.user-details {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-size: 0.9375rem;
    font-weight: 600;
    margin: 0 0 0.25rem 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-role {
    font-size: 0.75rem;
    opacity: 0.7;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.logout-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    color: white;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    white-space: nowrap;
}

.logout-btn:hover {
    background: rgba(239, 68, 68, 0.8);
    border-color: transparent;
}

/* Overlay for mobile sidebar */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

/* === DESKTOP SIDEBAR COLLAPSE === */
.sidebar.collapsed {
    width: var(--sidebar-width-collapsed);
    overflow: hidden;
}
/* Brand area */
.sidebar.collapsed .logo-text,
.sidebar.collapsed .ln-badge { display: none !important; }
.sidebar.collapsed .logo-area { justify-content: center; }
.sidebar.collapsed .logo-icon { margin: 0 auto; }
.sidebar.collapsed .sidebar-header { padding: 1rem 0; justify-content: center; }
/* Hide group headers when collapsed */
.sidebar.collapsed .nav-group-toggle,
.sidebar.collapsed .sidebar-footer-label { display: none !important; }
/* Nav links — icon only (same font-size:0 trick as student sidebar) */
.sidebar.collapsed .nav-link {
    font-size: 0;
    justify-content: center;
    padding: 0.7rem;
    gap: 0;
}
.sidebar.collapsed .nav-link svg { flex-shrink: 0; width: 20px; height: 20px; }
/* Expand all group items so icons are reachable */
.sidebar.collapsed .nav-group-items { display: block !important; }
/* User / logout */
.sidebar.collapsed .user-section,
.sidebar.collapsed .user-details,
.sidebar.collapsed .logout-btn span,
.sidebar.collapsed .collapse-label { display: none !important; }
/* Hide expand/collapse all buttons when sidebar is collapsed */
.sidebar.collapsed .sidebar-expand-collapse-btns { display: none !important; }
/* Flip collapse button arrow */
.sidebar.collapsed .sidebar-collapse-btn svg { transform: rotate(180deg); margin: 0 auto; }
/* Main content offset */
.sidebar.collapsed + .main,
.sidebar.collapsed ~ .main {
    margin-left: var(--sidebar-width-collapsed);
    width: calc(100% - var(--sidebar-width-collapsed));
}
/* Collapse button strip */
.sidebar-collapse-btn {
    display: flex; align-items: center; gap: 6px;
    width: 100%; background: rgba(255,255,255,0.06);
    border: none; border-bottom: 1px solid rgba(255,255,255,0.12);
    color: rgba(255,255,255,0.55); padding: 6px 1.5rem;
    font-size: 0.7rem; font-family: inherit;
    cursor: pointer; transition: background 0.15s, color 0.15s;
}
.sidebar.collapsed .sidebar-collapse-btn { padding: 8px; justify-content: center; }
.sidebar-collapse-btn:hover { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.85); }
.sidebar-collapse-btn svg { transition: transform 0.3s ease; flex-shrink: 0; }

/* === MAIN CONTENT === */
.main {
    margin-left: var(--sidebar-width);
    width: calc(100% - var(--sidebar-width));
    background: var(--bg-main);
    padding: 2rem;
    min-height: 100vh;
    transition: margin-left 0.3s ease, width 0.3s ease;
}

.main h2 {
    margin: 0 0 8px 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
    letter-spacing: -0.025em;
    word-break: break-word;
}

.main .page-subtitle {
    color: var(--text-muted);
    font-size: 0.9375rem;
    margin-bottom: 1.75rem;
}

/* Cards */
.card {
    background: white;
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

/* Search Card */
.search-card {
    background: linear-gradient(135deg, var(--pup-maroon) 0%, var(--pup-maroon-dark) 100%);
    color: white;
    border: none;
}

.search-form {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.search-form input {
    flex: 1;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    border: none;
    font-size: 0.9375rem;
    min-width: 200px;
}

.search-form input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 199, 44, 0.3);
}

.btn-primary {
    background: var(--pup-gold);
    color: var(--pup-maroon);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
    min-height: 44px;
}

.btn-primary:hover {
    background: var(--pup-gold-dark);
    transform: translateY(-1px);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: var(--radius);
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid var(--border);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--pup-maroon);
    margin: 0.5rem 0;
    word-break: break-word;
}

/* Actions Grid */
.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.action-card {
    background: #f9fafb;
    padding: 1.25rem;
    border-radius: 10px;
    text-align: center;
    font-weight: 600;
    text-decoration: none;
    color: var(--text);
    border: 1px solid var(--border);
    transition: all 0.2s;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-card:hover {
    background: var(--pup-gold);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

/* Quick actions horizontal row - ensure flex-fill children stay horizontal */
.d-flex.flex-wrap .action-card {
    flex-direction: column;
    gap: 0.5rem;
}

.d-flex.flex-wrap .action-card .action-icon {
    width: 44px;
    height: 44px;
    background: rgba(128,0,0,0.06);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--pup-maroon);
    flex-shrink: 0;
}

/* Tables */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    min-width: 600px;
}

th {
    background: var(--pup-maroon);
    color: white;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
}

td {
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
    font-size: 0.875rem;
}

tr:last-child td {
    border-bottom: none;
}

/* Badges */
.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.warning-badge {
    background: #fef3c7;
    color: #92400e;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: var(--text-muted);
}

/* Two Column Layout */
.two-column-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

/* AI Card */
.ai-card {
    background: linear-gradient(135deg, var(--pup-maroon) 0%, var(--pup-maroon-dark) 100%);
    color: white;
    border: none;
}

.ai-card-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.ai-number {
    font-size: 2rem;
    font-weight: 700;
    margin: 0.25rem 0;
}

/* Ranked List */
.ranked-list {
    padding: 0.5rem 0;
}

.ranked-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
}

.rank-number {
    width: 32px;
    height: 32px;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
}

.rank-number.top-rank {
    background: var(--pup-gold);
    color: var(--pup-maroon);
}

.rank-info {
    flex: 1;
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Subject List */
.subject-list {
    padding: 0.5rem 0;
}

.subject-item {
    margin-bottom: 1rem;
}

.subject-name {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.subject-bar-wrapper {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.subject-bar {
    height: 8px;
    background: linear-gradient(90deg, var(--pup-maroon), var(--pup-maroon-light));
    border-radius: 4px;
    transition: width 0.3s;
}

.subject-count {
    font-size: 0.75rem;
    color: var(--text-muted);
    min-width: 40px;
}

/* Program List */
.program-list {
    padding: 0.5rem 0;
}

.program-item {
    margin-bottom: 1rem;
}

.program-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.program-progress {
    background: #e5e7eb;
    border-radius: 8px;
    height: 8px;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(90deg, var(--pup-gold), var(--pup-gold-dark));
    height: 100%;
    border-radius: 8px;
    transition: width 0.3s;
}

/* Chart Container */
.chart-container {
    padding: 1rem 0;
}

.action-btn.print {
    background: #D1FAE5;
    color: #065F46;
    border: none;
    padding: 0.375rem 0.875rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.action-btn.print:hover {
    background: #10B981;
    color: white;
}

/* ============================================ */
/* ENHANCED MOBILE RESPONSIVENESS */
/* ============================================ */

/* Tablet and smaller desktop */
@media (max-width: 1024px) {
    .main {
        padding: 1.5rem;
    }
    
    .two-column-grid {
        grid-template-columns: 1fr;
    }
}

/* Mobile styles */
@media (max-width: 768px) {
    /* Sidebar becomes off-canvas */
    .sidebar {
        transform: translateX(-100%);
        width: var(--sidebar-width);
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 1000;
        box-shadow: none;
    }
    
    .sidebar.open {
        transform: translateX(0);
        box-shadow: 2px 0 8px rgba(0,0,0,0.2);
    }
    
    /* Mobile menu toggle button */
    .mobile-menu-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Overlay */
    .sidebar-overlay {
        display: block;
    }
    
    .sidebar-overlay.active {
        opacity: 1;
    }
    
    /* Main content - full width with padding for toggle button */
    .main {
        margin-left: 0;
        width: 100%;
        padding: 1rem;
        padding-top: 4rem;
    }
    
    /* Adjust main content when sidebar is open */
    body.sidebar-open .main {
        overflow: hidden;
    }
    
    /* Sidebar header adjustments */
    .sidebar-header {
        padding: 1.5rem 1.5rem 1rem 1.5rem;
    }
    
    /* Make navigation items touch-friendly */
    .nav-link {
        padding: 0.875rem 1rem;
        font-size: 1rem;
    }
    
    /* Show all text in sidebar when open */
    .sidebar.open .nav-link span,
    .sidebar.open .user-details,
    .sidebar.open .logout-btn span,
    .sidebar.open .logo-area p {
        display: inline-block;
    }
    
    /* Stats grid */
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    /* Actions grid */
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    /* Search form */
    .search-form {
        flex-direction: column;
    }
    
    .search-form input {
        width: 100%;
    }
    
    .search-form .btn-primary {
        width: 100%;
        justify-content: center;
    }
    
    /* Card headers */
    .card-header {
        flex-direction: column;
        gap: 0.75rem;
        text-align: center;
    }
    
    /* Page headers */
    .main h2 {
        font-size: 1.5rem;
        word-break: break-word;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
    }
    
    /* Buttons */
    .btn-primary, .btn-secondary, .btn-outline {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        min-height: 44px;
    }
    
    /* Action buttons group */
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons a {
        width: 100%;
        justify-content: center;
    }
    
    /* Table adjustments */
    .table-responsive {
        margin: 0 -0.5rem;
        padding: 0 0.5rem;
    }
    
    /* Filter bar */
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .filter-actions {
        justify-content: stretch;
    }
    
    .btn-reset {
        width: 100%;
    }
    
    /* Pagination */
    .pagination-container {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }
    
    .pagination-controls {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .pagination-btn,
    .page-number {
        min-width: 44px;
        min-height: 44px;
        padding: 0.5rem 0.75rem;
    }
    
    /* Stats card */
    .stats-card {
        flex-direction: column;
        text-align: center;
    }
    
    .stats-icon {
        margin: 0 auto;
    }
    
    /* Two column grid */
    .two-column-grid {
        grid-template-columns: 1fr;
    }
    
    /* AI Card content */
    .ai-card-content {
        flex-direction: column;
        text-align: center;
    }
    
    /* Ranked items */
    .ranked-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .rank-info {
        width: 100%;
    }
    
    /* Subject bars */
    .subject-bar-wrapper {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .subject-bar {
        width: 100% !important;
    }
}
/* Dashboard Filter */
.period-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}
.period-btn {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    text-decoration: none;
    background: white;
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
}
.period-btn.active {
    background: var(--pup-maroon);
    color: white;
    border-color: var(--pup-maroon);
}
.period-btn:hover:not(.active) {
    background: var(--bg-main);
}

/* Extra small devices (phones under 480px) */
@media (max-width: 480px) {
    .main {
        padding: 0.75rem;
        padding-top: 3.5rem;
    }
    
    .card {
        padding: 1rem;
    }
    
    .card-header {
        padding: 1rem;
    }
    
    table th,
    table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .action-card {
        padding: 1rem;
    }
    
    .pagination-btn,
    .page-number {
        min-width: 40px;
        min-height: 40px;
        padding: 0.375rem 0.625rem;
        font-size: 0.75rem;
    }
    
    .pagination-info {
        font-size: 0.75rem;
    }
}

/* Landscape mode on mobile */
@media (max-width: 768px) and (orientation: landscape) {
    .sidebar {
        overflow-y: auto;
    }

    .sidebar-nav {
        padding: 1rem;
    }

    .nav-link {
        padding: 0.5rem 1rem;
    }
}

/* Touch responsiveness — removes 300ms tap delay */
.nav-link,
.btn-primary, .btn-secondary, .btn-outline,
.action-card, .action-btn,
.logout-btn, .period-btn,
.mobile-menu-toggle,
.nav-group-toggle,
.pagination-btn, .page-number {
    touch-action: manipulation;
}

/* Global Pagination & SVG Sizing Fix */
nav[role="navigation"] svg,
.pagination svg,
.pagination-container svg,
.audit-pagination-wrap svg {
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
    color: var(--pup-maroon);
    border-color: #cbd5e1;
}

.pagination .page-item.active .page-link {
    background: var(--pup-maroon) !important;
    border-color: var(--pup-maroon) !important;
    color: #ffffff !important;
    font-weight: 700;
}

.pagination .page-item.disabled .page-link {
    color: #94a3b8;
    background: #f8fafc;
    border-color: #e2e8f0;
}


/* HTMX Top Progress Bar for SPA navigation */
.htmx-indicator-bar {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--pup-gold) 0%, var(--pup-maroon) 100%);
    z-index: 9999;
    width: 0%;
    transition: width 0.2s ease, opacity 0.2s ease;
    opacity: 0;
    pointer-events: none;
}
.htmx-request.htmx-indicator-bar,
.htmx-request .htmx-indicator-bar {
    opacity: 1;
    width: 75%;
}
</style>
@stack('styles')
</head>
<body>

<!-- Top Loading Progress Bar for SPA Navigation -->
<div class="htmx-indicator-bar" id="htmxProgressBar"></div>

<!-- Mobile Menu Toggle Button -->
<button type="button" class="mobile-menu-toggle" id="mobileMenuToggle">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-area">
            <div class="logo-icon">
                <img src="{{ asset('images/pup-logo.png') }}" 
                     alt="PUP Logo" 
                     onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2280%22>📚</text></svg>'">
            </div>
            <div class="logo-text">
                <h2>PUPSJ Libris</h2>
                <p>Library Management System</p>
            </div>
        </div>
    </div>

    <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Toggle sidebar">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M15 18l-6-6 6-6"/>
        </svg>
        <span class="collapse-label">Collapse sidebar</span>
    </button>

    <nav class="sidebar-nav" id="sidebarNav" hx-boost="true" hx-target=".main" hx-select=".main" hx-swap="outerHTML" hx-indicator="#htmxProgressBar">

        {{-- Expand / Collapse All --}}
        <div class="sidebar-expand-collapse-btns" style="display:flex;gap:6px;padding:4px 12px 8px;border-bottom:1px solid rgba(255,255,255,0.08);margin-bottom:4px;">
            <button type="button" onclick="expandAll()" style="flex:1;padding:4px 0;font-size:0.7rem;font-weight:600;background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.75);border:1px solid rgba(255,255,255,0.15);border-radius:5px;cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'">Expand All</button>
            <button type="button" onclick="collapseAll()" style="flex:1;padding:4px 0;font-size:0.7rem;font-weight:600;background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.75);border:1px solid rgba(255,255,255,0.15);border-radius:5px;cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'">Collapse All</button>
        </div>

        {{-- CORE --}}
        <div class="nav-group open" data-group="core">
            <button type="button" class="nav-group-toggle" onclick="toggleGroup(this)">
                <span>Core</span>
                <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="nav-group-items">
                <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>Dashboard</span>
                </a>
            </div>
        </div>

        {{-- COLLECTION --}}
        <div class="nav-group open" data-group="collection">
            <button type="button" class="nav-group-toggle" onclick="toggleGroup(this)">
                <span>Collection</span>
                <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="nav-group-items">
                <a href="{{ route('admin.books') }}" class="nav-link {{ (request()->is('admin/books*') || request()->is('borrow-history*') || request()->routeIs('admin.borrow-history') || request()->routeIs('admin.books*')) ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <span>Books Management</span>
                </a>
                <a href="/admin/archive-scanner" class="nav-link {{ request()->is('admin/archive-scanner*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                    <span>Batch Scan &amp; Archive</span>
                </a>
                <a href="{{ route('admin.collection-types.index') }}" class="nav-link {{ (request()->routeIs('admin.collection-types.*') || request()->is('admin/collection-types*')) ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    <span>Collection Types</span>
                </a>
                <a href="/admin/book-suggestions" class="nav-link {{ request()->is('admin/book-suggestions*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    <span>Book Recommendations</span>
                </a>
            </div>
        </div>

        {{-- PROGRAMS & DEPARTMENTS --}}
        <div class="nav-group open" data-group="programs">
            <button type="button" class="nav-group-toggle" onclick="toggleGroup(this)">
                <span>Programs &amp; Departments</span>
                <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="nav-group-items">
                <a href="{{ route('admin.programs.index') }}" class="nav-link {{ (request()->is('admin/programs*') || request()->is('admin/courses*') || request()->routeIs('admin.programs.*') || request()->routeIs('admin.courses.*')) ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    <span>Programs & Departments</span>
                </a>
            </div>
        </div>

        {{-- USERS --}}
        <div class="nav-group open" data-group="users">
            <button type="button" class="nav-group-toggle" onclick="toggleGroup(this)">
                <span>Users</span>
                <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="nav-group-items">
                <a href="{{ route('admin.account.requests') }}" class="nav-link {{ (request()->is('admin/account-requests*') || request()->is('admin/faculty-requests*') || request()->routeIs('admin.account.*') || request()->routeIs('admin.faculty.*')) ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg>
                    <span>User Approvals</span>
                    @php
                        $pendingAll = \App\Models\Student::where('status','pending')->count()
                            + \App\Models\Faculty::where('status','pending')->count();
                    @endphp
                    @if($pendingAll)
                        <span class="badge" style="background:var(--pup-gold); color:var(--pup-maroon); margin-left:auto; font-size:0.75rem; padding:2px 7px; border-radius:10px; font-weight:700;">{{ $pendingAll }}</span>
                    @endif
                </a>
                <a href="/users" class="nav-link {{ (request()->is('users*') || request()->routeIs('admin.users*')) ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Users</span>
                </a>
                <a href="{{ route('admin.operations.index') }}" class="nav-link {{ (request()->routeIs('admin.operations.*') || request()->is('admin/operations*')) ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                    <span>Transactions</span>
                    @php
                        $pendingRequisitionCount = \App\Models\BookRequisition::where('status', 'submitted')->count();
                    @endphp
                    @if($pendingRequisitionCount)
                        <span class="badge" style="background:var(--pup-gold); color:var(--pup-maroon); margin-left:auto; font-size:0.75rem; padding:2px 7px; border-radius:10px; font-weight:700;">{{ $pendingRequisitionCount }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- LOGS & AUDIT --}}
        <div class="nav-group open" data-group="logs">
            <button type="button" class="nav-group-toggle" onclick="toggleGroup(this)">
                <span>Logs &amp; History</span>
                <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="nav-group-items">
                <a href="{{ route('admin.analytics') }}" class="nav-link {{ (request()->routeIs('admin.analytics*') || request()->is('admin/analytics*') || request()->is('analytics*')) ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                    <span>Analytics</span>
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="nav-link {{ (request()->routeIs('admin.audit-logs.*') || request()->is('admin/audit-logs*')) ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>Audit Logs</span>
                </a>
            </div>
        </div>

        {{-- TOOLS --}}
        <div class="nav-group open" data-group="tools">
            <button type="button" class="nav-group-toggle" onclick="toggleGroup(this)">
                <span>Tools</span>
                <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="nav-group-items">
<a href="{{ route('kiosk.index') }}" target="_blank" class="nav-link {{ request()->is('kiosk*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    <span>Library Kiosk</span>
                </a>

                <a href="{{ route('admin.impersonate.test') }}" class="nav-link" hx-boost="false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>View as Student</span>
                </a>
                <a href="{{ route('admin.impersonate.faculty') }}" class="nav-link" hx-boost="false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>View as Faculty</span>
                </a>
                <a href="{{ route('admin.user-manual') }}" class="nav-link {{ request()->is('admin/user-manual*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    <span>User Manual</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>Settings</span>
                </a>
            </div>
        </div>

    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">AD</div>
            <div class="user-details">
                <p class="user-name">Admin User</p>
                <p class="user-role">Librarian</p>
            </div>
        </div>
        <a href="#" onclick="document.getElementById('logout-form').submit(); return false;" class="logout-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Logout</span>
        </a>
        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>

<div class="main">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom:18px;">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif(session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom:18px;">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert" style="margin-bottom:18px;">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom:18px;">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<script>
// Mobile sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('mobileMenuToggle');
    const overlay = document.getElementById('sidebarOverlay');
    
    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
    }
    
    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        document.body.classList.add('sidebar-open');
    }
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
        overlay.addEventListener('touchstart', closeSidebar);
    }
    
    // Close sidebar on window resize if screen becomes larger than mobile breakpoint
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
    
    // Close sidebar when a nav link is clicked (for better UX on mobile)
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });

    // ── Sidebar collapsible groups ──
    // Restore open/closed state from sessionStorage
    document.querySelectorAll('.nav-group[data-group]').forEach(function(group) {
        const key = 'sidebar-group-' + group.dataset.group;
        const saved = sessionStorage.getItem(key);
        if (saved === 'closed') group.classList.remove('open');
        else group.classList.add('open');
    });
});

function toggleGroup(btn) {
    const group = btn.closest('.nav-group');
    const isOpen = group.classList.toggle('open');
    sessionStorage.setItem('sidebar-group-' + group.dataset.group, isOpen ? 'open' : 'closed');
}

function collapseAll() {
    document.querySelectorAll('.nav-group[data-group]').forEach(function(group) {
        group.classList.remove('open');
        sessionStorage.setItem('sidebar-group-' + group.dataset.group, 'closed');
    });
}

function expandAll() {
    document.querySelectorAll('.nav-group[data-group]').forEach(function(group) {
        group.classList.add('open');
        sessionStorage.setItem('sidebar-group-' + group.dataset.group, 'open');
    });
}

// ── Desktop sidebar collapse ──
(function() {
    var sidebar = document.getElementById('sidebar');
    var btn     = document.getElementById('sidebarCollapseBtn');
    var main    = document.querySelector('.main');
    var KEY     = 'sidebar-collapsed';

    function applyCollapse(collapsed) {
        sidebar.classList.toggle('collapsed', collapsed);
        if (main) {
            main.style.marginLeft = collapsed ? 'var(--sidebar-width-collapsed)' : '';
            main.style.width = collapsed ? 'calc(100% - var(--sidebar-width-collapsed))' : '';
        }
        // When collapsing, expand all nav groups so all icons are reachable
        if (collapsed) {
            document.querySelectorAll('.nav-group').forEach(function(g) { g.classList.add('open'); });
        }
        // Update tooltip on nav links for collapsed mode
        document.querySelectorAll('.nav-link').forEach(function(link) {
            var spanEl = link.querySelector('span');
            if (collapsed && spanEl) {
                link.setAttribute('title', spanEl.textContent.trim());
            } else {
                link.removeAttribute('title');
            }
        });
    }

    if (btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var collapsed = !sidebar.classList.contains('collapsed');
            localStorage.setItem(KEY, collapsed ? '1' : '0');
            applyCollapse(collapsed);
        });
    }
    if (window.innerWidth > 768 && localStorage.getItem(KEY) === '1') {
        applyCollapse(true);
    }
})();

// ── HTMX SPA Sidebar active link updating & SPA script re-init ──
function updateActiveSidebar() {
    const currentPath = window.location.pathname;
    document.querySelectorAll('#sidebarNav .nav-link').forEach(link => {
        const rawHref = link.getAttribute('href');
        if (!rawHref || rawHref.startsWith('javascript:')) return;
        let linkPath = rawHref;
        try {
            linkPath = new URL(rawHref, window.location.origin).pathname;
        } catch(e) {}

        let isActive = false;
        if (linkPath === '/dashboard' || linkPath === '/' || linkPath === '') {
            isActive = (currentPath === '/dashboard' || currentPath === '/');
        } else if (linkPath === '/admin/books') {
            isActive = currentPath.startsWith('/admin/books') || currentPath.startsWith('/borrow-history') || currentPath.startsWith('/books');
        } else if (linkPath === '/admin/account-requests') {
            isActive = currentPath.startsWith('/admin/account-requests') || currentPath.startsWith('/admin/faculty-requests');
        } else if (linkPath === '/admin/programs') {
            isActive = currentPath.startsWith('/admin/programs') || currentPath.startsWith('/admin/courses');
        } else if (linkPath === '/users') {
            isActive = currentPath.startsWith('/users');
        } else if (linkPath === '/reports') {
            isActive = currentPath.startsWith('/reports');
        } else {
            isActive = currentPath.startsWith(linkPath);
        }

        link.classList.toggle('active', isActive);
        if (isActive) {
            const parentGroup = link.closest('.nav-group');
            if (parentGroup) parentGroup.classList.add('open');
        }
    });
}

document.addEventListener('DOMContentLoaded', updateActiveSidebar);

document.addEventListener('htmx:afterSwap', function(evt) {
    updateActiveSidebar();
    // Scroll content window back to top on page swap
    window.scrollTo({ top: 0, behavior: 'instant' });

    // Safely re-initialize SPA page components if defined
    if (typeof window.initBooksPage === 'function') {
        try { window.initBooksPage(); } catch(e) { console.error('Error in initBooksPage:', e); }
    }
    if (typeof window.initUsersPage === 'function') {
        try { window.initUsersPage(); } catch(e) { console.error('Error in initUsersPage:', e); }
    }
    if (typeof window.initReportsPage === 'function') {
        try { window.initReportsPage(); } catch(e) { console.error('Error in initReportsPage:', e); }
    }
});
</script>
    <!-- Bootstrap 5 JS (required for dropdown) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>


