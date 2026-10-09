<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUPSJ Libris Nexus — PUP San Juan Digital Library</title>
    <meta name="description" content="Discover a world of knowledge at PUPSJ Libris Nexus. Browse thousands of academic resources, borrow books, and manage your library experience digitally.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #5a0000;
            --maroon-deeper: #3a0000;
            --maroon-light: #a00000;
            --gold: #FFC72C;
            --gold-dark: #e6b328;
            --gold-light: #ffe18a;
            --gold-pale: #fef7e0;
            --bg-cream: #fdfbf7;
            --bg-soft: #f8f6f2;
            --text-dark: #1a1a2e;
            --text-mid: #4a4a5a;
            --text-light: #6b7280;
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
            --shadow-md: 0 8px 30px rgba(0,0,0,0.08);
            --shadow-lg: 0 16px 50px rgba(0,0,0,0.12);
            --shadow-gold: 0 8px 30px rgba(255,199,44,0.25);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-cream);
            color: var(--text-dark);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ═══════════════════════════════════════
           ANIMATIONS
        ═══════════════════════════════════════ */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-40px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255,199,44,0.4); }
            50% { box-shadow: 0 0 0 12px rgba(255,199,44,0); }
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ═══════════════════════════════════════
           HERO SECTION
        ═══════════════════════════════════════ */
        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: linear-gradient(150deg, #1a0000 0%, #3a0000 30%, var(--maroon) 60%, var(--maroon-dark) 100%);
            background-size: 200% 200%;
            animation: gradientShift 12s ease infinite;
            padding-top: 72px;
        }

        .hero-bg-elements {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .hero-bg-elements .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.12;
        }

        .hero-bg-elements .orb-1 {
            width: 600px;
            height: 600px;
            background: var(--gold);
            top: -200px;
            right: -100px;
        }

        .hero-bg-elements .orb-2 {
            width: 400px;
            height: 400px;
            background: #fff;
            bottom: -100px;
            left: -80px;
        }

        .hero-bg-elements .orb-3 {
            width: 300px;
            height: 300px;
            background: var(--gold);
            bottom: 10%;
            right: 20%;
            opacity: 0.06;
        }

        .hero-grid-pattern {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,199,44,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,199,44,0.03) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        .hero-container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 40px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-content {
            animation: slideInLeft 0.8s ease forwards;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,199,44,0.12);
            border: 1px solid rgba(255,199,44,0.25);
            color: var(--gold);
            font-size: 0.78rem;
            font-weight: 600;
            padding: 8px 18px;
            border-radius: 50px;
            margin-bottom: 28px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .hero-badge svg { opacity: 0.8; }

        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.6rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.12;
            margin-bottom: 22px;
        }

        .hero-content h1 .text-gold {
            color: var(--gold);
            position: relative;
        }

        .hero-content p {
            font-size: 1.08rem;
            color: rgba(255,255,255,0.65);
            line-height: 1.8;
            margin-bottom: 36px;
            max-width: 520px;
            font-weight: 400;
        }

        .hero-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
        }

        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--gold);
            color: var(--maroon-dark);
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            padding: 16px 36px;
            border-radius: 50px;
            border: none;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-gold);
        }

        .btn-hero-primary:hover {
            background: #fff;
            color: var(--maroon);
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(255,255,255,0.25);
        }

        .btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            color: rgba(255,255,255,0.85);
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            padding: 16px 32px;
            border-radius: 50px;
            border: 2px solid rgba(255,255,255,0.15);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-hero-secondary:hover {
            border-color: var(--gold);
            color: var(--gold);
            background: rgba(255,199,44,0.06);
        }

        /* Hero visual side */
        .hero-visual {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: fadeIn 1s ease 0.3s forwards;
            opacity: 0;
        }

        .hero-illustration {
            position: relative;
            width: 100%;
            max-width: 480px;
        }

        .hero-card {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-xl);
            padding: 40px;
            text-align: center;
            animation: float 5s ease-in-out infinite;
        }

        .hero-card-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 12px 40px rgba(255,199,44,0.3);
        }

        .hero-card h3 {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 1.3rem;
            margin-bottom: 8px;
        }

        .hero-card p {
            color: rgba(255,255,255,0.5);
            font-size: 0.85rem;
            margin-bottom: 24px;
        }

        .hero-search-preview {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.4);
            font-size: 0.88rem;
        }

        .hero-floating-tag {
            position: absolute;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            padding: 10px 16px;
            color: #fff;
            font-size: 0.78rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .hero-floating-tag.tag-1 {
            top: -10px;
            right: -30px;
            animation: float 4s ease-in-out 0.5s infinite;
        }

        .hero-floating-tag.tag-2 {
            bottom: 40px;
            left: -40px;
            animation: float 4.5s ease-in-out 1s infinite;
        }

        .hero-floating-tag .tag-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4ade80;
        }

        .hero-floating-tag .tag-dot.gold { background: var(--gold); }

        /* ═══════════════════════════════════════
           SEARCH SECTION
        ═══════════════════════════════════════ */
        .search-section {
            background: var(--maroon);
            padding: 0;
            position: relative;
            z-index: 10;
            margin-top: -1px;
        }

        .search-inner {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 40px;
            transform: translateY(-36px);
        }

        .search-card {
            background: #fff;
            border-radius: var(--radius-lg);
            padding: 8px;
            box-shadow: var(--shadow-lg);
            display: flex;
            gap: 0;
        }

        .search-card input {
            flex: 1;
            border: none;
            padding: 18px 28px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            border-radius: var(--radius-md);
            background: transparent;
            color: var(--text-dark);
        }

        .search-card input::placeholder { color: #9ca3af; }

        .search-card button {
            background: var(--maroon);
            color: #fff;
            border: none;
            padding: 0 36px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            border-radius: var(--radius-md);
            transition: all 0.25s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-card button:hover {
            background: var(--maroon-dark);
            transform: scale(1.02);
        }

        /* ═══════════════════════════════════════
           STATS BAR
        ═══════════════════════════════════════ */
        .stats-section {
            padding: 48px 0 72px;
            background: var(--bg-cream);
        }

        .stats-grid {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 40px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: var(--radius-md);
            padding: 28px 24px;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--maroon), var(--gold));
            transform: scaleX(0);
            transition: transform 0.35s ease;
        }

        .stat-card:hover::before { transform: scaleX(1); }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }

        .stat-icon-wrap {
            width: 52px;
            height: 52px;
            background: var(--gold-pale);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: var(--maroon);
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon-wrap {
            background: var(--maroon);
            color: var(--gold);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--maroon);
            line-height: 1.1;
            margin-bottom: 4px;
            font-family: 'Playfair Display', serif;
        }

        .stat-label {
            font-size: 0.82rem;
            color: var(--text-light);
            font-weight: 500;
        }

        /* ═══════════════════════════════════════
           FEATURES / SERVICES SECTION
        ═══════════════════════════════════════ */
        .section-features {
            padding: 80px 0 100px;
            background: var(--bg-soft);
            position: relative;
        }

        .section-features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(128,0,0,0.08), transparent);
        }

        .features-container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 56px;
        }

        .section-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--gold-pale);
            color: var(--maroon);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 8px 20px;
            border-radius: 50px;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.4rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .section-title .text-maroon { color: var(--maroon); }

        .section-subtitle {
            color: var(--text-light);
            font-size: 1rem;
            max-width: 560px;
            margin: 0 auto;
            line-height: 1.7;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        .feature-card {
            background: #fff;
            border-radius: var(--radius-lg);
            padding: 36px 30px;
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.35s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--maroon), var(--gold));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.35s ease;
        }

        .feature-card:hover::after { transform: scaleX(1); }

        .feature-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-6px);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--gold-pale), rgba(255,199,44,0.15));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            color: var(--maroon);
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: var(--gold);
            transform: scale(1.05);
        }

        .feature-card h5 {
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .feature-card p {
            font-size: 0.88rem;
            color: var(--text-light);
            line-height: 1.75;
            margin: 0;
        }

        /* ═══════════════════════════════════════
           HOW IT WORKS SECTION
        ═══════════════════════════════════════ */
        .section-how {
            padding: 100px 0;
            background: #fff;
        }

        .how-container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .how-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            margin-top: 60px;
        }

        .how-step {
            text-align: center;
            position: relative;
        }

        .how-step::after {
            content: '';
            position: absolute;
            top: 36px;
            right: -16px;
            width: 32px;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), transparent);
        }

        .how-step:last-child::after { display: none; }

        .how-step-number {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: var(--gold);
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            box-shadow: 0 8px 25px rgba(128,0,0,0.2);
            transition: all 0.3s ease;
        }

        .how-step:hover .how-step-number {
            transform: scale(1.08);
            box-shadow: 0 12px 35px rgba(128,0,0,0.3);
        }

        .how-step h5 {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .how-step p {
            font-size: 0.85rem;
            color: var(--text-light);
            line-height: 1.7;
            max-width: 220px;
            margin: 0 auto;
        }

        /* ═══════════════════════════════════════
           CTA SECTION
        ═══════════════════════════════════════ */
        .cta-section {
            background: linear-gradient(135deg, var(--maroon-deeper) 0%, var(--maroon) 50%, var(--maroon-dark) 100%);
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section .cta-bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.1;
        }

        .cta-section .cta-bg-orb.orb-a {
            width: 500px;
            height: 500px;
            background: var(--gold);
            top: -200px;
            left: -100px;
        }

        .cta-section .cta-bg-orb.orb-b {
            width: 350px;
            height: 350px;
            background: #fff;
            bottom: -100px;
            right: -50px;
        }

        .cta-container {
            max-width: 680px;
            margin: 0 auto;
            padding: 0 40px;
            position: relative;
            z-index: 2;
        }

        .cta-section .section-tag {
            background: rgba(255,199,44,0.15);
            color: var(--gold);
            border: 1px solid rgba(255,199,44,0.2);
        }

        .cta-section h2 {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 2.6rem;
            font-weight: 800;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .cta-section p {
            color: rgba(255,255,255,0.6);
            font-size: 1.05rem;
            margin-bottom: 40px;
            line-height: 1.7;
        }

        .cta-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-cta-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--gold);
            color: var(--maroon-dark);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            padding: 18px 42px;
            border-radius: 50px;
            border: none;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-gold);
        }

        .btn-cta-primary:hover {
            background: #fff;
            color: var(--maroon);
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(255,255,255,0.2);
        }

        .btn-cta-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            color: rgba(255,255,255,0.85);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            padding: 18px 38px;
            border-radius: 50px;
            border: 2px solid rgba(255,255,255,0.15);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-cta-secondary:hover {
            border-color: var(--gold);
            color: var(--gold);
        }

        /* ═══════════════════════════════════════
           RESPONSIVE
        ═══════════════════════════════════════ */
        @media (max-width: 1100px) {
            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 40px;
            }
            .hero-content p { margin-left: auto; margin-right: auto; }
            .hero-actions { justify-content: center; }
            .hero-content h1 { font-size: 2.8rem; }
            .hero-visual { order: -1; }
            .hero-illustration { max-width: 380px; }
            .hero-floating-tag.tag-1 { right: -10px; }
            .hero-floating-tag.tag-2 { left: -10px; }
        }

        @media (max-width: 991px) {
            .how-grid { grid-template-columns: repeat(2, 1fr); gap: 32px; }
            .how-step::after { display: none; }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .hero-section { min-height: auto; padding: 120px 0 60px; }
            .hero-container { padding: 0 20px; }
            .hero-content h1 { font-size: 2.2rem; }
            .hero-content p { font-size: 0.95rem; }
            .hero-illustration { max-width: 300px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .section-title { font-size: 1.8rem; }
            .search-inner { padding: 0 20px; }
            .features-container, .how-container, .cta-container { padding: 0 20px; }
            .section-features, .section-how { padding: 60px 0; }
            .cta-section { padding: 60px 0; }
            .cta-section h2 { font-size: 1.8rem; }
        }

        @media (max-width: 480px) {
            .hero-content h1 { font-size: 1.8rem; }
            .hero-actions { flex-direction: column; align-items: stretch; }
            .btn-hero-primary, .btn-hero-secondary { justify-content: center; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
            .features-grid { grid-template-columns: 1fr; }
            .how-grid { grid-template-columns: 1fr; }
            .hero-floating-tag { display: none; }
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════
     NAVBAR
═══════════════════════════════════════ -->
@include('partials.public-nav', ['fixed' => true])

<!-- ═══════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════ -->
<section class="hero-section" id="home">
    <div class="hero-bg-elements">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
    <div class="hero-grid-pattern"></div>

    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badge">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                PUP San Juan Library System
            </div>
            <h1>Where Knowledge<br>Meets <span class="text-gold">Innovation</span></h1>
            <p>Discover a world of knowledge at your fingertips. PUPSJ Libris Nexus provides seamless access to thousands of academic resources, research materials, and literary works.</p>
            <div class="hero-actions">
                <a href="{{ route('guest.books') }}" class="btn-hero-primary">
                    Browse Collection
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="#services" class="btn-hero-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                    Our Services
                </a>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-illustration">
                <div class="hero-card">
                    <div class="hero-card-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" color="#5a0000">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    </div>
                    <h3>Digital Library</h3>
                    <p>Access anytime, anywhere</p>
                    <div class="hero-search-preview">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        Search books, authors, subjects...
                    </div>
                </div>
                <div class="hero-floating-tag tag-1">
                    <div class="tag-dot gold"></div>
                    Books and Journals
                </div>
                <div class="hero-floating-tag tag-2">
                    <div class="tag-dot"></div>
                    Available Now
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     SEARCH BAR (Overlapping)
═══════════════════════════════════════ -->
<div class="search-section">
    <div class="search-inner">
        <form action="{{ route('guest.books') }}" method="GET" class="search-card" id="searchForm">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" style="margin-left:16px; flex-shrink:0;">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="text" name="search" placeholder="Search by title, author, ISBN, or subject..." id="searchInput">
            <button type="submit">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                Search
            </button>
        </form>
    </div>
</div>



<!-- ═══════════════════════════════════════
     SERVICES SECTION
═══════════════════════════════════════ -->
<section class="section-features" id="services">
    <div class="features-container">
        <div class="section-header animate-on-scroll">
            <div class="section-tag">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                Library Services
            </div>
            <h2 class="section-title">Everything You Need for a<br><span class="text-maroon">Seamless Library Experience</span></h2>
            <p class="section-subtitle">Explore the full suite of services available at the PUP San Juan Library</p>
        </div>

        <div class="features-grid">
            <div class="feature-card animate-on-scroll">
                <div class="feature-icon">
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </div>
                <h5>Book Catalog & OPAC</h5>
                <p>Search thousands of books by title, author, ISBN, or subject — available to everyone, even without an account.</p>
            </div>

            <div class="feature-card animate-on-scroll">
                <div class="feature-icon">
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
                    </svg>
                </div>
                <h5>Self-Service Borrowing</h5>
                <p>Borrow and return books at the library kiosk — fast, easy, and no need to wait in line.</p>
            </div>

            <div class="feature-card animate-on-scroll">
                <div class="feature-icon">
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </div>
                <h5>Book Reservations</h5>
                <p>Reserve a book in advance and get notified when it becomes available for pick-up.</p>
            </div>

            <div class="feature-card animate-on-scroll">
                <div class="feature-icon">
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>
                    </svg>
                </div>
                <h5>Borrow History</h5>
                <p>Access your complete borrowing history, due dates, and past transactions any time from your portal.</p>
            </div>

            <div class="feature-card animate-on-scroll">
                <div class="feature-icon">
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h5>Penalties & Fines</h5>
                <p>View and monitor your library fees online. All pending fines are settled with the library administrator.</p>
            </div>

            <div class="feature-card animate-on-scroll">
                <div class="feature-icon">
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h5>Book Requests</h5>
                <p>Submit acquisition requests for books you'd like added to the library collection for your studies or research.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     HOW IT WORKS SECTION
═══════════════════════════════════════ -->
<section class="section-how" id="how-it-works">
    <div class="how-container">
        <div class="section-header animate-on-scroll">
            <div class="section-tag">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                Get Started
            </div>
            <h2 class="section-title">How <span class="text-maroon">It Works</span></h2>
            <p class="section-subtitle">Getting started with PUPSJ Libris is quick and easy</p>
        </div>

        <div class="how-grid">
            <div class="how-step animate-on-scroll">
                <div class="how-step-number">1</div>
                <h5>Create Your Account</h5>
                <p>Sign up with your PUP student or faculty credentials to access the full library system.</p>
            </div>
            <div class="how-step animate-on-scroll">
                <div class="how-step-number">2</div>
                <h5>Browse & Search</h5>
                <p>Explore our catalog of thousands of books by title, author, ISBN, or subject.</p>
            </div>
            <div class="how-step animate-on-scroll">
                <div class="how-step-number">3</div>
                <h5>Borrow or Reserve</h5>
                <p>Reserve books online or borrow them at the kiosk with a quick QR scan.</p>
            </div>
            <div class="how-step animate-on-scroll">
                <div class="how-step-number">4</div>
                <h5>Return & Track</h5>
                <p>Return books at the library and track your borrowing history from your dashboard.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     CTA SECTION
═══════════════════════════════════════ -->
<section class="cta-section">
    <div class="cta-bg-orb orb-a"></div>
    <div class="cta-bg-orb orb-b"></div>
    <div class="cta-container animate-on-scroll">
        <div class="section-tag">Start Today</div>
        <h2>Ready to Explore<br>Our Collection?</h2>
        <p>Join hundreds of PUP San Juan students and faculty who already use Libris Nexus for their academic and research needs.</p>
        <div class="cta-actions">
            <a href="{{ route('guest.books') }}" class="btn-cta-primary">
                Browse Books
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('login.selection') }}" class="btn-cta-secondary">
                Login to Your Account
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     FOOTER
═══════════════════════════════════════ -->
@include('partials.public-footer')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Scroll animations using IntersectionObserver
    const observerOptions = {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                // Stagger the animation
                const el = entry.target;
                const siblings = Array.from(el.parentElement.children).filter(c => c.classList.contains('animate-on-scroll'));
                const staggerIndex = siblings.indexOf(el);
                const delay = staggerIndex * 80;

                setTimeout(() => {
                    el.classList.add('visible');
                }, delay);

                observer.unobserve(el);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Close mobile drawer if open
                pubCloseNav();
            }
        });
    });
</script>
</body>
</html>