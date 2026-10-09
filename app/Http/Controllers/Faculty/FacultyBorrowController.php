<?php
namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookUsage;
use App\Models\CollectionType;
use App\Models\LibraryDamageReport;
use App\Models\Specialty;
use App\Services\BorrowEligibilityService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BookNotification;
use App\Mail\BookAvailableMail;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class FacultyBorrowController extends Controller
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

public function history(Request $request)
{
    $faculty = Auth::guard('faculty')->user();
    $filters = $request->validate([
        'q'      => 'nullable|string|max:100',
        'status' => 'nullable|in:borrowed,returned,overdue',
        'from'   => 'nullable|date',
        'to'     => 'nullable|date|after_or_equal:from',
    ]);

    $history = BookUsage::with('book')
        ->where('faculty_id', $faculty->id)
        ->historyFilters($filters)
        ->orderByDesc('created_at')
        ->paginate(10)
        ->withQueryString();
    return view('faculty.history', compact('history', 'filters'));
}

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
    if ($request->filled('subject')) {
        $query->where('subject', 'like', '%' . $request->subject . '%');
    }
    if ($request->filled('department_id')) {
        $query->where('specialty_id', $request->department_id);
    }

    $books = $query->with('tocImages')->orderBy('title')->paginate(10);

    // Get dropdown options
    $authors = Book::where('status', 'available')->where('copies', '>', 0)->whereNotNull('author')->distinct()->orderBy('author')->pluck('author');
    $subjects = Book::where('status', 'available')->where('copies', '>', 0)->whereNotNull('subject')->distinct()->orderBy('subject')->pluck('subject');
    $locClassifications = $this->getLocClassifications();
    $collectionTypes = CollectionType::whereNull('archived_at')->orderBy('name')->get();
    $specialties = Specialty::orderBy('name')->get();

    $faculty = Auth::guard('faculty')->user();
    $myReservations = $faculty->reservations()->with('book')->orderBy('created_at', 'desc')->get();

    return view('faculty.borrow', compact('books', 'authors', 'subjects', 'locClassifications', 'collectionTypes', 'specialties', 'myReservations'));
}
    public function borrow(Request $request)
    {
        $faculty = Auth::guard('faculty')->user();

        $block = BorrowEligibilityService::facultyBlockingReason($faculty);
        if ($block) {
            return response()->json(['message' => $block], 403);
        }

        $book = Book::findOrFail($request->book_id);

        $alreadyBorrowed = BookUsage::where('faculty_id', $faculty->id)
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
            'faculty_id' => $faculty->id,
            'book_id'    => $book->id,
            'time_in'    => now(),
            'status'     => 'active',
            'usage_context' => 'off_site',
        ]);

        BorrowEligibilityService::fulfillReservationForBorrow($book->id, null, $faculty->id);

        $book->decrement('copies');
        $book->update(['status' => $book->copies > 0 ? 'available' : 'borrowed']);

        AuditLogger::log('borrow_created', 'Faculty member ' . $faculty->first_name . ' ' . $faculty->last_name . ' borrowed "' . $book->title . '"', [
            'usage_id'       => $usage->id,
            'affected_book'  => $book->title,
            'affected_user'  => $faculty->first_name . ' ' . $faculty->last_name,
            'faculty_id'     => $faculty->id,
            'employee_id'    => $faculty->employee_id,
            'book_id'        => $book->id,
            'usage_context'  => 'off_site',
        ], 'circulation', 'faculty', $faculty->id);

        return response()->json(['message' => 'Book borrowed successfully!']);
    }

    public function returnBook(Request $request)
    {
        $request->validate([
            'book_id' => 'required',
            'report_damage' => 'sometimes|boolean',
            'damage_note' => 'required_if:report_damage,true|string|max:2000',
        ]);

        $faculty = Auth::guard('faculty')->user();
        $usage = BookUsage::where('faculty_id', $faculty->id)
            ->where('book_id', $request->book_id)
            ->where('status', 'active')
            ->first();

        if (!$usage) return response()->json(['message' => 'No active borrow found.'], 404);

        $usage->update(['status' => 'completed', 'time_out' => now()]);

        if ($request->boolean('report_damage') && $request->filled('damage_note')) {
            LibraryDamageReport::create([
                'book_usage_id' => $usage->id,
                'student_id' => null,
                'faculty_id' => $faculty->id,
                'book_id' => $usage->book_id,
                'patron_note' => $request->input('damage_note'),
                'status' => 'pending',
            ]);
        }

        $message = 'Book returned.';

        $book = $usage->book;
        $book->increment('copies');
        $book->update(['status' => $book->copies > 0 ? 'available' : 'borrowed']);

        AuditLogger::log('borrow_returned', 'Faculty member ' . $faculty->first_name . ' ' . $faculty->last_name . ' returned "' . $book->title . '"', [
            'usage_id'       => $usage->id,
            'affected_book'  => $book->title,
            'affected_user'  => $faculty->first_name . ' ' . $faculty->last_name,
            'faculty_id'     => $faculty->id,
            'employee_id'    => $faculty->employee_id,
            'book_id'        => $book->id,
        ], 'circulation', 'faculty', $faculty->id);

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
        BookNotification::where('book_id', $book->id)->delete();

        return response()->json(['message' => $message]);
    }

    public function getActiveBorrows()
    {
        $faculty = Auth::guard('faculty')->user();
        $active = BookUsage::with('book')
            ->where('faculty_id', $faculty->id)
            ->where('status', 'active')
            ->get();
        return response()->json($active);
    }

    public function getBook(Book $book)
{
    if (request()->wantsJson()) {
        return response()->json($book);
    }
    // Eager-load TOC images so they're available in the view
    $book->load('tocImages');
    return view('faculty.book-details', compact('book'));
}

public function extendBorrow(Request $request)
{
    $request->validate(['book_id' => 'required|integer|exists:books,id']);

    $faculty = Auth::guard('faculty')->user();
    $usage = BookUsage::where('faculty_id', $faculty->id)
        ->where('book_id', $request->book_id)
        ->where('status', 'active')
        ->first();

    if (!$usage) {
        return response()->json(['message' => 'No active borrow found for this book.'], 404);
    }

    if ($usage->extension_count >= 1) {
        return response()->json(['message' => 'Extension limit reached. You may only extend once per borrow.'], 422);
    }

    $usage->time_in = now();
    $usage->is_overdue_flagged = false;
    $usage->extension_count = 1;
    $maxDays = (int) Setting::getValue('max_borrow_days_student', 7);
    $usage->due_date = now()->addDays($maxDays)->toDateString();
    $usage->save();

    $newDueDate = now()->addDays($maxDays)->format('M d, Y');

    AuditLogger::log('borrow_renewed', 'Faculty member ' . $faculty->first_name . ' ' . $faculty->last_name . ' renewed borrow for "' . ($usage->book->title ?? 'Book #' . $usage->book_id) . '" until ' . $newDueDate, [
        'usage_id'       => $usage->id,
        'affected_book'  => $usage->book->title ?? 'Book #' . $usage->book_id,
        'affected_user'  => $faculty->first_name . ' ' . $faculty->last_name,
        'faculty_id'     => $faculty->id,
        'employee_id'    => $faculty->employee_id,
        'new_due_date'   => $newDueDate,
    ], 'circulation', 'faculty', $faculty->id);

    return response()->json([
        'message'      => 'Borrow period extended successfully.',
        'new_due_date' => $newDueDate,
    ]);
}

public function notify(Request $request, Book $book)
{
    $faculty = Auth::guard('faculty')->user();

    if ($book->status !== 'borrowed') {
        return response()->json(['message' => 'This book is not currently borrowed.'], 400);
    }

    // Check if already notified
    $exists = BookNotification::where('book_id', $book->id)
                ->where('faculty_id', $faculty->id)
                ->exists();

    if ($exists) {
        return response()->json(['message' => 'You are already on the notification list for this book.']);
    }

    BookNotification::create([
        'book_id'    => $book->id,
        'faculty_id' => $faculty->id,
    ]);

    return response()->json(['message' => 'You will be notified when this book becomes available.']);
}
}