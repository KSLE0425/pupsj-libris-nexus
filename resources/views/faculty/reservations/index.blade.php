@extends('layouts.faculty')

@section('title', 'Reservations')

@section('content')
<div class="container" style="max-width:900px;">
    <div class="page-header mb-4">
        <h1 style="font-size:1.6rem;font-weight:800;color:var(--maroon);margin-bottom:4px;">Book Reservations</h1>
        <p class="text-muted mb-0">After reserving, you have <strong>5 minutes</strong> to arrive and pick up the book.</p>
    </div>

    @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger mb-3">{{ session('error') }}</div>@endif

    {{-- New Reservation Card --}}
    <div class="card p-4 mb-4" style="border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <h5 class="mb-3" style="color:var(--maroon);font-weight:700;display:flex;align-items:center;gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Make a Reservation
        </h5>
        <form method="POST" action="{{ route('faculty.reservations.store') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-8">
                <label class="form-label">Book ID</label>
                <input type="number" name="book_id" class="form-control" required min="1" placeholder="Enter book ID from book details page">
                <small class="form-text text-muted">Find the book ID from the book's detail page URL (e.g., /faculty/books/5 → ID is 5)</small>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100" style="background:var(--yellow);color:var(--maroon);font-weight:700;border:none;padding:12px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Reserve Book
                </button>
            </div>
        </form>
    </div>

    {{-- Reservations List Card --}}
    <div class="card p-4" style="border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <h5 class="mb-3" style="color:var(--maroon);font-weight:700;display:flex;align-items:center;gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            Your Reservations
        </h5>

        @if($reservations->isEmpty())
            <div class="text-center py-5">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" class="mb-3">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <p class="text-muted mb-0">No reservations yet. Browse books and reserve one when you find something you need!</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover" style="font-size:0.88rem;">
                    <thead>
                        <tr style="background:var(--maroon);color:white;">
                            <th style="border-radius:8px 0 0 0;padding:12px 16px;">Book</th>
                            <th style="padding:12px 16px;">Status</th>
                            <th style="padding:12px 16px;">Queue Position</th>
                            <th style="padding:12px 16px;">Expires</th>
                            <th style="border-radius:0 8px 0 0;padding:12px 16px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($reservations as $r)
                        <tr>
                            <td style="padding:12px 16px;vertical-align:middle;">
                                <strong>{{ $r->book->title ?? 'Book #'.$r->book_id }}</strong>
                                @if($r->book && $r->book->author)
                                    <br><small class="text-muted">{{ $r->book->author }}</small>
                                @endif
                            </td>
                            <td style="padding:12px 16px;vertical-align:middle;">
                                @php
                                    $badgeClass = match($r->status) {
                                        'pending' => 'bg-warning text-dark',
                                        'ready' => 'bg-success text-white',
                                        'expired' => 'bg-secondary text-white',
                                        default => 'bg-secondary text-white',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} rounded-pill" style="font-size:0.75rem;padding:6px 14px;">{{ ucfirst($r->status) }}</span>
                            </td>
                            <td style="padding:12px 16px;vertical-align:middle;">
                                <span class="badge" style="background:var(--maroon);color:white;font-size:0.75rem;min-width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:20px;padding:0 8px;">#{{ $r->position }}</span>
                            </td>
                            <td style="padding:12px 16px;vertical-align:middle;">
                                @if($r->status === 'pending' && $r->expires_at)
                                    <span class="countdown" data-expires="{{ $r->expires_at->toIso8601String() }}" id="cd-{{ $r->id }}"
                                          style="display:inline-flex;align-items:center;gap:4px;font-size:0.8rem;font-weight:700;color:#d97706;background:#fef3c7;padding:3px 10px;border-radius:20px;border:1px solid #f59e0b;">…</span>
                                @else
                                    <span class="text-muted" style="font-size:0.8rem;">{{ ucfirst($r->status) }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 16px;vertical-align:middle;">
                                @if($r->status === 'pending')
                                    <form method="POST" action="{{ route('faculty.reservations.destroy', $r) }}" onsubmit="return confirm('Cancel this reservation?');" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="background:#FEE2E2;color:#DC2626;font-weight:600;font-size:0.75rem;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;">
                                                <line x1="18" y1="6" x2="6" y2="18"/>
                                                <line x1="6" y1="6" x2="18" y2="18"/>
                                            </svg>
                                            Cancel
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted" style="font-size:0.75rem;">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($reservations->hasPages())
                <div class="mt-3">
                    {{ $reservations->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
function updateCountdowns() {
    document.querySelectorAll('.countdown[data-expires]').forEach(el => {
        const diff = Math.floor((new Date(el.dataset.expires) - Date.now()) / 1000);
        if (diff <= 0) {
            el.textContent = 'Expired';
            el.style.color = '#6b7280';
            el.style.background = '#f3f4f6';
            el.style.borderColor = '#d1d5db';
        } else {
            const m = Math.floor(diff / 60);
            const s = diff % 60;
            el.textContent = `⏱ ${m}:${String(s).padStart(2, '0')}`;
        }
    });
}
updateCountdowns();
setInterval(updateCountdowns, 1000);
</script>
@endpush
@endsection