{{-- resources/views/student/return.blade.php --}}
@extends('layouts.app')

@section('title', 'Return Books')

@section('content')
<div class="container">
    <h1>Return Books</h1>
    
    {{-- Currently Borrowed Books --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Books to Return</h5>
                </div>
                <div class="card-body">
                    @if(isset($borrowedBooks) && count($borrowedBooks) > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Book Title</th>
                                        <th>Author</th>
                                        <th>Borrowed Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Fine</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($borrowedBooks as $borrow)
                                        <tr>
                                            <td>{{ $borrow->book->title ?? 'N/A' }}</td>
                                            <td>{{ $borrow->book->author ?? 'N/A' }}</td>
                                            <td>{{ date('M d, Y', strtotime($borrow->borrowed_at)) }}</td>
                                            <td>
                                                {{ date('M d, Y', strtotime($borrow->due_date)) }}
                                                @if($borrow->due_date < now())
                                                    <span class="badge bg-danger">Overdue</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($borrow->due_date < now())
                                                    <span class="text-danger">Overdue</span>
                                                @else
                                                    <span class="text-success">On Time</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $fine = 0;
                                                    if($borrow->due_date < now()) {
                                                        $daysOverdue = now()->diffInDays($borrow->due_date);
                                                        $fine = $daysOverdue * 20; // Assuming ₱20/day fine
                                                    }
                                                @endphp
                                                ₱{{ number_format($fine, 2) }}
                                            </td>
                                            <td>
                                                <button class="btn btn-primary btn-sm return-book" 
                                                        data-borrow-id="{{ $borrow->id }}"
                                                        data-fine="{{ $fine }}">
                                                    Return Book
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            You have no books to return at the moment.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for Returning Books --}}
@push('scripts')
<script>
document.querySelectorAll('.return-book').forEach(button => {
    button.addEventListener('click', function() {
        const borrowId = this.dataset.borrowId;
        const fine = this.dataset.fine;
        
        let confirmMessage = 'Are you sure you want to return this book?';
        if(fine > 0) {
            confirmMessage += `\n\nYou have a fine of ₱${fine}. Do you want to proceed?`;
        }
        
        if(confirm(confirmMessage)) {
                        fetch('{{ route("student.return") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ book_id: bookId })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Book returned successfully!');
                    location.reload();
                } else {
                    alert(data.error || 'Failed to return book');
                }
            });
        }
    });
});
</script>
@endpush
@endsection