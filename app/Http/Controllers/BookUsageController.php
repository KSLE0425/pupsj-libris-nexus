<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookUsage;
use App\Models\Reservation;
use App\Models\Student;
use Illuminate\Http\Request;

class BookUsageController extends Controller
{
    // Borrowing history with relations
    public function index()
    {
        $usages = BookUsage::with(['student', 'book'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($usages);
    }

    // Student starts using a book
    public function start(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'book_id' => 'required|exists:books,id',
        ]);

        // Get the book
        $book = Book::findOrFail($validated['book_id']);

        // ❗ Prevent borrowing deleted books
        if ($book->trashed()) {
            return response()->json([
                'message' => 'This book is no longer available.',
            ], 400);
        }
        // Prevent multiple active usage for same student
        $activeUsage = BookUsage::where('student_id', $validated['student_id'])
            ->where('status', 'active')
            ->first();

        if ($activeUsage) {
            return response()->json([
                'message' => 'You must return your current book before borrowing another.',
            ], 400);
        }

        // if ($activeUsage) {
        //     return response()->json([
        //         'message' => 'Student already borrowed a book. Return it first.'
        //     ], 400);
        // }

        // if ($activeUsage) {
        //     return response()->json([
        //         'message' => 'Student already has an active book usage.'
        //     ], 400);
        // }

        // Check book availability
        $book = Book::find($validated['book_id']);

        if ($book->copies <= 0) {
            return response()->json([
                'message' => 'No available copies for this book.',
            ], 400);
        }

        // Decrease available copies
        $book->decrement('copies');
        if ($book->copies <= 0) {
            $book->status = 'unavailable';
            $book->save();
        }

        $usage = BookUsage::create([
            'student_id' => $validated['student_id'],
            'book_id' => $validated['book_id'],
            'time_in' => now(),
            'status' => 'active',
        ]);

        // If there is a pending reservation for this student/book, mark it completed
        Reservation::where('student_id', $validated['student_id'])
            ->where('book_id', $validated['book_id'])
            ->where('status', 'pending')
            ->update([
                'status' => 'completed',
                'fulfilled_at' => now(),
            ]);

        ActivityLog::create([
            'action' => 'borrow_start',
            'description' => 'Student started borrowing a book.',
            'performed_by_type' => 'student',
            'performed_by_id' => $validated['student_id'],
            'metadata' => [
                'book_id' => $validated['book_id'],
                'usage_id' => $usage->id,
            ],
        ]);

        return response()->json([
            'message' => 'Book usage started.',
            'data' => $usage,
        ], 201);
    }

    // Student returns book
    public function returnBook(Request $request)
    {

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'book_id' => 'required|exists:books,id',
        ]);

        $usage = BookUsage::where('student_id', $validated['student_id'])
            ->where('book_id', $validated['book_id'])
            ->where('status', 'active')
            ->first();

        if (! $usage) {
            return response()->json([
                'message' => 'No active borrow found',
            ], 400);
        }

        $usage->update([
            'time_out' => now(),
            'status' => 'completed',
        ]);

        $book = Book::find($validated['book_id']);

        $book->increment('copies');

        if ($book->status == 'unavailable') {
            $book->status = 'available';
            $book->save();
        }

        return response()->json([
            'message' => 'Book returned successfully',
        ]);

    }

    // Student finishes using book
    public function end($id)
    {
        $usage = BookUsage::findOrFail($id);

        if ($usage->status === 'completed') {
            return response()->json([
                'message' => 'Usage already completed.',
            ], 400);
        }

        $usage->update([
            'time_out' => now(),
            'status' => 'completed',
        ]);

        // Return book copy
        $book = $usage->book;
        $book->increment('copies');
        if ($book->copies > 0 && $book->status === 'unavailable') {
            $book->status = 'available';
            $book->save();
        }

        ActivityLog::create([
            'action' => 'borrow_end',
            'description' => 'Student returned a book.',
            'performed_by_type' => 'student',
            'performed_by_id' => $usage->student_id,
            'metadata' => [
                'book_id' => $usage->book_id,
                'usage_id' => $usage->id,
            ],
        ]);

        return response()->json([
            'message' => 'Book usage completed.',
            'data' => $usage,
        ]);
    }
}
