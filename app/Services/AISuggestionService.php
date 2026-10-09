<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookUsage;
use App\Models\Faculty;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AISuggestionService
{
    // In app/Services/AISuggestionService.php

    public function getExternalRecommendations($studentId, $returnedBookId = null, $limit = 3)
    {
        $student = Student::find($studentId);
        if (! $student) {
            return $this->getFallbackRecommendations($studentId, $limit);
        }

        // Get the subject of the returned book (if provided)
        $returnedBook = null;
        $subjectQuery = '';
        if ($returnedBookId) {
            $returnedBook = Book::find($returnedBookId);
            if ($returnedBook && $returnedBook->subject) {
                $subjectQuery = $returnedBook->subject;
            }
        }

        // Build search query: prioritize returned book subject, then student's course
        $searchQuery = $subjectQuery ?: $student->program;

        // If still empty, use a default term
        if (empty($searchQuery)) {
            $searchQuery = 'library science';
        }

        // Call Open Library Search API
        $response = Http::timeout(10)->get('https://openlibrary.org/search.json', [
            'q' => $searchQuery,
            'limit' => $limit,
            'fields' => 'key,title,author_name,cover_i,first_publish_year,subject',
        ]);

        if ($response->failed()) {
            Log::error('OpenLibrary API request failed: '.$response->body());

            return $this->getFallbackRecommendations($studentId, $limit);
        }

        $books = $response->json()['docs'] ?? [];

        // Get already borrowed book IDs to avoid suggesting what the student already read
        $borrowedIds = BookUsage::where('student_id', $studentId)->pluck('book_id')->toArray();

        $recommendations = [];
        foreach ($books as $book) {
            // Skip if we don't have a title or author
            if (empty($book['title'])) {
                continue;
            }

            // Optional: skip if the book already exists in your library?
            // But external recommendations are meant to be new, so we don't check local DB.

            $recommendations[] = [
                'book_id' => null, // external book has no local ID
                'title' => $book['title'],
                'author' => $book['author_name'][0] ?? 'Unknown Author',
                'cover_url' => isset($book['cover_i']) ? "https://covers.openlibrary.org/b/id/{$book['cover_i']}-M.jpg" : null,
                'openlibrary_url' => "https://openlibrary.org{$book['key']}",
                'reason' => 'Recommended based on your interest in '.($subjectQuery ?: $student->program).'.',
                'is_external' => true,
            ];

            if (count($recommendations) >= $limit) {
                break;
            }
        }

        // If no external results, fallback to internal course-based recommendations
        if (empty($recommendations)) {
            return $this->getFallbackRecommendations($studentId, $limit);
        }

        return $recommendations;
    }

    /**
     * Fallback recommendations (internal, based on course popularity)
     */
    private function getFallbackRecommendations($studentId, $limit = 3)
    {
        $student = Student::find($studentId);
        if (! $student) {
            return [];
        }

        $borrowedIds = BookUsage::where('student_id', $studentId)->pluck('book_id')->toArray();

        // Try course‑specific popular books first
        $popularInCourse = BookUsage::join('students', 'book_usages.student_id', '=', 'students.id')
            ->join('books', 'book_usages.book_id', '=', 'books.id')
            ->where('students.program', $student->program)
            ->whereNotIn('book_usages.book_id', $borrowedIds)
            ->select('books.id', 'books.title', 'books.author', DB::raw('COUNT(*) as borrow_count'))
            ->groupBy('books.id', 'books.title', 'books.author')
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->get();

        if ($popularInCourse->isNotEmpty()) {
            return $popularInCourse->map(function ($book) {
                return [
                    'book_id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'reason' => 'Popular among '.$book->program.' students.',
                    'is_external' => false,
                ];
            })->toArray();
        }

        // Fallback to overall popular books (any course)
        $overallPopular = BookUsage::select('book_id', DB::raw('COUNT(*) as borrow_count'))
            ->groupBy('book_id')
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->with('book')
            ->get();

        if ($overallPopular->isNotEmpty()) {
            return $overallPopular->map(function ($usage) {
                return [
                    'book_id' => $usage->book->id,
                    'title' => $usage->book->title,
                    'author' => $usage->book->author,
                    'reason' => 'Frequently borrowed by other students.',
                    'is_external' => false,
                ];
            })->toArray();
        }

        // Ultimate fallback: return a single generic recommendation
        return [
            [
                'book_id' => null,
                'title' => 'Explore Our Library',
                'author' => 'Library Staff',
                'reason' => 'Check out our latest arrivals!',
                'is_external' => false,
            ],
        ];
    }

    /**
     * Get purchase suggestions for admin based on trends
     */
    public function getPurchaseSuggestions($limit = 8)
    {
        // Get trending subjects from last 30 days and split comma-separated values
        $rawSubjects = BookUsage::join('books', 'book_usages.book_id', '=', 'books.id')
            ->where('book_usages.created_at', '>=', now()->subDays(30))
            ->select('books.subject', DB::raw('COUNT(*) as count'))
            ->whereNotNull('books.subject')
            ->groupBy('books.subject')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Split comma-separated subject strings into individual terms
        $subjectCounts = [];
        foreach ($rawSubjects as $row) {
            foreach (array_map('trim', explode(',', $row->subject)) as $part) {
                if ($part !== '') {
                    $subjectCounts[$part] = ($subjectCounts[$part] ?? 0) + $row->count;
                }
            }
        }
        arsort($subjectCounts);
        $trendingSubjects = array_slice($subjectCounts, 0, 5, true);

        // Get existing books in the system (lowercase for comparison)
        $existingBooks = Book::pluck('title')->map(fn ($t) => strtolower($t))->toArray();

        $suggestions = [];

        // Search Open Library using individual subject terms
        foreach ($trendingSubjects as $searchSubject => $borrowCount) {
            if (count($suggestions) >= $limit) break;
            try {
                $response = Http::timeout(10)->get('https://openlibrary.org/search.json', [
                    'subject' => $searchSubject,
                    'limit'   => 8,
                ]);

                if ($response->successful()) {
                    $books = $response->json()['docs'] ?? [];
                    foreach ($books as $book) {
                        $title = $book['title'] ?? '';
                        if (empty($title) || in_array(strtolower($title), $existingBooks)) {
                            continue;
                        }

                        $isbn    = $book['isbn'][0] ?? null;
                        $coverId = $book['cover_i'] ?? null;

                        $suggestions[] = [
                            'title'    => $title,
                            'author'   => $book['author_name'][0] ?? 'Unknown Author',
                            'subject'  => $searchSubject,
                            'reason'   => "Popular in {$searchSubject} category. This subject has been borrowed {$borrowCount} times recently.",
                            'year'     => $book['first_publish_year'] ?? null,
                            'cover_url'=> $coverId ? "https://covers.openlibrary.org/b/id/{$coverId}-M.jpg" : null,
                            'isbn'     => $isbn,
                            'purchase_links' => $this->generatePurchaseLinks($title, $book['author_name'][0] ?? '', $isbn),
                        ];

                        if (count($suggestions) >= $limit) break;
                    }
                }
            } catch (\Exception $e) {
                Log::error('OpenLibrary search failed: '.$e->getMessage());
            }
        }

        // Fallback 1: popular borrowed books in the library
        if (count($suggestions) < $limit) {
            $needed      = $limit - count($suggestions);
            $alreadyTitles = array_map(fn ($s) => strtolower($s['title']), $suggestions);

            $popularBooks = BookUsage::join('books', 'book_usages.book_id', '=', 'books.id')
                ->select('books.title', 'books.author', 'books.subject', DB::raw('COUNT(*) as count'))
                ->groupBy('books.id', 'books.title', 'books.author', 'books.subject')
                ->orderByDesc('count')
                ->limit($needed + 5)
                ->get();

            foreach ($popularBooks as $book) {
                if (count($suggestions) >= $limit) break;
                if (in_array(strtolower($book->title), $alreadyTitles)) continue;

                $suggestions[] = [
                    'title'    => $book->title,
                    'author'   => $book->author,
                    'subject'  => $book->subject,
                    'reason'   => "Borrowed {$book->count} times. Consider acquiring more copies or related titles.",
                    'year'     => null,
                    'cover_url'=> null,
                    'isbn'     => null,
                    'purchase_links' => $this->generatePurchaseLinks($book->title, $book->author),
                ];
                $alreadyTitles[] = strtolower($book->title);
            }
        }

        // Fallback 2: recently added catalog books to fill remaining slots
        if (count($suggestions) < $limit) {
            $needed      = $limit - count($suggestions);
            $alreadyTitles = array_map(fn ($s) => strtolower($s['title']), $suggestions);

            $recentBooks = Book::orderByDesc('created_at')
                ->limit($needed + 10)
                ->get();

            foreach ($recentBooks as $book) {
                if (count($suggestions) >= $limit) break;
                if (in_array(strtolower($book->title), $alreadyTitles)) continue;

                $suggestions[] = [
                    'title'    => $book->title,
                    'author'   => $book->author,
                    'subject'  => $book->subject,
                    'reason'   => 'Recently added to the library catalog. Consider acquiring related titles in this subject.',
                    'year'     => null,
                    'cover_url'=> null,
                    'isbn'     => null,
                    'purchase_links' => $this->generatePurchaseLinks($book->title, $book->author),
                ];
                $alreadyTitles[] = strtolower($book->title);
            }
        }

        return $suggestions;
    }

    /**
     * Get personalized book recommendations for a student
     */
    public function getStudentRecommendations($studentId, $limit = 6)
    {
        $student = Student::find($studentId);
        if (! $student) {
            return [];
        }

        // Content-based filtering: recommend books with similar subjects/keywords
        $borrowedBookIds = BookUsage::where('student_id', $studentId)->pluck('book_id');

        $borrowedBooks = Book::whereIn('id', $borrowedBookIds)->get();

        // Extract top subjects and keywords from the student's reading history
        $subjects = $borrowedBooks->pluck('subject')->filter()
            ->countBy()->sortDesc()->keys()->take(3);

        $keywords = collect($borrowedBooks->pluck('keywords')->filter()
            ->flatMap(fn($k) => explode(',', $k)))->map(fn($k) => trim($k))
            ->filter()->countBy()->sortDesc()->keys()->take(5);

        if ($subjects->isNotEmpty() || $keywords->isNotEmpty()) {
            $candidates = Book::whereNotIn('id', $borrowedBookIds)
                ->where('status', 'available')
                ->where('is_condemned', false)
                ->where(function ($q) use ($subjects, $keywords) {
                    foreach ($subjects as $s) { $q->orWhere('subject', 'LIKE', "%{$s}%"); }
                    foreach ($keywords as $k) { $q->orWhere('keywords', 'LIKE', "%{$k}%"); }
                })
                ->limit($limit * 3)
                ->get();

            if ($candidates->isNotEmpty()) {
                $topSubject = $subjects->first() ?? 'your interests';
                $scored = $candidates->map(function ($book) use ($subjects, $keywords, $topSubject) {
                    $score = 0;
                    foreach ($subjects as $s) { if ($book->subject && stripos($book->subject, $s) !== false) $score += 2; }
                    foreach ($keywords as $k) { if ($book->keywords && stripos($book->keywords, $k) !== false) $score += 1; }
                    return [
                        'book_id' => $book->id,
                        'title'   => $book->title,
                        'author'  => $book->author,
                        'subject' => $book->subject,
                        'reason'  => "Matches your reading interest in {$topSubject}.",
                        '_score'  => $score,
                    ];
                })->sortByDesc('_score')->take($limit)->values()->toArray();

                return $scored;
            }
        }

        // Fallback: popular books in the same program (collaborative)
        $courseBooks = BookUsage::join('students', 'book_usages.student_id', '=', 'students.id')
            ->join('books', 'book_usages.book_id', '=', 'books.id')
            ->where('students.program', $student->program)
            ->whereNotIn('book_usages.book_id', $borrowedBookIds)
            ->where('books.status', 'available')
            ->select('books.id', 'books.title', 'books.author', 'books.subject', DB::raw('COUNT(*) as borrow_count'))
            ->groupBy('books.id', 'books.title', 'books.author', 'books.subject')
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->get();

        if ($courseBooks->isNotEmpty()) {
            return $courseBooks->map(fn($b) => [
                'book_id' => $b->id,
                'title'   => $b->title,
                'author'  => $b->author,
                'subject' => $b->subject,
                'reason'  => 'Popular among students in your program.',
            ])->toArray();
        }

        // Final fallback: overall popular books (join avoids N+1)
        return BookUsage::join('books', 'book_usages.book_id', '=', 'books.id')
            ->select('books.id as book_id', 'books.title', 'books.author', 'books.subject', DB::raw('COUNT(*) as borrow_count'))
            ->groupBy('books.id', 'books.title', 'books.author', 'books.subject')
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->get()
            ->map(fn($b) => [
                'book_id' => $b->book_id,
                'title'   => $b->title,
                'author'  => $b->author,
                'subject' => $b->subject,
                'reason'  => 'Frequently borrowed by students.',
            ])->toArray();
    }

    /**
     * Get personalized book recommendations for a faculty member
     */
    public function getFacultyRecommendations($facultyId, $limit = 3)
    {
        $faculty = Faculty::find($facultyId);
        if (!$faculty) {
            return [];
        }

        $borrowedBookIds = BookUsage::where('faculty_id', $facultyId)
            ->pluck('book_id')
            ->toArray();

        // Popular books among faculty in same department
        if ($faculty->department) {
            $deptBooks = BookUsage::join('faculties', 'book_usages.faculty_id', '=', 'faculties.id')
                ->join('books', 'book_usages.book_id', '=', 'books.id')
                ->where('faculties.department', $faculty->department)
                ->whereNotIn('book_usages.book_id', $borrowedBookIds)
                ->select('books.id', 'books.title', 'books.author', DB::raw('COUNT(*) as borrow_count'))
                ->groupBy('books.id', 'books.title', 'books.author')
                ->orderByDesc('borrow_count')
                ->limit($limit)
                ->get();

            if ($deptBooks->isNotEmpty()) {
                return $deptBooks->map(fn($book) => [
                    'book_id' => $book->id,
                    'title'   => $book->title,
                    'author'  => $book->author,
                    'reason'  => 'Popular in the ' . $faculty->department . ' department.',
                ])->toArray();
            }
        }

        // Fallback: overall popular books among faculty (join avoids N+1)
        $popularBooks = BookUsage::whereNotNull('book_usages.faculty_id')
            ->join('books', 'book_usages.book_id', '=', 'books.id')
            ->whereNotIn('book_usages.book_id', $borrowedBookIds)
            ->select('books.id as book_id', 'books.title', 'books.author', DB::raw('COUNT(*) as borrow_count'))
            ->groupBy('books.id', 'books.title', 'books.author')
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->get();

        if ($popularBooks->isNotEmpty()) {
            return $popularBooks->map(fn($b) => [
                'book_id' => $b->book_id,
                'title'   => $b->title,
                'author'  => $b->author,
                'reason'  => 'Frequently borrowed by other faculty members.',
            ])->toArray();
        }

        return [];
    }

    /**
     * Check if there are new editions available for an existing book
     */
    public function checkNewEditions($title, $author)
    {
        $searchQuery = urlencode($title . ' ' . $author);
        
        try {
            $response = Http::timeout(10)->get('https://openlibrary.org/search.json', [
                'q' => $searchQuery,
                'limit' => 5,
                'sort' => 'new',
                'fields' => 'key,title,author_name,first_publish_year,publish_year,isbn,cover_i',
            ]);

            if ($response->failed()) {
                return ['error' => 'Unable to check for new editions at this time.'];
            }

            $books = $response->json()['docs'] ?? [];
            
            if (empty($books)) {
                return ['message' => 'No new editions found.', 'has_new' => false];
            }

            $newEditions = [];
            $currentYear = (int) date('Y');
            
            foreach ($books as $book) {
                $publishYears = $book['publish_year'] ?? [];
                $latestYear = !empty($publishYears) ? max($publishYears) : null;
                
                if ($latestYear && $latestYear >= $currentYear - 5) {
                    $newEditions[] = [
                        'title' => $book['title'] ?? $title,
                        'author' => $book['author_name'][0] ?? $author,
                        'latest_edition_year' => $latestYear,
                        'isbn' => $book['isbn'][0] ?? null,
                        'cover_url' => isset($book['cover_i']) ? "https://covers.openlibrary.org/b/id/{$book['cover_i']}-M.jpg" : null,
                        'openlibrary_url' => "https://openlibrary.org{$book['key']}",
                    ];
                }
            }

            if (empty($newEditions)) {
                return ['message' => 'No recent editions found.', 'has_new' => false];
            }

            return [
                'has_new' => true,
                'editions' => $newEditions,
                'message' => 'Found ' . count($newEditions) . ' recent edition(s).',
            ];

        } catch (\Exception $e) {
            Log::error('checkNewEditions failed: ' . $e->getMessage());
            return ['error' => 'Error checking for new editions. Please try again.'];
        }
    }

    private function generatePurchaseLinks($title, $author, $isbn = null)
    {
        $searchQuery = urlencode($title.' '.$author);

        $links = [
            'Fully Booked' => "https://www.fullybookedonline.com/search?q={$searchQuery}",
            'National Book Store' => "https://www.nationalbookstore.com/search?q={$searchQuery}",
            'Amazon' => "https://www.amazon.com/s?k={$searchQuery}",
            'Shopee' => "https://shopee.ph/search?keyword={$searchQuery}",
        ];

        if ($isbn) {
            $links['Fully Booked'] = "https://www.fullybookedonline.com/products?isbn={$isbn}";
            $links['Amazon'] = "https://www.amazon.com/dp/{$isbn}";
        }

        return $links;
    }
}
