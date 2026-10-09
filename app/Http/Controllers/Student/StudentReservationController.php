<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentReservationController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $reservations = Reservation::with('book')
            ->where('student_id', $student->id)
            ->whereNull('faculty_id')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('student.reservations.index', compact('reservations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $student = Auth::guard('student')->user();
        $book = Book::findOrFail($data['book_id']);

        if ($book->trashed()) {
            return back()->with('error', 'This book cannot be reserved.');
        }

        $exists = Reservation::where('book_id', $book->id)
            ->where('status', 'pending')
            ->where('student_id', $student->id)
            ->whereNull('faculty_id')
            ->exists();

        if ($exists) {
            return back()->with('error', 'You already have a pending reservation for this book.');
        }

        $activeCount = Reservation::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'ready'])
            ->count();
        if ($activeCount >= 3) {
            return back()->with('error', 'You can only have 3 active reservations at a time.');
        }

        $position = (int) Reservation::where('book_id', $book->id)->where('status', 'pending')->max('position') + 1;

        Reservation::create([
            'book_id'    => $book->id,
            'student_id' => $student->id,
            'faculty_id' => null,
            'status'     => 'pending',
            'position'   => $position,
            'reserved_at' => now(),
            'expires_at'  => now()->addMinutes(5),
        ]);

        return back()->with('success', 'Reservation placed! You have 5 minutes to arrive and pick up the book.');
    }

    public function destroy(Reservation $reservation)
    {
        $student = Auth::guard('student')->user();
        if ($reservation->student_id !== $student->id || $reservation->status !== 'pending') {
            abort(403);
        }
        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Reservation cancelled.');
    }
}
