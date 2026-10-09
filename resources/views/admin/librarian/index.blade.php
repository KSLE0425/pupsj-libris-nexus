@extends('layouts.admin')
@section('content')
<h2>Librarian Tools – Borrow on Behalf of Student</h2>
<form method="POST" action="{{ route('admin.librarian.select') }}">
    @csrf
    <div class="form-group">
        <label>Select Student</label>
        <select name="student_id" class="form-control" required>
            <option value="">-- Choose Student --</option>
            @foreach($students as $student)
            <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }} ({{ $student->student_number }})</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Start Borrowing Session</button>
</form>
@endsection