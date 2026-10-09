@extends('layouts.app')

@section('title', $book->title)

@php
    $collectionLabel = match($book->collection) {
        'Library of Congress' => 'Circulation',
        'Thesis Collection'   => 'Research & Innovation',
        default               => $book->collection,
    };
    $showClassification = in_array($book->collection, ['Library of Congress', 'Filipiniana', 'Circulation'], true)
        && ($book->loc_full_name || $book->loc_number);
    $hasToc = $book->toc_image_path || $book->tocImages->count();
@endphp

@section('content')
{{-- ── HERO ── --}}
<section class="bd-hero">
    <div class="bd-hero-inner">
        <nav class="bd-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('landing') }}">Home</a>
            <span aria-hidden="true">›</span>
            <a href="{{ route('guest.books') }}">Catalog</a>
            <span aria-hidden="true">›</span>
            <span class="bd-crumb-current">{{ \Illuminate\Support\Str::limit($book->title, 40) }}</span>
        </nav>
        <div class="bd-hero-tags">
            @if($collectionLabel)<span class="bd-tag">{{ $collectionLabel }}</span>@endif
            @if($book->is_new_acquisition)<span class="bd-tag bd-tag-gold">New Acquisition</span>@endif
        </div>
        <h1>{{ $book->title }}</h1>
        <p class="bd-hero-author">by <strong>{{ $book->author ?? 'Unknown Author' }}</strong>@if($book->publication_year) · {{ $book->publication_year }}@endif</p>
    </div>
</section>

<div class="bd-page">
    <a href="{{ route('guest.books') }}" class="bd-back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back to Catalog
    </a>

    <div class="bd-card">
        {{-- Cover --}}
        <div class="bd-cover-col">
            <div class="bd-cover">
                <div class="bd-cover-ph">
                    <svg width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <span>PUP San Juan Library</span>
                </div>
                @if($book->title_cover_image_path)
                    <a href="{{ asset('storage/' . $book->title_cover_image_path) }}" target="_blank" rel="noopener" class="bd-cover-link">
                        <img src="{{ asset('storage/' . $book->title_cover_image_path) }}" alt="Cover of {{ $book->title }}" onerror="this.parentElement.remove()">
                    </a>
                @endif
            </div>
        </div>

        {{-- Details --}}
        <div class="bd-info">
            <h2 class="bd-section-title">About this book</h2>
            <dl class="bd-details">
                <div><dt>Author</dt><dd>{{ $book->author ?? 'Unknown' }}</dd></div>
                <div><dt>ISBN / ISSN</dt><dd>{{ $book->isbn ?? '—' }}</dd></div>
                <div><dt>Publisher</dt><dd>{{ $book->publisher ?? '—' }}</dd></div>
                <div><dt>Publication Year</dt><dd>{{ $book->publication_year ?? '—' }}</dd></div>
                <div><dt>Collection</dt><dd>{{ $collectionLabel ?? '—' }}</dd></div>
                @if($showClassification)
                    <div><dt>Classification</dt><dd>{{ $book->loc_full_name ?? $book->loc_number }}</dd></div>
                @endif
                <div><dt>Shelf Location</dt><dd>{{ $book->shelf_location ?? '—' }}</dd></div>
                <div class="bd-wide"><dt>Subject</dt><dd>{{ $book->subject ?? 'General' }}</dd></div>
            </dl>

            @if($book->keywords)
                <div class="bd-keywords">
                    <span class="bd-kw-label">Keywords</span>
                    <div class="bd-kw-list">
                        @foreach(collect(preg_split('/\s*,\s*/', $book->keywords))->filter()->unique()->take(16) as $kw)
                            <a href="{{ route('guest.books', ['search' => $kw]) }}" class="bd-kw">{{ $kw }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Table of Contents --}}
    <section class="bd-toc">
        <h2 class="bd-section-title">Table of Contents</h2>
        @if($hasToc)
            <div class="bd-toc-grid">
                @if($book->toc_image_path)
                    <a href="{{ asset('storage/' . $book->toc_image_path) }}" target="_blank" rel="noopener" class="bd-toc-item">
                        <img src="{{ asset('storage/' . $book->toc_image_path) }}" alt="Table of Contents" loading="lazy">
                    </a>
                @endif
                @foreach($book->tocImages as $tocImage)
                    <a href="{{ asset('storage/' . $tocImage->path) }}" target="_blank" rel="noopener" class="bd-toc-item">
                        <img src="{{ asset('storage/' . $tocImage->path) }}" alt="Table of Contents page {{ $loop->iteration }}" loading="lazy">
                    </a>
                @endforeach
            </div>
        @else
            <div class="bd-toc-empty">No table of contents available for this book.</div>
        @endif
    </section>

    {{-- CTA --}}
    <section class="bd-cta">
        <div class="bd-cta-icon" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <div class="bd-cta-text">
            <h3>Want to borrow this book?</h3>
            <p>Borrowing is done at the PUP San Juan Library kiosk. Log in to reserve it and track your loans.</p>
        </div>
        <a href="{{ route('login.selection') }}" class="bd-cta-btn">Log in →</a>
    </section>
</div>

<style>
/* ── HERO ── */
.bd-hero {
    position: relative;
    margin: 0 calc(50% - 50vw);
    color: #fff;
    background:
        radial-gradient(circle at 88% 15%, rgba(255,199,44,0.18) 0%, transparent 40%),
        linear-gradient(150deg, var(--pup-maroon-deeper) 0%, var(--pup-maroon) 55%, var(--pup-maroon-dark) 100%);
    overflow: hidden;
}
.bd-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: linear-gradient(rgba(255,255,255,0.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.035) 1px, transparent 1px);
    background-size: 44px 44px;
    pointer-events: none;
}
.bd-hero-inner { position: relative; z-index: 1; max-width: 1100px; margin: 0 auto; padding: 30px 40px 84px; }
.bd-crumbs { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-size: 0.78rem; color: rgba(255,255,255,0.6); margin-bottom: 16px; }
.bd-crumbs a { color: var(--pup-gold); text-decoration: none; font-weight: 600; }
.bd-crumbs a:hover { text-decoration: underline; }
.bd-crumb-current { color: rgba(255,255,255,0.75); }
.bd-hero-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
.bd-tag { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; padding: 4px 10px; border-radius: 999px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); }
.bd-tag-gold { background: var(--pup-gold); color: var(--pup-maroon-dark); border-color: var(--pup-gold); }
.bd-hero h1 { font-family: var(--font-serif); font-size: clamp(1.6rem, 4vw, 2.6rem); font-weight: 800; line-height: 1.18; margin: 0 0 8px; overflow-wrap: anywhere; }
.bd-hero-author { margin: 0; font-size: 0.98rem; color: rgba(255,255,255,0.78); }
.bd-hero-author strong { color: #fff; }

/* ── PAGE ── */
.bd-page { max-width: 1100px; margin: -60px auto 0; padding: 0 40px 48px; position: relative; z-index: 2; }
.bd-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 14px;
    padding: 7px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,0.14);
    border: 1px solid rgba(255,255,255,0.28);
    color: #fff;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    backdrop-filter: blur(6px);
    transition: background 0.2s;
}
.bd-back:hover { background: rgba(255,255,255,0.24); }
.bd-card {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
    gap: 32px;
    background: #fff;
    border-radius: 22px;
    padding: 28px;
    box-shadow: 0 16px 50px rgba(58,0,0,0.12);
    border-top: 4px solid var(--pup-gold);
}
.bd-cover { position: relative; aspect-ratio: 3 / 4; border-radius: 16px; overflow: hidden; box-shadow: 0 12px 30px rgba(58,0,0,0.25); }
.bd-cover-ph {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: rgba(255,255,255,0.65);
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    background:
        radial-gradient(circle at 30% 20%, rgba(255,199,44,0.2) 0%, transparent 45%),
        linear-gradient(150deg, #a31515 0%, var(--pup-maroon) 50%, var(--pup-maroon-dark) 100%);
}
.bd-cover-link { position: absolute; inset: 0; cursor: zoom-in; }
.bd-cover-link img { width: 100%; height: 100%; object-fit: cover; display: block; }

.bd-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: var(--font-serif);
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--pup-maroon);
    margin: 0 0 16px;
}
.bd-section-title::before { content: ''; width: 5px; height: 22px; border-radius: 3px; background: var(--pup-gold); }
.bd-details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 28px; margin: 0; }
.bd-details > div { padding: 12px 0; border-bottom: 1px solid #f1ece6; min-width: 0; }
.bd-details .bd-wide { grid-column: 1 / -1; }
.bd-details dt { font-size: 0.68rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--pup-maroon); margin-bottom: 4px; }
.bd-details dd { margin: 0; font-size: 0.92rem; color: var(--text-dark); font-weight: 500; overflow-wrap: anywhere; }
.bd-keywords { margin-top: 18px; }
.bd-kw-label { display: block; font-size: 0.68rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--pup-maroon); margin-bottom: 8px; }
.bd-kw-list { display: flex; flex-wrap: wrap; gap: 6px; }
.bd-kw {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 5px 11px;
    border-radius: 999px;
    background: var(--pup-gold-pale);
    border: 1px solid rgba(255,199,44,0.6);
    color: var(--pup-maroon-dark);
    text-decoration: none;
    transition: all 0.15s;
}
.bd-kw:hover { background: var(--pup-maroon); border-color: var(--pup-maroon); color: #fff; }

/* ── TOC ── */
.bd-toc { margin-top: 28px; background: #fff; border-radius: 22px; padding: 26px 28px; box-shadow: 0 6px 24px rgba(58,0,0,0.06); }
.bd-toc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
.bd-toc-item { display: block; border-radius: 14px; overflow: hidden; border: 1px solid #f1ece6; background: var(--bg-soft); cursor: zoom-in; transition: transform 0.2s, box-shadow 0.2s; }
.bd-toc-item:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(58,0,0,0.12); }
.bd-toc-item img { width: 100%; display: block; aspect-ratio: 3 / 4; object-fit: cover; }
.bd-toc-empty { padding: 22px; text-align: center; border: 1.5px dashed #e6dcd2; border-radius: 14px; color: var(--text-light); font-size: 0.88rem; }

/* ── CTA ── */
.bd-cta {
    margin-top: 28px;
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 22px 26px;
    border-radius: 20px;
    background: linear-gradient(135deg, var(--pup-gold-pale) 0%, #fff 100%);
    border: 1.5px solid rgba(255,199,44,0.55);
    border-left: 6px solid var(--pup-gold);
}
.bd-cta-icon { width: 52px; height: 52px; flex-shrink: 0; border-radius: 14px; background: var(--pup-maroon); color: var(--pup-gold); display: flex; align-items: center; justify-content: center; }
.bd-cta-text { flex: 1; min-width: 0; }
.bd-cta h3 { font-family: var(--font-serif); color: var(--pup-maroon); font-size: 1.15rem; margin: 0 0 4px; }
.bd-cta p { margin: 0; color: var(--text-mid); font-size: 0.86rem; line-height: 1.55; }
.bd-cta-btn {
    flex-shrink: 0;
    background: var(--pup-maroon);
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.88rem;
    padding: 12px 24px;
    border-radius: 999px;
    box-shadow: 0 6px 18px rgba(128,0,0,0.25);
    transition: background 0.2s, transform 0.2s;
}
.bd-cta-btn:hover { background: var(--pup-maroon-dark); transform: translateY(-1px); }

/* ── RESPONSIVE ── */
@media (max-width: 860px) {
    .bd-hero-inner { padding: 24px 20px 76px; }
    .bd-page { padding: 0 16px 36px; }
    .bd-card { grid-template-columns: 1fr; gap: 22px; padding: 20px; }
    .bd-cover-col { max-width: 220px; margin: 0 auto; width: 100%; }
    .bd-toc { padding: 20px; }
    .bd-cta { flex-direction: column; align-items: flex-start; padding: 20px; }
    .bd-cta-btn { width: 100%; text-align: center; }
}
@media (max-width: 480px) {
    .bd-details { grid-template-columns: 1fr; }
    .bd-toc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .bd-crumb-current { display: none; }
}
</style>
@endsection
