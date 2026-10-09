{{--
    Shared public navbar (homepage + guest catalog).
    Optional: $fixed (bool) — fixed overlay navbar that darkens on scroll (homepage hero). Default: sticky.
--}}
@php
    $fixed   = $fixed ?? false;
    $onHome  = request()->is('/');
    $homeUrl = url('/');
    $anchor  = fn ($id) => $onHome ? "#{$id}" : "{$homeUrl}#{$id}";
    $links = [
        ['label' => 'Home',           'href' => $onHome ? '#home' : $homeUrl, 'active' => $onHome],
        ['label' => 'Services',       'href' => $anchor('services'),          'active' => false],
        ['label' => 'How It Works',   'href' => $anchor('how-it-works'),      'active' => false],
        ['label' => 'Browse Catalog', 'href' => route('guest.books'),         'active' => request()->routeIs('guest.*')],
    ];
@endphp
<style>
    .pub-nav {
        --pub-maroon: #800000;
        --pub-maroon-dark: #5a0000;
        --pub-gold: #FFC72C;
        background: linear-gradient(135deg, var(--pub-maroon) 0%, var(--pub-maroon-dark) 100%);
        border-bottom: 3px solid var(--pub-gold);
        top: 0; left: 0; right: 0;
        z-index: 1000;
        transition: background 0.3s ease, box-shadow 0.3s ease;
        font-family: 'Inter', system-ui, sans-serif;
    }
    .pub-nav.is-sticky { position: sticky; box-shadow: 0 4px 20px rgba(58,0,0,0.25); }
    .pub-nav.is-fixed { position: fixed; }
    .pub-nav.scrolled {
        background: rgba(90, 0, 0, 0.97);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        box-shadow: 0 4px 30px rgba(0,0,0,0.25);
    }
    .pub-nav-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        height: 70px;
        max-width: 1320px;
        margin: 0 auto;
        padding: 0 40px;
    }
    .pub-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; min-width: 0; }
    .pub-brand img { width: 44px; height: 44px; object-fit: contain; flex-shrink: 0; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.25)); }
    .pub-brand-text { display: flex; flex-direction: column; min-width: 0; }
    .pub-brand-main {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.15rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.15;
        white-space: nowrap;
    }
    .pub-brand-main em { font-style: normal; color: var(--pub-gold); }
    .pub-brand-sub {
        font-size: 0.62rem;
        color: rgba(255,255,255,0.62);
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .pub-links { display: flex; align-items: center; gap: 2px; }
    .pub-link {
        position: relative;
        color: rgba(255,255,255,0.82);
        font-size: 0.88rem;
        font-weight: 500;
        padding: 10px 16px;
        border-radius: 8px;
        text-decoration: none;
        transition: color 0.2s, background 0.2s;
    }
    .pub-link::after {
        content: '';
        position: absolute;
        left: 16px; right: 16px; bottom: 4px;
        height: 2px;
        border-radius: 2px;
        background: var(--pub-gold);
        transform: scaleX(0);
        transition: transform 0.25s ease;
    }
    .pub-link:hover { color: #fff; background: rgba(255,255,255,0.07); }
    .pub-link:hover::after, .pub-link.active::after { transform: scaleX(1); }
    .pub-link.active { color: #fff; font-weight: 700; }
    .pub-login {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--pub-gold);
        color: var(--pub-maroon-dark);
        font-weight: 700;
        font-size: 0.88rem;
        padding: 10px 22px;
        border-radius: 999px;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 2px 12px rgba(255,199,44,0.3);
        transition: all 0.22s ease;
    }
    .pub-login:hover { background: #fff; color: var(--pub-maroon); transform: translateY(-1px); }
    .pub-toggle {
        display: none;
        flex-direction: column;
        justify-content: center;
        gap: 5px;
        width: 42px; height: 40px;
        padding: 0 9px;
        background: transparent;
        border: 1.5px solid rgba(255,199,44,0.45);
        border-radius: 10px;
        cursor: pointer;
        flex-shrink: 0;
    }
    .pub-toggle span { display: block; height: 2px; border-radius: 2px; background: var(--pub-gold); transition: transform 0.25s, opacity 0.25s; }
    .pub-nav.open .pub-toggle span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .pub-nav.open .pub-toggle span:nth-child(2) { opacity: 0; }
    .pub-nav.open .pub-toggle span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
    .pub-drawer {
        display: none;
        flex-direction: column;
        gap: 2px;
        padding: 10px 20px 18px;
        background: var(--pub-maroon-dark);
        border-top: 1px solid rgba(255,199,44,0.18);
    }
    .pub-drawer .pub-link { padding: 12px 14px; }
    .pub-drawer .pub-link::after { display: none; }
    .pub-drawer .pub-link.active { background: rgba(255,199,44,0.12); color: var(--pub-gold); }
    .pub-drawer .pub-login { justify-content: center; margin-top: 10px; }
    .pub-nav.open .pub-drawer { display: flex; }

    @media (max-width: 991px) {
        .pub-nav-inner { padding: 0 18px; height: 64px; }
        .pub-links, .pub-actions { display: none; }
        .pub-toggle { display: flex; }
    }
    @media (max-width: 400px) {
        .pub-brand img { width: 36px; height: 36px; }
        .pub-brand-main { font-size: 1rem; }
        .pub-brand-sub { display: none; }
    }
</style>

<nav class="pub-nav {{ $fixed ? 'is-fixed' : 'is-sticky' }}" id="pubNav">
    <div class="pub-nav-inner">
        <a class="pub-brand" href="{{ $onHome ? '#home' : $homeUrl }}">
            <img src="{{ asset('images/pup-logo.png') }}" alt="PUP Logo" onerror="this.style.display='none'">
            <span class="pub-brand-text">
                <span class="pub-brand-main">PUPSJ Libris <em>Nexus</em></span>
                <span class="pub-brand-sub">PUP San Juan — Digital Library</span>
            </span>
        </a>

        <div class="pub-links">
            @foreach($links as $link)
                <a class="pub-link {{ $link['active'] ? 'active' : '' }}" href="{{ $link['href'] }}" @if($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
            @endforeach
        </div>

        <div class="pub-actions">
            <a href="{{ route('login.selection') }}" class="pub-login">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Login
            </a>
        </div>

        <button type="button" class="pub-toggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="pubDrawer" onclick="pubToggleNav()">
            <span></span><span></span><span></span>
        </button>
    </div>

    <div class="pub-drawer" id="pubDrawer">
        @foreach($links as $link)
            <a class="pub-link {{ $link['active'] ? 'active' : '' }}" href="{{ $link['href'] }}" onclick="pubCloseNav()">{{ $link['label'] }}</a>
        @endforeach
        <a href="{{ route('login.selection') }}" class="pub-login">Login</a>
    </div>
</nav>

<script>
    function pubToggleNav() {
        const nav = document.getElementById('pubNav');
        const open = nav.classList.toggle('open');
        nav.querySelector('.pub-toggle').setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    function pubCloseNav() {
        const nav = document.getElementById('pubNav');
        nav.classList.remove('open');
        nav.querySelector('.pub-toggle').setAttribute('aria-expanded', 'false');
    }
    @if($fixed)
    window.addEventListener('scroll', function () {
        document.getElementById('pubNav').classList.toggle('scrolled', window.scrollY > 50);
    }, { passive: true });
    @endif
</script>
