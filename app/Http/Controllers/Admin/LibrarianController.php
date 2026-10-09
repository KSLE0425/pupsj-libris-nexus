<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookUsage;
use App\Models\Student;
use Illuminate\Http\Request;

class LibrarianController extends Controller
{
    public function index()
    {
        $students = Student::orderBy('last_name')->get();

        return view('admin.librarian.index', compact('students'));
    }

    public function borrowAsStudent(Request $request)
    {
        $studentId = $request->student_id;
        $student = Student::findOrFail($studentId);

        // Store selected student in session
        session(['librarian_student_id' => $studentId]);

        return redirect()->route('admin.librarian.borrow');
    }

    public function borrowView()
    {
        $studentId = session('librarian_student_id');
        if (! $studentId) {
            return redirect()->route('admin.librarian.index')->with('error', 'Select a student first.');
        }

        // Reuse student borrow logic but with admin layout
        $student = Student::findOrFail($studentId);
        $query = Book::where('status', 'available')->where('copies', '>', 0)->where('status', '!=', 'archived');
        $books = $query->orderBy('title')->paginate(10);

        return view('admin.librarian.borrow', compact('books', 'student'));
    }

    public function borrow(Request $request)
    {
        $studentId = session('librarian_student_id');
        if (! $studentId) {
            return response()->json(['message' => 'No student selected.'], 400);
        }

        $hasActive = BookUsage::where('student_id', $studentId)->where('status', 'active')->exists();
        if ($hasActive) {
            return response()->json(['message' => 'Student already has a borrowed book.']);
        }

        $book = Book::find($request->book_id);
        if (! $book || $book->copies <= 0) {
            return response()->json(['message' => 'No available copies.']);
        }

        BookUsage::create([
            'student_id' => $studentId,
            'book_id' => $book->id,
            'time_in' => now(),
            'status' => 'active',
        ]);

        $book->decrement('copies');

        return response()->json(['message' => 'Book borrowed successfully!']);
    }

    public function return(Request $request)
    {
        $studentId = session('librarian_student_id');
        if (! $studentId) {
            return response()->json(['message' => 'No student selected.'], 400);
        }

        $usage = BookUsage::where('student_id', $studentId)
            ->where('book_id', $request->book_id)
            ->where('status', 'active')
            ->first();

        if (! $usage) {
            return response()->json(['message' => 'No active borrow found.'], 404);
        }

        $usage->update([
            'status' => 'completed',
            'time_out' => now(),
        ]);

        $usage->book->increment('copies');

        return response()->json(['message' => 'Book returned successfully!']);
    }

    public function scannerView()
    {
        $studentId = session('librarian_student_id');
        if (! $studentId) {
            return redirect()->route('admin.librarian.index')->with('error', 'Select a student first.');
        }

        return view('admin.librarian.scanner', ['studentId' => $studentId]);
    }
}
