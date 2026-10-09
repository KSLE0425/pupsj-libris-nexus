@extends('layouts.faculty')

@section('title', $book->title)

@push('styles')
<style>
    /* ── PAGE HEADER ── */
    .page-header { margin-bottom: 24px; }

    /* ── BACK BUTTON ── */
    .back-button-container { margin-bottom: 1.5rem; }
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: transparent;
        color: var(--maroon);
        border: 2px solid var(--maroon);
        padding: 0.5rem 1rem;
        border-radius: 40px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        transition: all 0.2s;
        min-height: 44px;
    }
    .back-btn:hover { background: var(--maroon); color: white; transform: translateX(-4px); }
    .back-btn svg { stroke: currentColor; }

    /* ── BOOK DETAILS CARD ── */
    .book-details-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    .book-details-header {
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .book-details-title {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        letter-spacing: -0.02em;
        word-break: break-word;
    }
    .new-badge {
        display: inline-block;
        background: linear-gradient(135deg, var(--yellow), var(--yellow-dark));
        color: var(--maroon);
        font-size: 0.7rem;
        font-weight: 800;
        padding: 0.3rem 0.9rem;
        border-radius: 30px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .book-details-body { padding: 2rem; }

    /* ── DETAILS GRID: cover | details | details ── */
    .details-grid {
        display: grid;
        grid-template-columns: 240px 1fr 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    .cover-column { display: flex; flex-direction: column; gap: 0.75rem; }
    .cover-frame {
        width: 100%;
        aspect-ratio: 3 / 4;
        border-radius: 16px;
        overflow: hidden;
        background: #f8f9fc;
        border: 1px solid #e8e8e8;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }
    .cover-frame img { width: 100%; height: 100%; object-fit: cover; display: block; cursor: zoom-in; }
    .cover-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at 30% 20%, #a31515 0%, var(--maroon) 55%, var(--maroon-dark) 100%);
        color: rgba(255, 255, 255, 0.75);
    }
    .cover-caption {
        text-align: center;
        font-size: 0.72rem;
        color: #8a94a6;
        font-weight: 500;
    }
    .details-section {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        background: #f8f9fc;
        padding: 1.25rem;
        border-radius: 16px;
        min-width: 0;
    }
    .detail-item {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 0.5rem;
        padding: 0.625rem 0;
        border-bottom: 1px solid #e8e8e8;
    }
    .detail-item:last-child { border-bottom: none; }
    .detail-label {
        font-weight: 700;
        color: var(--maroon);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        min-width: 130px;
    }
    .detail-value {
        color: #1a2a3a;
        font-size: 0.875rem;
        word-break: break-word;
        flex: 1;
        font-weight: 500;
    }

    /* ── STATUS BADGE ── */
    .status-badge {
        display: inline-block;
        padding: 0.3rem 1rem;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .status-badge.available { background: #E8F5E9; color: #2E7D32; border-left: 3px solid #4CAF50; }
    .status-badge.borrowed { background: #FFF3E0; color: #E65100; border-left: 3px solid #FF9800; }
    .status-badge.archived { background: #F3F4F6; color: #6B7280; border-left: 3px solid #9CA3AF; }
    .status-badge.default { background: #E3F2FD; color: #1565C0; border-left: 3px solid #2196F3; }
    .notify-btn {
        margin-left: 10px;
        background: var(--maroon);
        color: white;
        border: none;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        cursor: pointer;
    }
    .notify-note { margin-left: 10px; font-size: 0.8rem; color: #888; }

    /* ── TABLE OF CONTENTS ── */
    .toc-section {
        margin-top: 2rem;
        padding-top: 1.75rem;
        border-top: 2px solid #e8e8e8;
    }
    .image-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 700;
        color: var(--maroon);
        font-size: 0.875rem;
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .image-label::before {
        content: '';
        width: 4px;
        height: 18px;
        background: var(--yellow);
        border-radius: 2px;
    }
    .toc-gallery { display: flex; flex-wrap: wrap; gap: 12px; }
    .image-wrapper {
        background: #f8f9fc;
        border-radius: 16px;
        padding: 1rem;
        border: 1px solid #e8e8e8;
        transition: all 0.2s;
    }
    .image-wrapper:hover { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); transform: scale(1.01); }
    .book-image { max-width: 100%; max-height: 360px; border-radius: 12px; display: block; cursor: zoom-in; }
    .toc-empty {
        background: #f8f9fc;
        border: 1px dashed #d6dbe4;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        color: #8a94a6;
        font-size: 0.85rem;
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 1199px) {
        .details-grid { grid-template-columns: 200px 1fr 1fr; }
        .detail-item { flex-direction: column; gap: 0.25rem; }
        .detail-label { min-width: auto; }
    }
    @media (max-width: 991px) {
        .details-grid { grid-template-columns: 1fr; }
        .cover-column { max-width: 240px; margin: 0 auto; width: 100%; }
    }
    @media (max-width: 768px) {
        .book-details-header { padding: 1.25rem; flex-direction: column; align-items: flex-start; }
        .book-details-title { font-size: 1.25rem; }
        .book-details-body { padding: 1.25rem; }
        .details-section { padding: 1rem; }
        .detail-item { padding: 0.5rem 0; }
        .image-wrapper { padding: 0.75rem; }
        .book-image { max-height: 240px; }
    }
    @media (max-width: 480px) {
        .back-btn { padding: 0.4rem 0.8rem; font-size: 0.8rem; min-height: 38px; }
        .back-btn svg { width: 14px; height: 14px; }
        .book-details-title { font-size: 1.1rem; }
        .new-badge { font-size: 0.6rem; padding: 0.2rem 0.6rem; }
        .detail-label { font-size: 0.7rem; }
        .detail-value { font-size: 0.8rem; }
        .image-label { font-size: 0.75rem; }
        .status-badge { font-size: 0.7rem; padding: 0.2rem 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="back-button-container">
        <a href="{{ route('faculty.catalog') }}" class="back-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Back to Catalog
        </a>
    </div>
</div>

<div class="book-details-card">
    <div class="book-details-header">
        <h1 class="book-details-title">{{ $book->title }}</h1>
        @if($book->is_new_acquisition)
            <span class="new-badge">NEW</span>
        @endif
    </div>

    <div class="book-details-body">
        <div class="details-grid">
            {{-- Column 1: Cover page --}}
            <div class="cover-column">
                <div class="cover-frame">
                    @if($book->title_cover_image_path)
                        <a href="{{ asset('storage/' . $book->title_cover_image_path) }}" target="_blank" rel="noopener">
                            <img src="{{ asset('storage/' . $book->title_cover_image_path) }}" alt="Cover of {{ $book->title }}">
                        </a>
                    @else
                        <div class="cover-placeholder">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 4h7a3 3 0 0 1 3 3v13a2 2 0 0 0-2-2H2z"/>
                                <path d="M22 4h-7a3 3 0 0 0-3 3v13a2 2 0 0 1 2-2h8z"/>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="cover-caption">{{ $book->title_cover_image_path ? 'Cover page' : 'No cover image available' }}</div>
            </div>

            {{-- Column 2: Identification & subjects --}}
            <div class="details-section">
                <div class="detail-item">
                    <span class="detail-label">Author</span>
                    <span class="detail-value">{{ $book->author ?? 'Unknown' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">ISBN/ISSN</span>
                    <span class="detail-value">{{ $book->isbn ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Barcode</span>
                    <span class="detail-value">{{ $book->barcode ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Subject</span>
                    <span class="detail-value">{{ $book->subject ?? 'General' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Sub-Keywords</span>
                    <span class="detail-value">{{ $book->keywords ?? 'None' }}</span>
                </div>
            </div>

            {{-- Column 3: Publication, location & status --}}
            <div class="details-section">
                <div class="detail-item">
                    <span class="detail-label">Publisher</span>
                    <span class="detail-value">{{ $book->publisher ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Publication Year</span>
                    <span class="detail-value">{{ $book->publication_year ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Shelf Location</span>
                    <span class="detail-value">{{ $book->shelf_location ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Collection</span>
                    <span class="detail-value">
                        @if($book->collection === 'Library of Congress')
                            Circulation (Library of Congress)
                        @elseif($book->collection === 'Filipiniana')
                            Filipiniana
                        @elseif($book->collection === 'Thesis Collection')
                            Research & Innovation (Thesis Collection)
                        @elseif($book->collection === 'Fictions')
                            Fictions
                        @elseif($book->collection === 'Special Collections')
                            Special Collections
                        @else
                            {{ $book->collection ?? 'N/A' }}
                        @endif
                    </span>
                </div>
                @if($book->collection === 'Library of Congress' || $book->collection === 'Filipiniana')
                <div class="detail-item">
                    <span class="detail-label">Classification</span>
                    <span class="detail-value">{{ $book->loc_full_name ?? $book->loc_number }}</span>
                </div>
                @endif
                <div class="detail-item">
                    <span class="detail-label">New Acquisition</span>
                    <span class="detail-value">{{ $book->is_new_acquisition ? 'Yes' : 'No' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        @if($book->status === 'available')
                            <span class="status-badge available">Available</span>
                        @elseif($book->status === 'borrowed')
                            <span class="status-badge borrowed">Borrowed</span>
                            @auth('faculty')
                                @php
                                    $alreadyNotified = $book->notifications()
                                        ->where('faculty_id', Auth::guard('faculty')->id())
                                        ->exists();
                                @endphp
                                @if(!$alreadyNotified)
                                    <button type="button" onclick="notifyBook('{{ $book->id }}')" class="notify-btn">Notify Me</button>
                                @else
                                    <span class="notify-note">Already on notification list</span>
                                @endif
                            @endauth
                        @elseif($book->status === 'archived')
                            <span class="status-badge archived">Archived</span>
                        @else
                            <span class="status-badge default">{{ ucfirst($book->status) }}</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Table of Contents (full width, below the columns) --}}
        <div class="toc-section">
            <label class="image-label">Table of Contents</label>
            @if($book->toc_image_path || $book->tocImages->count())
                <div class="toc-gallery">
                    @if($book->toc_image_path)
                    <a href="{{ asset('storage/' . $book->toc_image_path) }}" target="_blank" rel="noopener" class="image-wrapper">
                        <img src="{{ asset('storage/' . $book->toc_image_path) }}" alt="Table of Contents" class="book-image">
                    </a>
                    @endif
                    @foreach($book->tocImages as $tocImage)
                    <a href="{{ asset('storage/' . $tocImage->path) }}" target="_blank" rel="noopener" class="image-wrapper">
                        <img src="{{ asset('storage/' . $tocImage->path) }}" alt="Table of Contents page {{ $loop->iteration }}" class="book-image">
                    </a>
                    @endforeach
                </div>
            @else
                <div class="toc-empty">No table of contents available for this book.</div>
            @endif
        </div>
    </div>
</div>

<script>
function notifyBook(bookId) {
    fetch(`/faculty/notify/${bookId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        location.reload();
    })
    .catch(err => alert('Error'));
}
</script>
@endsection
