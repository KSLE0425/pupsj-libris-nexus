{{-- Shared mobile chrome for the student & faculty portals (header, admin-view bar, bottom tab bar) --}}
<style>
    /* Keep wide children (scrollers, tables) from stretching the page past the viewport */
    .main-wrap { min-width: 0; }
    .page-content { min-width: 0; overflow-x: hidden; }
    img, svg, video { max-width: 100%; }

    /* ── Sticky portal header: admin-view bar + mobile topbar ── */
    .portal-header {
        position: sticky;
        top: 0;
        z-index: 150;
    }
    .imp-bar {
        background: linear-gradient(90deg, #b45309, #800000);
        color: #fff;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        font-family: 'Poppins', sans-serif;
        font-size: 0.88rem;
    }
    .imp-left { display: flex; align-items: center; gap: 8px; min-width: 0; }
    .imp-badge {
        background: #FFC72C;
        color: #800000;
        font-weight: 700;
        font-size: 0.72rem;
        padding: 2px 8px;
        border-radius: 4px;
        text-transform: uppercase;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .imp-text { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .imp-text-short { display: none; }
    .imp-exit {
        background: #FFC72C;
        color: #800000;
        border: none;
        padding: 6px 14px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        white-space: nowrap;
    }
    .imp-exit-short { display: none; }

    /* Mobile topbar */
    .topbar {
        display: none;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
        padding: 10px 14px;
        border-bottom: 3px solid #FFC72C;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }
    .topbar-toggle {
        background: rgba(255,255,255,0.1);
        border: none;
        border-radius: 10px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        flex-shrink: 0;
        cursor: pointer;
    }
    .topbar-toggle:active { background: rgba(255,255,255,0.2); }
    .topbar-logo { width: 30px; height: 30px; object-fit: contain; flex-shrink: 0; }
    .topbar-titles { min-width: 0; display: flex; flex-direction: column; line-height: 1.15; }
    .topbar-brand { font-size: 0.62rem; color: #FFC72C; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase; }
    .topbar-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #fff;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ── Bottom tab bar (mobile only) ── */
    .bottom-nav {
        display: none;
        position: fixed;
        left: 0; right: 0; bottom: 0;
        z-index: 140;
        background: #fff;
        border-top: 1px solid rgba(128,0,0,0.12);
        box-shadow: 0 -4px 18px rgba(0,0,0,0.08);
        padding: 6px 6px calc(6px + env(safe-area-inset-bottom, 0px));
        justify-content: space-around;
    }
    .bn-item {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        padding: 6px 2px;
        border-radius: 12px;
        color: #8a7f7f;
        text-decoration: none;
        font-size: 0.64rem;
        font-weight: 600;
        transition: background 0.15s, color 0.15s;
    }
    .bn-item svg { width: 20px; height: 20px; }
    .bn-item span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
    .bn-item.active { background: var(--maroon); color: #FFC72C; }
    .bn-item:not(.active):active { background: rgba(128,0,0,0.06); }

    @media (max-width: 991px) {
        .topbar { display: flex; }
        .bottom-nav { display: flex; }
        .page-content { padding: 16px 14px calc(96px + env(safe-area-inset-bottom, 0px)) !important; }
        .page-header h1, .page-content h1 { font-size: 1.35rem; }
        .page-header p { font-size: 0.8rem; }

        .imp-bar { padding: 8px 12px; font-size: 0.78rem; }
        .imp-text-full, .imp-exit-full { display: none; }
        .imp-text-short, .imp-exit-short { display: inline; }
        .imp-exit { padding: 5px 10px; font-size: 0.74rem; }

        /* Generic safety nets for child pages */
        .card, .table-responsive { max-width: 100%; }
        table { max-width: 100%; }
    }
    @media (max-width: 360px) {
        .bn-item { font-size: 0.58rem; }
        .bn-item svg { width: 18px; height: 18px; }
        .topbar-logo { display: none; }
    }
</style>
