@extends('layouts.admin')
@section('content')
<h2>Borrowing for: {{ $student->first_name }} {{ $student->last_name }}</h2>
<div class="row">
    @foreach($books as $book)
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5>{{ $book->title }}</h5>
                <p>{{ $book->author }}</p>
                <button class="btn btn-primary borrow-btn" data-id="{{ $book->id }}">Borrow</button>
            </div>
        </div>
    </div>
    @endforeach
</div>
{{ $books->links() }}

<script>
document.querySelectorAll('.borrow-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let bookId = this.dataset.id;
        fetch('{{ route("admin.librarian.borrow.post") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({book_id: bookId})
        }).then(r => r.json()).then(data => alert(data.message)).then(() => location.reload());
    });
});
</script>
@endsection