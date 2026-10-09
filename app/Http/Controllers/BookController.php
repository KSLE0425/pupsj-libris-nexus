<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\Http;
use App\Repositories\BookRepository;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Services\AuditLogger;

class BookController extends Controller
{
    protected $bookRepo;

    public function __construct(BookRepository $bookRepo)
    {
        $this->bookRepo = $bookRepo;
    }

    // ==============================
    // LIST BOOKS (API)
    // ==============================
    public function index()
    {
        $books = $this->bookRepo->getAll();
        $base = url('/storage');

        foreach ($books as $book) {
            $book->title_cover_url = $book->title_cover_image_path ? $base.'/'.$book->title_cover_image_path : null;
            $book->toc_url = $book->toc_image_path ? $base.'/'.$book->toc_image_path : null;
        }

        return response()->json($books);
    }

    // ==============================
    // SEARCH BOOKS
    // ==============================
    public function search(Request $request)
    {
        $query = Book::query();

        if ($request->title) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->author) {
            $query->where('author', 'like', '%' . $request->author . '%');
        }
        if ($request->subject) {
            $query->where('subject', 'like', '%' . $request->subject . '%');
        }
        if ($request->isbn) {
            $query->where('isbn', 'like', '%' . $request->isbn . '%');
        }
        if ($request->accession_number) {
            $query->where('accession_number', 'like', '%' . $request->accession_number . '%');
        }
        if ($request->publication_year) {
            $query->where('publication_year', $request->publication_year);
        }
        if ($request->availability) {
            if ($request->availability == "available") {
                $query->where('copies', '>', 0);
            } else {
                $query->where('copies', '<=', 0);
            }
        }

        return response()->json($query->get());
    }

    // ==============================
    // DELETED BOOKS (SOFT-DELETED)
    // ==============================
    public function deleted()
    {
        return response()->json(Book::onlyTrashed()->get());
    }

    // ==============================
    // STORE BOOK
    // ==============================
    public function store(Request $request)
    {
        $book = $this->bookRepo->create($request);

        // Auto-detect edition: if a book with the same title already exists, assign editions
        $this->autoAssignEdition($book);

        AuditLogger::log('book_created', 'Added new book "' . $book->title . '" by ' . ($book->author ?? 'Unknown'), [
            'affected_book'    => $book->title,
            'book_id'          => $book->id,
            'accession_number' => $book->accession_number,
            'isbn'             => $book->isbn,
            'call_number'      => $book->loc_number,
            'barcode'          => $book->barcode,
            'author'           => $book->author,
        ], 'books');

        return redirect()->route('admin.books')->with('message', 'Book added successfully');
    }

    private function autoAssignEdition(Book $book): void
    {
        if ($book->edition) {
            return; // Edition already set manually — leave it alone
        }

        // Find books with the same title (case-insensitive, trimmed)
        $similar = Book::whereRaw('LOWER(TRIM(title)) = ?', [strtolower(trim($book->title))])
            ->where('id', '!=', $book->id)
            ->whereNull('deleted_at')
            ->get();

        if ($similar->isEmpty()) {
            return; // No similar books — it's the only one, no edition needed
        }

        // Parse edition numbers from existing books
        $existingNums = $similar->pluck('edition')
            ->filter()
            ->map(fn($e) => (int) $e)
            ->filter(fn($n) => $n > 0);

        if ($existingNums->isEmpty()) {
            // Existing books have no edition — mark them as 1st, new book as 2nd
            Book::whereIn('id', $similar->pluck('id'))
                ->whereNull('edition')
                ->update(['edition' => '1st']);
            $book->update(['edition' => '2nd']);
        } else {
            $next = $existingNums->max() + 1;
            $book->update(['edition' => $next . $this->ordinalSuffix($next)]);
        }
    }

    private function ordinalSuffix(int $n): string
    {
        if (in_array($n % 100, [11, 12, 13])) return 'th';
        return match ($n % 10) {
            1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th',
        };
    }

    // ==============================
    // SHOW ONE BOOK
    // ==============================
    public function show(Book $book)
    {
        $base = url('/storage');
        $book->title_cover_url = $book->title_cover_image_path ? $base . '/' . $book->title_cover_image_path : null;
        $book->toc_url = $book->toc_image_path ? $base . '/' . $book->toc_image_path : null;
        return response()->json($book);
    }

    // ==============================
    // UPDATE BOOK
    // ==============================
    public function update(Request $request, Book $book)
    {
        $this->bookRepo->update($book, $request);

        AuditLogger::log('book_updated', 'Updated book details for "' . $book->title . '"', [
            'affected_book'    => $book->title,
            'book_id'          => $book->id,
            'accession_number' => $book->accession_number,
            'isbn'             => $book->isbn,
            'barcode'          => $book->barcode,
        ], 'books');

        return redirect()->route('admin.books')->with('message', 'Book updated successfully');
    }

    // ==============================
    // DELETE BOOK (SOFT DELETE)
    // ==============================
    public function destroy(Book $book)
    {
        $title = $book->title;
        $id = $book->id;
        $book->delete();

        AuditLogger::log('book_deleted', 'Deleted book "' . $title . '"', [
            'affected_book' => $title,
            'book_id'       => $id,
        ], 'books');

        return redirect()->route('admin.books')->with('message', 'Book removed');
    }

    // ==============================
    // ARCHIVE BOOK
    // ==============================
    public function archive(Book $book, Request $request)
    {
        if ($book->status === 'archived') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Book already archived'], 400);
            }
            return redirect()->route('admin.books')->with('message', 'Book already archived');
        }

        $this->bookRepo->archive($book);

        AuditLogger::log('book_archived', 'Archived book "' . $book->title . '"', [
            'affected_book' => $book->title,
            'book_id'       => $book->id,
        ], 'books');

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Book archived successfully']);
        }
        return redirect()->route('admin.books')->with('message', 'Book archived successfully');
    }

    // ==============================
    // UNARCHIVE BOOK
    // ==============================
    public function unarchive(Book $book, Request $request)
    {
        if ($book->status !== 'archived') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Book is not archived'], 400);
            }
            return redirect()->route('admin.books')->with('message', 'Book is not archived');
        }

        $this->bookRepo->unarchive($book);

        AuditLogger::log('book_unarchived', 'Restored book from archive: "' . $book->title . '"', [
            'affected_book' => $book->title,
            'book_id'       => $book->id,
        ], 'books');

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Book unarchived successfully']);
        }
        return redirect()->route('admin.books')->with('message', 'Book unarchived successfully');
    }

    // ==============================
    // CONDEMN BOOK
    // ==============================
    public function condemn(Book $book, Request $request)
    {
        if ($book->status === 'condemned') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Book already condemned'], 400);
            }
            return redirect()->route('admin.books')->with('message', 'Book already condemned');
        }

        $request->validate([
            'reason'      => 'required|string|min:10',
            'proof_image' => 'nullable|image|max:4096',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $proofPath = $request->file('proof_image')->store('damage-proofs', 'public');
        }

        $this->bookRepo->condemn($book, $request->reason, $proofPath);

        AuditLogger::log('book_condemned', 'Condemned book "' . $book->title . '": ' . $request->reason, [
            'affected_book' => $book->title,
            'book_id'       => $book->id,
            'reason'        => $request->reason,
            'proof_path'    => $proofPath,
        ], 'books');

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Book condemned successfully']);
        }
        return redirect()->route('admin.books')->with('message', 'Book condemned successfully');
    }

    // ==============================
    // UNCONDEMN BOOK
    // ==============================
    public function uncondemn(Book $book, Request $request)
    {
        if ($book->status !== 'condemned') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Book is not condemned'], 400);
            }
            return redirect()->route('admin.books')->with('message', 'Book is not condemned');
        }

        $this->bookRepo->uncondemn($book);

        AuditLogger::log('book_uncondemned', 'Restored condemned book: "' . $book->title . '"', [
            'affected_book' => $book->title,
            'book_id'       => $book->id,
        ], 'books');

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Book uncondemned successfully']);
        }
        return redirect()->route('admin.books')->with('message', 'Book uncondemned successfully');
    }

    // ==============================
    // RESTORE BOOK (FROM SOFT DELETE)
    // ==============================
    public function restore($id)
    {
        $book = Book::withTrashed()->findOrFail($id);
        $book->restore();
        return response()->json(['message' => 'Book restored']);
    }

    // ==============================
    // PERMANENTLY DELETE BOOK
    // ==============================
    public function forceDelete($id)
    {
        $book = Book::withTrashed()->findOrFail($id);
        $book->forceDelete();
        return response()->json(['message' => 'Book permanently deleted']);
    }

    // ==============================
    // FETCH BOOK INFO BY ISBN (Google Books API)
    // ==============================
    // ==============================
    // FETCH BOOK INFO BY CALLING NUMBER (Open Library)
    // ==============================
    public function fetchByCallNumber($callNumber)
    {
        $callNumber = trim($callNumber);
        if (empty($callNumber)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a calling number.'
            ], 400);
        }

        try {
            $response = Http::timeout(10)->get('https://openlibrary.org/search.json', [
                'q' => "callnumber:{$callNumber}",
                'limit' => 5,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['docs']) && count($data['docs']) > 0) {
                    $book = $data['docs'][0];
                    $author = isset($book['author_name']) ? implode(', ', $book['author_name']) : '';
                    $publisher = isset($book['publisher']) ? implode(', ', $book['publisher']) : '';
                    $subject = isset($book['subject']) ? implode(', ', array_slice($book['subject'], 0, 10)) : '';
                    $publicationYear = $book['first_publish_year'] ?? '';

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'title' => $book['title'] ?? '',
                            'author' => $author,
                            'publisher' => $publisher,
                            'publication_year' => (string)$publicationYear,
                            'subject' => $subject,
                            'loc_number' => $callNumber,
                            'isbn' => $book['isbn'][0] ?? '',
                        ],
                    ]);
                }
            }
        } catch (\Exception $e) {
            // API failed
        }

        return response()->json([
            'success' => false,
            'message' => 'No book found with that calling number. Please enter details manually.'
        ], 404);
    }

    public function fetchByIsbn($isbn)
    {
        // Clean the ISBN (remove dashes and spaces)
        $isbn = preg_replace('/[^0-9xX]/', '', $isbn);
        
        if (strlen($isbn) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ISBN format. Please enter at least 10 digits.'
            ], 400);
        }
        
        // Try Google Books API using Laravel HTTP facade
        try {
            $response = Http::timeout(10)->get('https://www.googleapis.com/books/v1/volumes', [
                'q' => "isbn:{$isbn}",
                'maxResults' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Check if book was found
                if (isset($data['items']) && count($data['items']) > 0) {
                    return $this->extractGoogleBooksData($data['items'][0]['volumeInfo'], $isbn);
                }
            }
        } catch (\Exception $e) {
            // Google Books failed, try OpenLibrary fallback
        }
        
        // Fallback to OpenLibrary API
        try {
            $olResponse = Http::timeout(10)->get('https://openlibrary.org/api/books', [
                'bibkeys' => "ISBN:{$isbn}",
                'format' => 'json',
                'jscmd' => 'data',
            ]);

            if ($olResponse->successful()) {
                $olData = $olResponse->json();
                $key = "ISBN:{$isbn}";
                if (isset($olData[$key])) {
                    return $this->extractOpenLibraryData($olData[$key], $isbn);
                }
            }
        } catch (\Exception $e) {
            // OpenLibrary fallback also failed
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Book not found. Please check the ISBN and try again, or enter book details manually.'
        ], 404);
    }

    // ==============================
    // HELPER: Extract data from Google Books response
    // ==============================
    private function extractGoogleBooksData($bookData, $isbn)
    {
        $description = $bookData['description'] ?? '';
        $title = $bookData['title'] ?? '';
        $author = isset($bookData['authors']) ? implode(', ', $bookData['authors']) : '';
        $publisher = $bookData['publisher'] ?? '';
        $publicationYear = '';
        if (isset($bookData['publishedDate'])) {
            preg_match('/^\d{4}/', $bookData['publishedDate'], $matches);
            $publicationYear = $matches[0] ?? '';
        }
        
        $categories = $bookData['categories'] ?? [];
        $subject = implode(', ', $categories);
        $keywords = $this->generateKeywords($categories, $description);
        $suggestedCollection = $this->suggestCollection($categories, $subject, $title);
        
        return response()->json([
            'success' => true,
            'data' => [
                'title' => $title,
                'author' => $author,
                'publisher' => $publisher,
                'publication_year' => $publicationYear,
                'subject' => $subject,
                'keywords' => $keywords,
                'collection' => $suggestedCollection,
                'description' => $description,
                'language' => $bookData['language'] ?? '',
                'page_count' => $bookData['pageCount'] ?? '',
                'cover_image' => $bookData['imageLinks']['thumbnail'] ?? $bookData['imageLinks']['smallThumbnail'] ?? '',
                'isbn' => $isbn,
            ],
        ]);
    }

    // ==============================
    // HELPER: Extract data from OpenLibrary response
    // ==============================
    private function extractOpenLibraryData($olBook, $isbn)
    {
        $description = $olBook['notes'] ?? $olBook['excerpts'][0]['text'] ?? '';
        $subjects = [];
        if (isset($olBook['subjects'])) {
            foreach ($olBook['subjects'] as $s) {
                $subjects[] = $s['name'];
            }
        }
        $subject = implode(', ', $subjects);
        $title = $olBook['title'] ?? '';
        $author = isset($olBook['authors']) ? implode(', ', array_column($olBook['authors'], 'name')) : '';
        $publisher = isset($olBook['publishers'][0]['name']) ? $olBook['publishers'][0]['name'] : '';
        $publicationYear = isset($olBook['publish_date']) ? preg_replace('/[^0-9]/', '', $olBook['publish_date']) : '';
        $pageCount = $olBook['number_of_pages'] ?? '';
        $coverImage = $olBook['cover']['large'] ?? $olBook['cover']['medium'] ?? $olBook['cover']['small'] ?? '';
        
        $categories = $subjects;
        $keywords = $this->generateKeywords($categories, $description);
        $suggestedCollection = $this->suggestCollection($categories, $subject, $title);
        
        return response()->json([
            'success' => true,
            'data' => [
                'title' => $title,
                'author' => $author,
                'publisher' => $publisher,
                'publication_year' => $publicationYear,
                'subject' => $subject,
                'keywords' => $keywords,
                'collection' => $suggestedCollection,
                'description' => $description,
                'language' => '',
                'page_count' => $pageCount,
                'cover_image' => $coverImage,
                'isbn' => $isbn,
            ],
        ]);
    }

    // ==============================
    // HELPER: Suggest Collection based on categories
    // ==============================
    private function suggestCollection($categories, $subject, $title)
    {
        $allText = strtolower($subject . ' ' . $title . ' ' . implode(' ', $categories));
        
        if (strpos($allText, 'philippine') !== false || 
            strpos($allText, 'filipino') !== false || 
            strpos($allText, 'rizal') !== false ||
            strpos($allText, 'tagalog') !== false ||
            strpos($allText, 'filipiniana') !== false) {
            return 'Filipiniana';
        }
        
        if (strpos($allText, 'fiction') !== false || 
            strpos($allText, 'novel') !== false || 
            strpos($allText, 'story') !== false ||
            strpos($allText, 'fantasy') !== false ||
            strpos($allText, 'science fiction') !== false ||
            strpos($allText, 'mystery') !== false ||
            strpos($allText, 'romance') !== false) {
            return 'Fictions';
        }
        
        if (strpos($allText, 'thesis') !== false || 
            strpos($allText, 'dissertation') !== false || 
            strpos($allText, 'research') !== false) {
            return 'Thesis Collection';
        }
        
        if (strpos($allText, 'history') !== false || 
            strpos($allText, 'heritage') !== false || 
            strpos($allText, 'rare') !== false ||
            strpos($allText, 'manuscript') !== false) {
            return 'Special Collections';
        }
        
        if (strpos($allText, 'congress') !== false || 
            strpos($allText, 'law') !== false || 
            strpos($allText, 'government') !== false ||
            strpos($allText, 'congressional') !== false) {
            return 'Library of Congress';
        }
        
        return '';
    }

    // ==============================
    // HELPER: Generate keywords from categories and description
    // ==============================
    private function generateKeywords($categories, $description)
    {
        $keywords = [];
        
        foreach ($categories as $category) {
            $words = explode(' ', $category);
            foreach ($words as $word) {
                if (strlen($word) > 3 && !in_array(strtolower($word), ['and', 'the', 'for', 'with', 'this', 'that'])) {
                    $keywords[] = $word;
                }
            }
            $keywords[] = $category;
        }
        
        if ($description) {
            $descWords = explode(' ', substr($description, 0, 200));
            $importantWords = array_filter($descWords, function($word) {
                return strlen($word) > 4 && !in_array(strtolower($word), ['this', 'that', 'these', 'those', 'there', 'their', 'would', 'could', 'should', 'about', 'with', 'without']);
            });
            $keywords = array_merge($keywords, array_slice($importantWords, 0, 10));
        }
        
        $uniqueKeywords = array_unique($keywords);
        $uniqueKeywords = array_slice($uniqueKeywords, 0, 15);
        
        return implode(', ', $uniqueKeywords);
    }

    // ==============================
    // IMPORT BOOKS: PREVIEW
    // ==============================
    public function importPreview(Request $request, \App\Services\BookImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:15360', // max 15MB
        ]);

        $preview = $importService->preview($request->file('file'));

        return response()->json($preview);
    }

    // ==============================
    // IMPORT BOOKS: PROCESS
    // ==============================
    public function importProcess(Request $request, \App\Services\BookImportService $importService)
    {
        $request->validate([
            'books' => 'required|array|min:1',
            'books.*.title' => 'required|string',
        ]);

        $adminUser = auth()->user()->name ?? auth()->user()->email ?? 'Admin';
        $result = $importService->executeImport($request->books, $adminUser);

        return response()->json($result);
    }
}















