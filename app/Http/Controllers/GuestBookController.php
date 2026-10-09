<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\CollectionType;
use App\Models\Program;
use Illuminate\Http\Request;

class GuestBookController extends Controller
{
    // ADD THIS METHOD
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

public function index(Request $request)
{
    $query = Book::whereNotIn('status', ['archived']);

    // Existing search filter
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%");
        });
    }

    // Collection filter
    if ($request->filled('collection') && $request->collection !== 'all') {
        if ($request->collection === 'new_acquisitions') {
            $query->where('is_new_acquisition', true);
        } else {
            $query->where('collection', $request->collection);
        }
    }

    // LoC / Filipiniana classification filter
    if ($request->filled('loc_class') && in_array($request->collection, ['Library of Congress', 'Filipiniana'])) {
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

    // ─── NEW FILTERS (Author, Year, Subject) ───
    if ($request->filled('author')) {
        $query->where('author', 'like', '%' . $request->author . '%');
    }
    if ($request->filled('year')) {
        $query->where('publication_year', $request->year);
    }
    if ($request->filled('subject')) {
        $query->where('subject', 'like', '%' . $request->subject . '%');
    }

    $books = $query
        ->withCount(['reservations as pending_reservations_count' => fn($q) => $q->where('status', 'pending')])
        ->orderBy('title')->paginate(12);

    // For dropdown options
    $authors = Book::whereNotIn('status', ['archived'])
                ->whereNotNull('author')->distinct()->orderBy('author')->pluck('author');
    $years = Book::whereNotIn('status', ['archived'])
                ->whereNotNull('publication_year')->distinct()->orderBy('publication_year', 'desc')->pluck('publication_year');
    $subjects = Book::whereNotIn('status', ['archived'])
                ->whereNotNull('subject')->distinct()->orderBy('subject')->pluck('subject');

    $locClassifications = $this->getLocClassifications();
    $collectionTypes = CollectionType::whereNull('archived_at')->orderBy('name')->get();

    $allBooks = Book::whereNotIn('status', ['archived'])
        ->select('id', 'title', 'author', 'isbn', 'collection', 'is_new_acquisition',
                 'loc_number', 'publication_year', 'subject', 'publisher',
                 'research_type', 'course_id', 'title_cover_image_path', 'toc_image_path')
        ->get();
    $programs = Program::orderBy('name')->get();
    return view('guest.books', compact('books', 'locClassifications', 'authors', 'years', 'subjects', 'allBooks', 'collectionTypes', 'programs'));

}

    public function show(Book $book)
    {
        if ($book->status === 'archived') {
            abort(404);
        }

        // Eager-load TOC images and reservation count
        $book->load('tocImages');
        $book->loadCount(['reservations as pending_reservations_count' => fn($q) => $q->where('status', 'pending')]);

        return view('guest.book-details', compact('book'));
    }
}
