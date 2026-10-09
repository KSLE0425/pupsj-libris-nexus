@extends('layouts.app')

@section('title', 'Book Catalog')

@section('content')
{{-- ── HERO ── --}}
<section class="cat-hero">
    <div class="cat-hero-inner">
        <nav class="cat-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('landing') }}">Home</a>
            <span aria-hidden="true">›</span>
            <span>Catalog</span>
        </nav>
        <h1>Browse Our <em>Collection</em></h1>
        <p>Explore books, theses and references held by the PUP San Juan Library. Borrowing is done at the library kiosk.</p>

        <div class="cat-search">
            <svg class="cat-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input type="text" id="liveSearch" placeholder="Search by title, author, ISBN or subject…" value="{{ request('search') }}" aria-label="Search the catalog">
            <button type="button" id="searchClearBtn" class="cat-search-clear" aria-label="Clear search" hidden>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="cat-hero-stats">
            <span><strong id="heroTotal">{{ $books->total() }}</strong> titles</span>
            <span class="dot" aria-hidden="true"></span>
            <span><strong>{{ collect($collectionTypes ?? [])->where('name', '!=', 'Library of Congress')->count() }}</strong> collections</span>
        </div>
    </div>
</section>

<div class="library-catalog">
    <div class="catalog-layout">
        {{-- ── FILTER SIDEBAR (sheet on mobile) ── --}}
        <aside class="filter-sidebar" id="filterSidebar" aria-label="Filters">
            <div class="sheet-head">
                <span>Filters</span>
                <button type="button" class="sheet-close" id="sheetCloseBtn" aria-label="Close filters">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="filter-section">
                <label class="newacq-toggle">
                    <input type="checkbox" id="newAcqToggle">
                    <span class="newacq-switch" aria-hidden="true"></span>
                    New Acquisitions only
                </label>
            </div>

            <div class="filter-section">
                <h4 class="filter-heading">Collection</h4>
                <div class="filter-options" id="collectionFilters">
                    <button type="button" class="filter-chip active" data-collection="" data-has-loc="false" data-has-research="false">All</button>
                    @foreach($collectionTypes ?? [] as $ct)
                        @if($ct->name === 'Library of Congress') @continue @endif
                        <button type="button" class="filter-chip"
                            data-collection="{{ $ct->name }}"
                            data-has-loc="{{ $ct->has_loc_classification ? 'true' : 'false' }}"
                            data-has-research="{{ $ct->has_research_type ? 'true' : 'false' }}">
                            {{ $ct->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- LoC Classification (Circulation / Filipiniana) --}}
            <div class="filter-section" id="classificationSection" hidden>
                <h4 class="filter-heading">Classification (LoC)</h4>
                <select id="locFilter" class="sidebar-select" aria-label="Classification">
                    <option value="">All Classifications</option>
                    @foreach($locClassifications as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Research & Innovation sub-filters --}}
            <div class="filter-section" id="riSection" hidden>
                <h4 class="filter-heading">Research Type</h4>
                <div class="filter-options" id="researchTypeFilters">
                    <button type="button" class="filter-chip active" data-research="">All</button>
                    <button type="button" class="filter-chip" data-research="thesis">Thesis</button>
                    <button type="button" class="filter-chip" data-research="capstone">Capstone</button>
                </div>
                <h4 class="filter-heading" style="margin-top:14px;">Program</h4>
                <select id="riProgramFilter" class="sidebar-select" aria-label="Program">
                    <option value="">All Programs</option>
                    @foreach($programs ?? [] as $prog)
                        <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-section">
                <h4 class="filter-heading">Author</h4>
                <select id="authorFilter" class="sidebar-select" aria-label="Author">
                    <option value="">All Authors</option>
                    @foreach($authors as $author)
                        <option value="{{ $author }}">{{ $author }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-section">
                <h4 class="filter-heading">Year</h4>
                <select id="yearFilter" class="sidebar-select" aria-label="Year">
                    <option value="">All Years</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-section">
                <h4 class="filter-heading">Subject</h4>
                <select id="subjectFilter" class="sidebar-select" aria-label="Subject">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject }}">{{ $subject }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-section">
                <h4 class="filter-heading">Publisher</h4>
                <select id="publisherFilter" class="sidebar-select" aria-label="Publisher">
                    <option value="">All Publishers</option>
                </select>
            </div>

            <div class="sheet-actions">
                <button type="button" id="clearFiltersBtn" class="clear-filters-btn">Clear all</button>
                <button type="button" id="sheetApplyBtn" class="sheet-apply-btn">Show results</button>
            </div>
        </aside>
        <div class="sheet-backdrop" id="sheetBackdrop" hidden></div>

        {{-- ── RESULTS ── --}}
        <section class="catalog-main" aria-live="polite">
            <div class="results-bar">
                <div>
                    <h2 class="results-title">All Books</h2>
                    <span class="results-count" id="resultCount">{{ $books->total() }} books</span>
                </div>
                <button type="button" class="mobile-filter-toggle" id="mobileFilterToggle">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filters
                    <span class="filter-count" id="filterCount" hidden>0</span>
                </button>
            </div>

            <div class="books-grid" id="booksGrid"></div>
            <div class="pagination-wrapper" id="paginationContainer"></div>
            <div class="empty-state" id="emptyState" hidden>
                <div class="empty-icon">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </div>
                <p>No books found</p>
                <small>Try a different search or clear some filters.</small>
                <button type="button" class="clear-filters-btn" onclick="document.getElementById('clearFiltersBtn').click()">Clear all filters</button>
            </div>
        </section>
    </div>

    {{-- ── CTA ── --}}
    <section class="cat-cta">
        <div>
            <h3>Found something you like?</h3>
            <p>Visit the PUP San Juan Library kiosk to borrow, or log in to reserve and track your books.</p>
        </div>
        <a href="{{ route('login.selection') }}" class="cat-cta-btn">Log in to your portal →</a>
    </section>
</div>

<script>
const allBooks = @json($allBooks);
let filteredBooks = [...allBooks];
let currentPage = 1;
const perPage = 12;

const booksGrid = document.getElementById('booksGrid');
const paginationContainer = document.getElementById('paginationContainer');
const emptyState = document.getElementById('emptyState');
const resultCount = document.getElementById('resultCount');
const classificationSection = document.getElementById('classificationSection');
const riSection = document.getElementById('riSection');
const filterCount = document.getElementById('filterCount');

const liveSearch = document.getElementById('liveSearch');
const searchClearBtn = document.getElementById('searchClearBtn');
const collectionChips = document.querySelectorAll('#collectionFilters .filter-chip');
const researchTypeChips = document.querySelectorAll('#researchTypeFilters .filter-chip');
const authorSelect = document.getElementById('authorFilter');
const yearSelect = document.getElementById('yearFilter');
const subjectSelect = document.getElementById('subjectFilter');
const publisherSelect = document.getElementById('publisherFilter');
const locSelect = document.getElementById('locFilter');
const riProgramSelect = document.getElementById('riProgramFilter');
const newAcqToggle = document.getElementById('newAcqToggle');
const clearBtn = document.getElementById('clearFiltersBtn');

// Populate publisher dropdown
const publishers = [...new Set(allBooks.map(b => b.publisher).filter(Boolean))].sort();
publishers.forEach(pub => {
    const opt = document.createElement('option');
    opt.value = pub; opt.textContent = pub;
    publisherSelect.appendChild(opt);
});

let currentCollection = '';
let currentCollectionHasLoc      = false;
let currentCollectionHasResearch = false;
let currentLoc = '';
let currentNewAcq = false;
let currentResearchType = '';
let currentRiProgram = '';
let currentAuthor = '';
let currentYear = '';
let currentSubject = '';
let currentPublisher = '';
let searchTerm = '';

function applyFilters() {
    filteredBooks = allBooks.filter(book => {
        // Collection — Circulation also includes "Library of Congress" books
        if (currentCollection) {
            if (currentCollection === 'Circulation') {
                if (book.collection !== 'Circulation' && book.collection !== 'Library of Congress') return false;
            } else {
                if (book.collection !== currentCollection) return false;
            }
        }
        if (currentNewAcq && !book.is_new_acquisition) return false;
        if (currentLoc && book.loc_number && !book.loc_number.startsWith(currentLoc)) return false;
        if (currentResearchType && book.research_type !== currentResearchType) return false;
        if (currentRiProgram && String(book.course_id) !== String(currentRiProgram)) return false;
        if (currentAuthor && book.author !== currentAuthor) return false;
        if (currentYear && book.publication_year != currentYear) return false;
        if (currentSubject && book.subject !== currentSubject) return false;
        if (currentPublisher && book.publisher !== currentPublisher) return false;
        if (searchTerm) {
            const t = searchTerm.toLowerCase();
            return (book.title && book.title.toLowerCase().includes(t)) ||
                   (book.author && book.author.toLowerCase().includes(t)) ||
                   (book.isbn && book.isbn.toLowerCase().includes(t)) ||
                   (book.subject && book.subject.toLowerCase().includes(t));
        }
        return true;
    });

    classificationSection.hidden = !currentCollectionHasLoc;
    if (!currentCollectionHasLoc) { currentLoc = ''; if (locSelect) locSelect.value = ''; }

    riSection.hidden = !currentCollectionHasResearch;
    if (!currentCollectionHasResearch) {
        currentResearchType = ''; currentRiProgram = '';
        researchTypeChips.forEach(c => c.classList.toggle('active', c.dataset.research === ''));
        if (riProgramSelect) riProgramSelect.value = '';
    }

    const active = [currentCollection, currentNewAcq, currentLoc, currentResearchType, currentRiProgram,
                    currentAuthor, currentYear, currentSubject, currentPublisher].filter(Boolean).length;
    filterCount.textContent = active;
    filterCount.hidden = active === 0;
    searchClearBtn.hidden = !liveSearch.value;

    currentPage = 1;
    renderBooks();
    updateURL();
}

const bookIcon = `<svg width="38" height="38" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>`;

function renderBooks() {
    const start = (currentPage - 1) * perPage;
    const pageBooks = filteredBooks.slice(start, start + perPage);
    const totalPages = Math.ceil(filteredBooks.length / perPage) || 1;

    resultCount.textContent = filteredBooks.length + (filteredBooks.length === 1 ? ' book' : ' books');
    booksGrid.innerHTML = '';
    if (pageBooks.length === 0) {
        booksGrid.hidden = true;
        emptyState.hidden = false;
        paginationContainer.innerHTML = '';
        return;
    }
    booksGrid.hidden = false;
    emptyState.hidden = true;

    pageBooks.forEach(book => {
        const cover = book.title_cover_image_path
            ? `<img src="/storage/${escapeHtml(book.title_cover_image_path)}" alt="" class="book-cover-img" loading="lazy" onerror="this.remove()">`
            : '';
        const card = document.createElement('a');
        card.className = 'book-card';
        card.href = `/guest/books/${book.id}`;
        card.innerHTML = `
            <div class="book-cover">
                <div class="book-cover-ph">${bookIcon}</div>
                ${cover}
                ${book.is_new_acquisition ? '<span class="new-badge">New</span>' : ''}
                ${book.collection ? `<span class="coll-tag">${escapeHtml(book.collection === 'Library of Congress' ? 'Circulation' : book.collection)}</span>` : ''}
            </div>
            <div class="book-card-body">
                <h3 class="book-title" title="${escapeHtml(book.title)}">${escapeHtml(book.title)}</h3>
                <p class="book-author">${escapeHtml(book.author || 'Unknown Author')}</p>
                <div class="book-meta">
                    ${book.publication_year ? `<span>${book.publication_year}</span>` : ''}
                    ${book.publisher ? `<span class="book-pub">${escapeHtml(book.publisher)}</span>` : ''}
                </div>
                <span class="btn-view-details">View details <span aria-hidden="true">→</span></span>
            </div>
        `;
        booksGrid.appendChild(card);
    });

    let pagHtml = '';
    if (totalPages > 1) {
        pagHtml += `<div class="pagination">`;
        if (currentPage > 1) pagHtml += `<button type="button" class="page-link" data-page="${currentPage-1}" aria-label="Previous page">‹</button>`;
        for (let i = 1; i <= totalPages; i++) {
            pagHtml += `<button type="button" class="page-link${i === currentPage ? ' active' : ''}" data-page="${i}">${i}</button>`;
        }
        if (currentPage < totalPages) pagHtml += `<button type="button" class="page-link" data-page="${currentPage+1}" aria-label="Next page">›</button>`;
        pagHtml += `</div>`;
    }
    paginationContainer.innerHTML = pagHtml;
    paginationContainer.querySelectorAll('.page-link').forEach(btn => {
        btn.addEventListener('click', function() {
            currentPage = parseInt(this.dataset.page);
            renderBooks();
            document.querySelector('.catalog-main').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
}

function updateURL() {
    const params = new URLSearchParams();
    if (searchTerm) params.set('search', searchTerm);
    if (currentCollection) params.set('collection', currentCollection);
    if (currentLoc) params.set('loc_class', currentLoc);
    if (currentNewAcq) params.set('new_acquisition', '1');
    if (currentResearchType) params.set('research_type', currentResearchType);
    if (currentRiProgram) params.set('program_id', currentRiProgram);
    if (currentAuthor) params.set('author', currentAuthor);
    if (currentYear) params.set('year', currentYear);
    if (currentSubject) params.set('subject', currentSubject);
    if (currentPublisher) params.set('publisher', currentPublisher);
    history.replaceState(null, '', window.location.pathname + (params.toString() ? '?' + params.toString() : ''));
}

function readInitialParams() {
    const params = new URLSearchParams(window.location.search);
    searchTerm = params.get('search') || '';
    currentCollection = params.get('collection') || '';
    currentLoc = params.get('loc_class') || '';
    currentNewAcq = params.get('new_acquisition') === '1';
    currentResearchType = params.get('research_type') || '';
    currentRiProgram = params.get('program_id') || '';
    currentAuthor = params.get('author') || '';
    currentYear = params.get('year') || '';
    currentSubject = params.get('subject') || '';
    currentPublisher = params.get('publisher') || '';

    liveSearch.value = searchTerm;
    newAcqToggle.checked = currentNewAcq;
    collectionChips.forEach(b => {
        const match = b.dataset.collection === currentCollection;
        b.classList.toggle('active', match);
        if (match) {
            currentCollectionHasLoc      = b.dataset.hasLoc      === 'true';
            currentCollectionHasResearch = b.dataset.hasResearch === 'true';
        }
    });
    if (locSelect) locSelect.value = currentLoc;
    researchTypeChips.forEach(b => b.classList.toggle('active', b.dataset.research === currentResearchType));
    if (riProgramSelect) riProgramSelect.value = currentRiProgram;
    authorSelect.value = currentAuthor;
    yearSelect.value = currentYear;
    subjectSelect.value = currentSubject;
    publisherSelect.value = currentPublisher;
    applyFilters();
}

// Live search (300ms debounce)
let searchDebounceTimer = null;
liveSearch.addEventListener('input', () => {
    clearTimeout(searchDebounceTimer);
    searchClearBtn.hidden = !liveSearch.value;
    searchDebounceTimer = setTimeout(() => {
        searchTerm = liveSearch.value.trim();
        applyFilters();
    }, 300);
});
searchClearBtn.addEventListener('click', () => {
    liveSearch.value = ''; searchTerm = '';
    applyFilters();
    liveSearch.focus();
});

collectionChips.forEach(btn => btn.addEventListener('click', function() {
    collectionChips.forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    currentCollection = this.dataset.collection;
    currentCollectionHasLoc      = this.dataset.hasLoc      === 'true';
    currentCollectionHasResearch = this.dataset.hasResearch === 'true';
    applyFilters();
}));

if (locSelect) locSelect.addEventListener('change', () => { currentLoc = locSelect.value; applyFilters(); });
newAcqToggle.addEventListener('change', () => { currentNewAcq = newAcqToggle.checked; applyFilters(); });

researchTypeChips.forEach(btn => btn.addEventListener('click', function() {
    researchTypeChips.forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    currentResearchType = this.dataset.research;
    applyFilters();
}));

if (riProgramSelect) riProgramSelect.addEventListener('change', () => { currentRiProgram = riProgramSelect.value; applyFilters(); });
authorSelect.addEventListener('change', () => { currentAuthor = authorSelect.value; applyFilters(); });
yearSelect.addEventListener('change', () => { currentYear = yearSelect.value; applyFilters(); });
subjectSelect.addEventListener('change', () => { currentSubject = subjectSelect.value; applyFilters(); });
publisherSelect.addEventListener('change', () => { currentPublisher = publisherSelect.value; applyFilters(); });

clearBtn.addEventListener('click', () => {
    liveSearch.value = ''; searchTerm = '';
    currentCollection = ''; currentLoc = ''; currentNewAcq = false;
    currentResearchType = ''; currentRiProgram = '';
    currentAuthor = ''; currentYear = ''; currentSubject = ''; currentPublisher = '';
    currentCollectionHasLoc = false; currentCollectionHasResearch = false;
    collectionChips.forEach(b => b.classList.remove('active'));
    collectionChips[0].classList.add('active');
    researchTypeChips.forEach(b => b.classList.remove('active'));
    if (researchTypeChips[0]) researchTypeChips[0].classList.add('active');
    newAcqToggle.checked = false;
    if (locSelect) locSelect.value = '';
    if (riProgramSelect) riProgramSelect.value = '';
    authorSelect.value = ''; yearSelect.value = ''; subjectSelect.value = ''; publisherSelect.value = '';
    applyFilters();
});

// Mobile filter sheet
const filterSidebar = document.getElementById('filterSidebar');
const sheetBackdrop = document.getElementById('sheetBackdrop');
function openSheet()  { filterSidebar.classList.add('open'); sheetBackdrop.hidden = false; document.body.style.overflow = 'hidden'; }
function closeSheet() { filterSidebar.classList.remove('open'); sheetBackdrop.hidden = true; document.body.style.overflow = ''; }
document.getElementById('mobileFilterToggle').addEventListener('click', openSheet);
document.getElementById('sheetCloseBtn').addEventListener('click', closeSheet);
document.getElementById('sheetApplyBtn').addEventListener('click', closeSheet);
sheetBackdrop.addEventListener('click', closeSheet);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSheet(); });

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

readInitialParams();
</script>

<style>
/* ── HERO ── */
.cat-hero {
    position: relative;
    margin: 0 calc(50% - 50vw);
    background:
        radial-gradient(circle at 85% 20%, rgba(255,199,44,0.16) 0%, transparent 40%),
        radial-gradient(circle at 10% 90%, rgba(255,255,255,0.06) 0%, transparent 45%),
        linear-gradient(150deg, var(--pup-maroon-deeper) 0%, var(--pup-maroon) 55%, var(--pup-maroon-dark) 100%);
    color: #fff;
    overflow: hidden;
}
.cat-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: linear-gradient(rgba(255,255,255,0.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.035) 1px, transparent 1px);
    background-size: 44px 44px;
    pointer-events: none;
}
.cat-hero-inner { position: relative; z-index: 1; max-width: 1320px; margin: 0 auto; padding: 36px 40px 44px; }
.cat-crumbs { display: flex; align-items: center; gap: 8px; font-size: 0.78rem; color: rgba(255,255,255,0.6); margin-bottom: 14px; }
.cat-crumbs a { color: var(--pup-gold); text-decoration: none; font-weight: 600; }
.cat-crumbs a:hover { text-decoration: underline; }
.cat-hero h1 { font-family: var(--font-serif); font-size: clamp(1.8rem, 4vw, 2.7rem); font-weight: 800; margin: 0 0 8px; line-height: 1.15; }
.cat-hero h1 em { font-style: normal; color: var(--pup-gold); }
.cat-hero p { margin: 0 0 22px; color: rgba(255,255,255,0.75); font-size: 0.95rem; max-width: 620px; line-height: 1.6; }
.cat-search {
    position: relative;
    max-width: 720px;
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 999px;
    box-shadow: 0 12px 36px rgba(0,0,0,0.25);
    border: 3px solid rgba(255,199,44,0.55);
}
.cat-search-icon { position: absolute; left: 20px; color: var(--pup-maroon); pointer-events: none; }
.cat-search input {
    flex: 1;
    min-width: 0;
    border: none;
    outline: none;
    background: transparent;
    padding: 16px 48px 16px 54px;
    font: inherit;
    font-size: 0.98rem;
    color: var(--text-dark);
    border-radius: 999px;
}
.cat-search-clear {
    position: absolute;
    right: 12px;
    width: 32px; height: 32px;
    border: none;
    border-radius: 50%;
    background: #f3eded;
    color: var(--pup-maroon);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
}
.cat-hero-stats { display: flex; align-items: center; gap: 12px; margin-top: 18px; font-size: 0.82rem; color: rgba(255,255,255,0.7); }
.cat-hero-stats strong { color: var(--pup-gold); font-weight: 800; }
.cat-hero-stats .dot { width: 4px; height: 4px; border-radius: 50%; background: rgba(255,255,255,0.4); }

/* ── LAYOUT ── */
.library-catalog { padding: 28px 40px 48px; }
.catalog-layout { display: flex; gap: 28px; align-items: flex-start; }

/* ── FILTER SIDEBAR ── */
.filter-sidebar {
    width: 270px;
    flex-shrink: 0;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 22px rgba(58,0,0,0.06);
    border-top: 4px solid var(--pup-maroon);
    padding: 20px 20px 18px;
    position: sticky;
    top: 92px;
}
.sheet-head { display: none; }
.filter-section { padding: 14px 0; border-bottom: 1px solid #f1ece6; }
.sheet-head + .filter-section { padding-top: 0; }
.filter-heading {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--pup-maroon);
    margin: 0 0 10px;
}
.filter-options { display: flex; flex-wrap: wrap; gap: 6px; }
.filter-chip {
    font: inherit;
    font-size: 0.76rem;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1.5px solid #ece4dc;
    background: #fdfbf7;
    color: var(--text-mid);
    cursor: pointer;
    transition: all 0.15s;
}
.filter-chip:hover { border-color: var(--pup-maroon); color: var(--pup-maroon); }
.filter-chip.active { background: var(--pup-maroon); border-color: var(--pup-maroon); color: #fff; box-shadow: 0 3px 10px rgba(128,0,0,0.22); }
.sidebar-select {
    width: 100%;
    font: inherit;
    font-size: 0.82rem;
    padding: 9px 12px;
    border-radius: 10px;
    border: 1.5px solid #ece4dc;
    background: #fdfbf7;
    color: var(--text-dark);
    outline: none;
    cursor: pointer;
}
.sidebar-select:focus { border-color: var(--pup-maroon); box-shadow: 0 0 0 3px rgba(128,0,0,0.08); }
.newacq-toggle { display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.84rem; font-weight: 600; color: var(--text-dark); }
.newacq-toggle input { position: absolute; opacity: 0; pointer-events: none; }
.newacq-switch { position: relative; width: 38px; height: 22px; border-radius: 999px; background: #e4dcd4; transition: background 0.2s; flex-shrink: 0; }
.newacq-switch::after { content: ''; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); transition: transform 0.2s; }
.newacq-toggle input:checked + .newacq-switch { background: var(--pup-maroon); }
.newacq-toggle input:checked + .newacq-switch::after { transform: translateX(16px); background: var(--pup-gold); }
.newacq-toggle input:focus-visible + .newacq-switch { outline: 2px solid var(--pup-gold); outline-offset: 2px; }
.sheet-actions { display: flex; gap: 8px; padding-top: 16px; }
.clear-filters-btn {
    flex: 1;
    font: inherit;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1.5px solid rgba(128,0,0,0.25);
    background: #fff;
    color: var(--pup-maroon);
    cursor: pointer;
    transition: all 0.15s;
}
.clear-filters-btn:hover { background: var(--pup-gold-pale); border-color: var(--pup-maroon); }
.sheet-apply-btn { display: none; }
.sheet-backdrop { position: fixed; inset: 0; background: rgba(26,0,0,0.45); z-index: 1100; }

/* ── RESULTS ── */
.catalog-main { flex: 1; min-width: 0; scroll-margin-top: 90px; }
.results-bar { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
.results-title { font-family: var(--font-serif); font-size: 1.5rem; font-weight: 800; color: var(--pup-maroon); margin: 0; }
.results-count { font-size: 0.8rem; color: var(--text-light); font-weight: 600; }
.mobile-filter-toggle {
    display: none;
    align-items: center;
    gap: 8px;
    font: inherit;
    font-size: 0.84rem;
    font-weight: 700;
    padding: 9px 16px;
    border-radius: 999px;
    border: none;
    background: var(--pup-maroon);
    color: #fff;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(128,0,0,0.25);
}
.filter-count { background: var(--pup-gold); color: var(--pup-maroon); border-radius: 999px; font-size: 0.7rem; padding: 1px 7px; font-weight: 800; }

.books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
.book-card {
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 3px 16px rgba(58,0,0,0.06);
    border: 1px solid #f1ece6;
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}
.book-card:hover { transform: translateY(-4px); box-shadow: 0 14px 34px rgba(128,0,0,0.14); border-color: rgba(128,0,0,0.2); }
.book-card:focus-visible { outline: 3px solid var(--pup-gold); outline-offset: 2px; }
.book-cover { position: relative; aspect-ratio: 4 / 3; overflow: hidden; }
.book-cover-ph {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.6);
    background:
        radial-gradient(circle at 30% 25%, rgba(255,199,44,0.18) 0%, transparent 45%),
        linear-gradient(145deg, #a31515 0%, var(--pup-maroon) 50%, var(--pup-maroon-dark) 100%);
}
.book-cover-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease; }
.book-card:hover .book-cover-img { transform: scale(1.04); }
.new-badge {
    position: absolute;
    top: 10px; left: 10px;
    background: var(--pup-gold);
    color: var(--pup-maroon-dark);
    font-size: 0.64rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 999px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.coll-tag {
    position: absolute;
    bottom: 10px; left: 10px;
    max-width: calc(100% - 20px);
    background: rgba(26,0,0,0.6);
    backdrop-filter: blur(6px);
    color: #fff;
    font-size: 0.66rem;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.book-card-body { display: flex; flex-direction: column; gap: 6px; padding: 14px 16px 16px; flex: 1; }
.book-title {
    font-family: var(--font-serif);
    font-size: 1.02rem;
    font-weight: 700;
    line-height: 1.3;
    color: var(--text-dark);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.book-author { font-size: 0.8rem; color: var(--pup-maroon); font-weight: 600; margin: 0; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.book-meta { display: flex; flex-wrap: wrap; gap: 6px; font-size: 0.72rem; color: var(--text-light); }
.book-meta span { background: var(--bg-soft); padding: 2px 8px; border-radius: 6px; }
.book-meta .book-pub { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.btn-view-details {
    margin-top: auto;
    padding-top: 10px;
    border-top: 1px solid #f4efe9;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--pup-maroon);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.btn-view-details span { transition: transform 0.2s; }
.book-card:hover .btn-view-details span { transform: translateX(4px); }

.pagination-wrapper { margin-top: 28px; }
.pagination { display: flex; justify-content: center; flex-wrap: wrap; gap: 6px; }
.page-link {
    min-width: 38px; height: 38px;
    padding: 0 12px;
    border-radius: 10px;
    border: 1.5px solid #ece4dc;
    background: #fff;
    color: var(--text-mid);
    font: inherit;
    font-size: 0.84rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s;
}
.page-link:hover { border-color: var(--pup-maroon); color: var(--pup-maroon); }
.page-link.active { background: var(--pup-maroon); border-color: var(--pup-maroon); color: var(--pup-gold); }

.empty-state { text-align: center; background: #fff; border-radius: 18px; padding: 48px 24px; box-shadow: 0 3px 16px rgba(58,0,0,0.05); }
.empty-icon { width: 72px; height: 72px; margin: 0 auto 14px; border-radius: 20px; background: rgba(128,0,0,0.07); color: var(--pup-maroon); display: flex; align-items: center; justify-content: center; }
.empty-state p { font-family: var(--font-serif); font-size: 1.2rem; font-weight: 700; color: var(--pup-maroon); margin: 0 0 4px; }
.empty-state small { display: block; color: var(--text-light); margin-bottom: 18px; }
.empty-state .clear-filters-btn { flex: none; }

/* ── CTA ── */
.cat-cta {
    margin-top: 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 26px 30px;
    border-radius: 20px;
    background: linear-gradient(135deg, var(--pup-gold-pale) 0%, #fff 100%);
    border: 1.5px solid rgba(255,199,44,0.55);
    border-left: 6px solid var(--pup-gold);
}
.cat-cta h3 { font-family: var(--font-serif); color: var(--pup-maroon); font-size: 1.25rem; margin: 0 0 4px; }
.cat-cta p { margin: 0; color: var(--text-mid); font-size: 0.88rem; }
.cat-cta-btn {
    flex-shrink: 0;
    background: var(--pup-maroon);
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.88rem;
    padding: 12px 22px;
    border-radius: 999px;
    box-shadow: 0 6px 18px rgba(128,0,0,0.25);
    transition: background 0.2s, transform 0.2s;
}
.cat-cta-btn:hover { background: var(--pup-maroon-dark); transform: translateY(-1px); }

/* ── RESPONSIVE ── */
@media (max-width: 991px) {
    .cat-hero-inner { padding: 26px 20px 32px; }
    .library-catalog { padding: 20px 16px 36px; }
    .mobile-filter-toggle { display: inline-flex; }
    .filter-sidebar {
        position: fixed;
        top: auto; left: 0; right: 0; bottom: 0;
        width: 100%;
        max-height: 82vh;
        overflow-y: auto;
        border-radius: 22px 22px 0 0;
        border-top: 4px solid var(--pup-gold);
        padding: 0 20px calc(16px + env(safe-area-inset-bottom, 0px));
        z-index: 1200;
        transform: translateY(105%);
        transition: transform 0.3s cubic-bezier(.4,0,.2,1);
        box-shadow: 0 -12px 40px rgba(0,0,0,0.25);
    }
    .filter-sidebar.open { transform: translateY(0); }
    .sheet-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        background: #fff;
        padding: 16px 0 12px;
        margin-bottom: 4px;
        z-index: 1;
        font-family: var(--font-serif);
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--pup-maroon);
        border-bottom: 1px solid #f1ece6;
    }
    .sheet-close { width: 36px; height: 36px; border-radius: 50%; border: none; background: #f6efe9; color: var(--pup-maroon); display: flex; align-items: center; justify-content: center; cursor: pointer; }
    .sheet-head + .filter-section { padding-top: 14px; }
    .sheet-actions { position: sticky; bottom: calc(-16px - env(safe-area-inset-bottom, 0px)); background: #fff; padding: 14px 0 4px; }
    .sheet-apply-btn {
        display: block;
        flex: 1.4;
        font: inherit;
        font-size: 0.86rem;
        font-weight: 700;
        padding: 11px 14px;
        border-radius: 10px;
        border: none;
        background: var(--pup-maroon);
        color: #fff;
        cursor: pointer;
    }
    .cat-cta { flex-direction: column; align-items: flex-start; padding: 22px; }
    .cat-cta-btn { width: 100%; text-align: center; }
}
@media (max-width: 560px) {
    .cat-hero p { font-size: 0.86rem; }
    .cat-search input { padding: 13px 44px 13px 48px; font-size: 0.9rem; }
    .cat-search-icon { left: 16px; }
    .books-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .book-card-body { padding: 10px 12px 12px; gap: 4px; }
    .book-title { font-size: 0.88rem; }
    .book-author { font-size: 0.72rem; }
    .book-meta .book-pub { display: none; }
    .coll-tag { display: none; }
    .results-title { font-size: 1.2rem; }
}
@media (max-width: 340px) {
    .books-grid { grid-template-columns: 1fr; }
}
</style>
@endsection
