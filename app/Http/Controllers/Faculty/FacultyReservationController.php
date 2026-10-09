<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacultyReservationController extends Controller
{
    public function index()
    {
        $faculty = Auth::guard('faculty')->user();
        $reservations = Reservation::with('book')
            ->where('faculty_id', $faculty->id)
            ->whereNull('student_id')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('faculty.reservations.index', compact('reservations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $faculty = Auth::guard('faculty')->user();
        $book = Book::findOrFail($data['book_id']);

        if ($book->trashed()) {
            return back()->with('error', 'This book cannot be reserved.');
        }

        $exists = Reservation::where('book_id', $book->id)
            ->where('status', 'pending')
            ->where('faculty_id', $faculty->id)
            ->whereNull('student_id')
            ->exists();

        if ($exists) {
            return back()->with('error', 'You already have a pending reservation for this book.');
        }

        $activeCount = Reservation::where('faculty_id', $faculty->id)
            ->whereIn('status', ['pending', 'ready'])
            ->count();
        if ($activeCount >= 3) {
            return back()->with('error', 'You can only have 3 active reservations at a time.');
        }

        $position = (int) Reservation::where('book_id', $book->id)->where('status', 'pending')->max('position') + 1;

        Reservation::create([
            'book_id'    => $book->id,
            'student_id' => null,
            'faculty_id' => $faculty->id,
            'status'     => 'pending',
            'position'   => $position,
            'reserved_at' => now(),
            'expires_at'  => now()->addMinutes(5),
        ]);

        return back()->with('success', 'Reservation placed! You have 5 minutes to arrive and pick up the book.');
    }

    public function destroy(Reservation $reservation)
    {
        $faculty = Auth::guard('faculty')->user();
        if ($reservation->faculty_id !== $faculty->id || $reservation->status !== 'pending') {
            abort(403);
        }
        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Reservation cancelled.');
    }
}
