@extends('layouts.admin')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@section('content')
<h2>Archived Books</h2>
<p class="subtitle">Soft-deleted books. Restore to make them active again.</p>

@if(session('message'))
<div class="alert alert-success">{{ session('message') }}</div>
@endif

<div class="search-box">
    <input type="text" id="liveSearch" placeholder="Search title, author, ISBN..." autocomplete="off" />
    <span class="search-hint">Updates as you type</span>
</div>

<p class="toolbar">
    <a href="/books" class="btn btn-outline">← Back to Books</a>
</p>

<table class="data-table" id="booksTable">
    <thead>
        <tr>
            <th>Accession No.</th>
            <th>Title</th>
            <th>Author</th>
            <th>ISBN</th>
            <th>Copies</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($books as $book)
        <tr data-search="{{ strtolower($book->accession_number . ' ' . $book->id . ' ' . $book->title . ' ' . $book->author . ' ' . ($book->isbn ?? '')) }}">
            <td><strong>{{ $book->accession_number ?? '—' }}</strong></td>
            <td>{{ $book->title }}</td>
            <td>{{ $book->author }}</td>
            <td>{{ $book->isbn ?? '—' }}</td>
            <td>{{ $book->copies }}</td>
            <td class="actions">
                <form method="POST" action="/books/restore/{{ $book->id }}" class="form-inline">
                    @csrf
                    <button type="submit" class="btn btn-restore">Restore</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="empty">No archived books.</td></tr>
        @endforelse
    </tbody>
</table>

<style>
.subtitle { color:#666; margin-top:-8px; margin-bottom:15px; }
.alert-success { background:#d4edda; color:#155724; padding:10px 15px; border-radius:8px; margin-bottom:15px; }
.search-box { margin-bottom:20px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.search-box input { padding:10px 14px; width:280px; max-width:100%; border:1px solid #ccc; border-radius:8px; font-size:1rem; }
.search-box input:focus { outline:none; border-color:#800000; }
.search-hint { color:#666; font-size:0.9rem; }
.toolbar { margin-bottom:20px; }
.btn { display:inline-block; padding:8px 16px; border-radius:8px; text-decoration:none; font-size:0.95rem; cursor:pointer; border:none; font-family:inherit; }
.btn-outline { background:transparent; color:#800000; border:2px solid #800000; }
.btn-outline:hover { background:#800000; color:white; }
.btn-restore { background:#16a34a; color:white; }
.btn-restore:hover { background:#15803d; }
.data-table { width:100%; background:white; border-collapse:collapse; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.08); }
.data-table th { background:#800000; color:white; padding:12px 14px; text-align:left; font-weight:600; }
.data-table td { padding:12px 14px; border-bottom:1px solid #eee; }
.data-table tbody tr:hover { background:#fafafa; }
.data-table .empty { color:#666; font-style:italic; }
.data-table tr.hidden { display:none; }
</style>

<script>
(function(){
    var input = document.getElementById('liveSearch');
    var table = document.getElementById('booksTable');
    if (!input || !table) return;
    var tbody = table.tBodies[0];
    if (!tbody) return;
    var rows = tbody.querySelectorAll('tr[data-search]');
    input.addEventListener('input', function(){
        var q = (this.value || '').trim().toLowerCase();
        rows.forEach(function(row){
            var text = (row.getAttribute('data-search') || '');
            row.classList.toggle('hidden', q && text.indexOf(q) === -1);
        });
    });
})();
</script>
@endsection