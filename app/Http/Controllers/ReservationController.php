<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with('book')->orderByDesc('reserved_at');

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->filled('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
            'student_id' => 'nullable|exists:students,id',
            'faculty_id' => 'nullable|exists:faculties,id',
        ]);

        if (empty($data['student_id']) && empty($data['faculty_id'])) {
            return response()->json(['message' => 'student_id or faculty_id is required.'], 422);
        }

        $book = Book::findOrFail($data['book_id']);
        if ($book->trashed()) {
            return response()->json(['message' => 'Book is not available for reservation.'], 400);
        }

        $existing = Reservation::where('book_id', $data['book_id'])
            ->where('status', 'pending')
            ->where(function ($q) use ($data) {
                if (! empty($data['student_id'])) {
                    $q->where('student_id', $data['student_id']);
                }
                if (! empty($data['faculty_id'])) {
                    $q->where('faculty_id', $data['faculty_id']);
                }
            })
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'You already have a pending reservation for this book.'], 422);
        }

        $position = (int) Reservation::where('book_id', $data['book_id'])->where('status', 'pending')->max('position') + 1;
        $days = (int) config('library_fees.reservation_default_expiry_days', 7);

        $reservation = Reservation::create([
            'book_id' => $data['book_id'],
            'student_id' => $data['student_id'] ?? null,
            'faculty_id' => $data['faculty_id'] ?? null,
            'status' => 'pending',
            'position' => $position,
            'reserved_at' => now(),
            'expires_at' => now()->addDays($days),
        ]);

        return response()->json($reservation->load('book'), 201);
    }

    public function cancel(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return response()->json(['message' => 'Only pending reservations can be cancelled.'], 422);
        }
        $reservation->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Reservation cancelled.']);
    }
}
