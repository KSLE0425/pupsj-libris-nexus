<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookUsage;
use App\Models\LibraryDamageReport;
use App\Services\AISuggestionService;
use App\Services\BorrowEligibilityService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BookNotification;
use App\Mail\BookAvailableMail;
use Illuminate\Support\Facades\Mail;


class StudentBorrowController extends Controller
{
    private function getLocClassifications()
    {
        return [
            'A' => 'A - General Works',
            'B' => 'B - Philosophy, Psychology, Religion',
            'C' => 'C - Auxiliary Sciences of History',
            'D' => 'D - World History and History of Europe, Asia, Africa',
            'E-F' => 'E-F - History of the Americas',
            'G' => 'G - Geography, Anthropology, Recreation',
            'H' => 'H - Social Sciences',
            'J' => 'J - Political Science',
            'K' => 'K - Law',
            'L' => 'L - Education',
            'M' => 'M - Music and Books on Music',
            'N' => 'N - Fine Arts',
            'P' => 'P - Language and Literature',
            'Q' => 'Q - Science',
            'R' => 'R - Medicine',
            'S' => 'S - Agriculture',
            'T' => 'T - Technology',
            'U' => 'U - Military Science',
            'V' => 'V - Naval Science',
            'Z' => 'Z - Bibliography, Library Science',
        ];
    }

    // 📖 Borrow page
public function showBorrow(Request $request)
{
    $query = Book::where('copies', '>', 0)
        ->where('status', 'available')
        ->where('is_condemned', false);

    // ─── SEARCH ───
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%");
        });
    }

    // ─── COLLECTION FILTER ───
    if ($request->filled('collection') && $request->collection !== 'all') {
        // Circulation encompasses books also stored under "Library of Congress"
        if ($request->collection === 'Circulation') {
            $query->whereIn('collection', ['Circulation', 'Library of Congress']);
        } else {
            $query->where('collection', $request->collection);
        }
    }

    // ─── NEW ACQUISITIONS TOGGLE (independent of collection) ───
    if ($request->boolean('new_acquisition')) {
        $query->where('is_new_acquisition', true);
    }

    // ─── LoC CLASSIFICATION FILTER ───
    if ($request->filled('loc_class') && in_array($request->collection, ['Circulation', 'Library of Congress', 'Filipiniana'])) {
        $class = $request->loc_class;
        if ($class === 'E-F') {
            $query->where(function ($q) {
                $q->where('loc_number', 'LIKE', 'E%')
                    ->orWhere('loc_number', 'LIKE', 'F%');
            });
        } else {
            $query->where('loc_number', 'LIKE', $class.'%');
        }
    }

    // ─── RESEARCH TYPE FILTER (Research & Innovation) ───
    if ($request->filled('research_type')) {
        $query->where('research_type', $request->research_type);
    }

    // ─── AUTHOR, YEAR, SUBJECT, PROGRAM FILTERS ───
    if ($request->filled('author')) {
        $query->where('author', 'like', '%' . $request->author . '%');
    }
    if ($request->filled('year')) {
        $query->where('publication_year', $request->year);
    }
    if ($request->filled('subject')) {
        $query->where('subject', 'like', '%' . $request->subject . '%');
    }
    if ($request->filled('program_id')) {
        $query->where('course_id', $request->program_id);
    }

    $books = $query->with('tocImages')->orderBy('title')->paginate(10);

    // For dropdown options – get distinct authors
    $authors = Book::where('status', 'available')->where('copies', '>', 0)
                ->whereNotNull('author')->distinct()->orderBy('author')->pluck('author');
    $years = Book::where('status', 'available')->where('copies', '>', 0)
                ->whereNotNull('publication_year')->distinct()->orderBy('publication_year', 'desc')->pluck('publication_year');
    $subjects = Book::where('status', 'available')->where('copies', '>', 0)
                ->whereNotNull('subject')->distinct()->orderBy('subject')->pluck('subject');

    $locClassifications = $this->getLocClassifications();

    $collectionTypes = \App\Models\CollectionType::whereNull('archived_at')->orderBy('name')->get();
    $programs        = \App\Models\Program::orderBy('name')->get();

    $myReservations = auth()->guard('student')->user()
        ->reservations()
        ->with('book')
        ->orderBy('created_at', 'desc')
        ->get();

    return view('student.borrow', compact('books', 'locClassifications', 'authors', 'years', 'subjects', 'myReservations', 'collectionTypes', 'programs'));
}

    // 📦 Get active borrow (AJAX)
    public function getActiveBorrow()
    {
        $student = Auth::guard('student')->user();

        $active = BookUsage::where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        return response()->json([
            'has_active' => $active ? true : false,
        ]);
    }

    public function getBook(Book $book)
    {
        // If the request expects JSON (e.g., from scanner), return JSON
        if (request()->wantsJson()) {
            return response()->json($book);
        }
        // Otherwise return the view
        $student = Auth::guard('student')->user();
        $activeBorrow = BookUsage::with('book')
    ->where('student_id', $student->id)
    ->where('status', 'active')
    ->get();

        // Eager-load TOC images so they're available in the view
        $book->load('tocImages');

        return view('student.book-details', compact('book', 'activeBorrow'));
    }
    
    // ✅ BORROW BOOK
    public function borrow(Request $request)
    {
        $student = Auth::guard('student')->user();

        $block = BorrowEligibilityService::studentBlockingReason($student);
        if ($block) {
            return response()->json(['message' => $block], 403);
        }

        $book = Book::find($request->book_id);
        if (! $book) {
            return response()->json(['message' => 'Book not found.'], 404);
        }

        $alreadyBorrowed = BookUsage::where('student_id', $student->id)
            ->where('book_id', $book->id)
            ->where('status', 'active')
            ->exists();

        if ($alreadyBorrowed) {
            return response()->json(['message' => 'Already borrowed.']);
        }

        if ($book->copies <= 0) {
            return response()->json(['message' => 'No available copies.']);
        }

        if ($book->is_condemned) {
            return response()->json(['message' => 'This copy is not available for borrowing.'], 400);
        }

        $usage = BookUsage::create([
            'student_id' => $student->id,
            'book_id' => $book->id,
            'time_in' => now(),
            'status' => 'active',
            'usage_context' => 'in_library',
        ]);

        BorrowEligibilityService::fulfillReservationForBorrow($book->id, $student->id, null);

        $book->decrement('copies');
        $book->update(['status' => $book->copies > 0 ? 'available' : 'borrowed']);

        AuditLogger::log('borrow_created', 'Student ' . $student->first_name . ' ' . $student->last_name . ' borrowed "' . $book->title . '"', [
            'usage_id'       => $usage->id,
            'affected_book'  => $book->title,
            'affected_user'  => $student->first_name . ' ' . $student->last_name,
            'student_id'     => $student->id,
            'student_number' => $student->student_number,
            'book_id'        => $book->id,
            'usage_context'  => 'in_library',
        ], 'circulation', 'student', $student->id);

        return response()->json(['message' => 'Book borrowed successfully!']);
    }
// In StudentBorrowController

public function notify(Request $request, Book $book)
{
    $student = Auth::guard('student')->user();

    if ($book->status !== 'borrowed') {
        return response()->json(['message' => 'This book is not currently borrowed.'], 400);
    }

    // Check if already notified
    $exists = BookNotification::where('book_id', $book->id)
                ->where('student_id', $student->id)
                ->exists();

    if ($exists) {
        return response()->json(['message' => 'You are already on the notification list for this book.']);
    }

    BookNotification::create([
        'book_id'    => $book->id,
        'student_id' => $student->id,
    ]);

    return response()->json(['message' => 'You will be notified when this book becomes available.']);
}
    // ✅ RETURN BOOK (FIXED!!!)
public function returnBook(Request $request, AISuggestionService $aiService)
{
    $request->validate([
        'book_id' => 'required',
        'report_damage' => 'sometimes|boolean',
        'damage_note' => 'required_if:report_damage,true|string|max:2000',
    ]);

    $student = Auth::guard('student')->user();

    $usage = BookUsage::where('student_id', $student->id)
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

    if ($request->boolean('report_damage') && $request->filled('damage_note')) {
        LibraryDamageReport::create([
            'book_usage_id' => $usage->id,
            'student_id' => $student->id,
            'faculty_id' => null,
            'book_id' => $usage->book_id,
            'patron_note' => $request->input('damage_note'),
            'status' => 'pending',
        ]);
    }

    $book = $usage->book;
    $book->increment('copies');
    $book->update(['status' => $book->copies > 0 ? 'available' : 'borrowed']);

    AuditLogger::log('borrow_returned', 'Student ' . $student->first_name . ' ' . $student->last_name . ' returned "' . $book->title . '"', [
        'usage_id'       => $usage->id,
        'affected_book'  => $book->title,
        'affected_user'  => $student->first_name . ' ' . $student->last_name,
        'student_id'     => $student->id,
        'student_number' => $student->student_number,
        'book_id'        => $book->id,
    ], 'circulation', 'student', $student->id);

        // (inside returnBook, after the book status update)

        // ── Notify users who want this book ──
    $notifications = BookNotification::where('book_id', $book->id)->get();

    foreach ($notifications as $notification) {
        if ($notification->notifier_email) {
            try {
                Mail::to($notification->notifier_email)
                    ->send(new BookAvailableMail($notification));
            } catch (\Exception $e) {
                \Log::error('Notify email failed: ' . $e->getMessage());
            }
        }
    }

    // Delete all notifications for this book (they've been notified)
    BookNotification::where('book_id', $book->id)->delete();

    // ✅ Try to get AI recommendations… (existing code follows)

    // ✅ Try to get AI recommendations, but don't fail if offline
    try {
        $recommendations = $aiService->getExternalRecommendations($student->id, $usage->book_id, 3);
    } catch (\Exception $e) {
        // AI service unavailable – return empty recommendations and log the error
        \Log::warning('AI recommendation service unavailable: ' . $e->getMessage());
        $recommendations = [];
    }

    return response()->json([
        'message' => 'Book returned successfully!',
        'recommendations' => $recommendations,
    ]);
}
    // 📜 HISTORY
    public function history(Request $request)
    {
        $student = Auth::guard('student')->user();

        $filters = $request->validate([
            'q'      => 'nullable|string|max:100',
            'status' => 'nullable|in:borrowed,returned,overdue',
            'from'   => 'nullable|date',
            'to'     => 'nullable|date|after_or_equal:from',
        ]);

        $history = BookUsage::with('book')
            ->where('student_id', $student->id)
            ->historyFilters($filters)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('student.history', compact('history', 'filters'));
    }

    public function getBookByHash($hash)
{
    $book = Book::where('qr_hash', $hash)->first();
    if (!$book) abort(404);
    return response()->json($book);
}
    public function getBookByBarcode($barcode)
    {
        $book = Book::where('barcode', $barcode)->first();
        if (! $book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        return response()->json($book);
    }
}
