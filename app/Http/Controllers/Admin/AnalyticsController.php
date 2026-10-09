<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookUsage;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Resolve period context (supports shortcuts: today, week, month, quarter, half_year, year, all, custom).
     */
    protected function periodContext(Request $request): array
    {
        $periodParam = $request->query('period');
        $startDateParam = $request->query('start_date');
        $endDateParam = $request->query('end_date');

        $startDate = null;
        $endDate = null;
        $periodKey = $periodParam ?: 'all';

        if (!empty($startDateParam) || !empty($endDateParam)) {
            $periodKey = 'custom';
            if (!empty($startDateParam)) {
                try { $startDate = Carbon::parse($startDateParam)->startOfDay(); } catch (\Exception $e) {}
            }
            if (!empty($endDateParam)) {
                try { $endDate = Carbon::parse($endDateParam)->endOfDay(); } catch (\Exception $e) {}
            }
            $fromStr = $startDate ? $startDate->format('M d, Y') : 'Beginning';
            $toStr = $endDate ? $endDate->format('M d, Y') : 'Present';
            $label = "$fromStr – $toStr";
        } else {
            switch ($periodKey) {
                case 'today':
                case 'day':
                    $periodKey = 'today';
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    $label = 'Today (' . Carbon::today()->format('M d, Y') . ')';
                    break;
                case 'week':
                    $periodKey = 'week';
                    $startDate = Carbon::now()->subDays(7)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    $label = 'Past 7 Days';
                    break;
                case 'month':
                    $periodKey = 'month';
                    $startDate = Carbon::now()->subDays(30)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    $label = 'Past Month (30 Days)';
                    break;
                case 'quarter':
                    $periodKey = 'quarter';
                    $startDate = Carbon::now()->subMonths(3)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    $label = 'Past Quarter (3 Months)';
                    break;
                case 'half_year':
                    $periodKey = 'half_year';
                    $startDate = Carbon::now()->subMonths(6)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    $label = 'Half a Year (6 Months)';
                    break;
                case 'year':
                    $periodKey = 'year';
                    $startDate = Carbon::now()->subYear()->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    $label = 'Past Year';
                    break;
                case 'all':
                default:
                    $periodKey = 'all';
                    $startDate = null;
                    $endDate = null;
                    $label = 'All Time';
                    break;
            }
        }

        return [
            'periodKey'      => $periodKey,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'periodLabel'    => $label,
            'startDateParam' => $startDateParam,
            'endDateParam'   => $endDateParam,
        ];
    }

    protected function applyUsagePeriodFilter($query, ?Carbon $startDate, ?Carbon $endDate = null, string $column = 'time_in')
    {
        if ($startDate) {
            $query->where($column, '>=', $startDate);
        }
        if ($endDate) {
            $query->where($column, '<=', $endDate);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $ctx = $this->periodContext($request);
        $periodKey = $ctx['periodKey'];
        $startDate = $ctx['startDate'];
        $endDate = $ctx['endDate'];
        $periodLabel = $ctx['periodLabel'];

        // Period borrows
        $usageQuery = BookUsage::query();
        $this->applyUsagePeriodFilter($usageQuery, $startDate, $endDate, 'time_in');
        $periodBorrows = (clone $usageQuery)->count();

        // Completed Returns in period
        $returnsQuery = BookUsage::where('status', 'completed');
        $this->applyUsagePeriodFilter($returnsQuery, $startDate, $endDate, 'time_out');
        $periodReturns = $returnsQuery->count();

        // Active borrowers count in period
        $activeBorrowersQuery = BookUsage::query();
        $this->applyUsagePeriodFilter($activeBorrowersQuery, $startDate, $endDate, 'time_in');
        $activeUsers = $activeBorrowersQuery->get()
            ->map(fn ($usage) => $usage->student_id ? 's:' . $usage->student_id : 'f:' . $usage->faculty_id)
            ->unique()
            ->count();

        // Monthly borrows
        $monthBorrows = BookUsage::whereMonth('time_in', now()->month)
            ->whereYear('time_in', now()->year)
            ->count();

        // Avg daily borrows
        $avgDailyBorrows = $periodKey === 'week'
            ? $periodBorrows / 7
            : ($periodKey === 'today' ? $periodBorrows : BookUsage::whereBetween('time_in', [
                now()->startOfWeek(Carbon::MONDAY),
                now()->endOfWeek(Carbon::SUNDAY),
            ])->count() / 7);

        // Most Borrowed Books (Top 10)
        $mostBorrowedBooks = BookUsage::select('book_id', DB::raw('count(*) as total'))
            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
            ->groupBy('book_id')
            ->with('book')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Least Borrowed Books (Bottom 10)
        $leastBorrowedBooks = Book::leftJoin('book_usages', function ($join) use ($startDate, $endDate) {
            $join->on('books.id', '=', 'book_usages.book_id');
            if ($startDate) {
                $join->where('book_usages.time_in', '>=', $startDate);
            }
            if ($endDate) {
                $join->where('book_usages.time_in', '<=', $endDate);
            }
        })
            ->where('books.status', '!=', 'archived')
            ->where('books.is_condemned', false)
            ->select('books.id', 'books.title', 'books.author', 'books.collection', 'books.accession_number',
                     DB::raw('count(book_usages.id) as total'))
            ->groupBy('books.id', 'books.title', 'books.author', 'books.collection', 'books.accession_number')
            ->orderBy('total')
            ->limit(10)
            ->get();

        // Never Borrowed Count
        $neverBorrowedCount = Book::where('status', '!=', 'archived')
            ->where('is_condemned', false)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('book_usages')
                  ->whereColumn('book_usages.book_id', 'books.id');
            })->count();

        // Program usage (by student program)
        $programFilter = $request->query('program_filter');
        $programUsage = BookUsage::join('students', 'book_usages.student_id', '=', 'students.id')
            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
            ->when($programFilter, fn ($q) => $q->where('students.program', $programFilter))
            ->select('students.program', DB::raw('count(*) as total'))
            ->groupBy('students.program')
            ->orderByDesc('total')
            ->get();

        $allCourses = Course::orderBy('name')->get();

        // Borrow trend timeline (grouped by day)
        $borrowTrend = BookUsage::select(
            DB::raw('DATE(time_in) as day'),
            DB::raw('count(*) as total')
        )
            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Top subjects
        $rawSubjects = BookUsage::join('books', 'books.id', '=', 'book_usages.book_id')
            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
            ->whereNotNull('books.subject')
            ->select('books.subject', DB::raw('count(*) as total'))
            ->groupBy('books.subject')
            ->get();

        $subjectCounts = [];
        foreach ($rawSubjects as $row) {
            foreach (array_map('trim', explode(',', $row->subject)) as $part) {
                if ($part !== '') {
                    $subjectCounts[$part] = ($subjectCounts[$part] ?? 0) + $row->total;
                }
            }
        }
        arsort($subjectCounts);
        $topSubjects = collect(array_slice($subjectCounts, 0, 10, true))
            ->map(fn ($total, $subject) => (object) ['subject' => $subject, 'total' => $total])
            ->values();

        // Collection distribution
        $collectionUsage = BookUsage::join('books', 'books.id', '=', 'book_usages.book_id')
            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
            ->select('books.collection', DB::raw('count(*) as total'))
            ->groupBy('books.collection')
            ->orderByDesc('total')
            ->get();

        // Faculty popular books
        $facultyPopularBooks = BookUsage::whereNotNull('faculty_id')
            ->join('books', 'book_usages.book_id', '=', 'books.id')
            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
            ->select('books.id', 'books.title', 'books.author', 'books.accession_number', DB::raw('count(*) as total'))
            ->groupBy('books.id', 'books.title', 'books.author', 'books.accession_number')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // New acquisitions (within period or last 3 months)
        $newAcquisitions = Book::when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $totalBooks     = Book::sum('copies');
        $totalStudents  = Student::count();
        $totalFaculty   = Faculty::count();

        return view('admin.analytics', compact(
            'periodBorrows',
            'periodReturns',
            'activeUsers',
            'monthBorrows',
            'avgDailyBorrows',
            'mostBorrowedBooks',
            'leastBorrowedBooks',
            'neverBorrowedCount',
            'programUsage',
            'borrowTrend',
            'topSubjects',
            'collectionUsage',
            'facultyPopularBooks',
            'newAcquisitions',
            'totalBooks',
            'totalStudents',
            'totalFaculty',
            'periodKey',
            'periodLabel',
            'startDate',
            'endDate',
            'allCourses',
            'programFilter'
        ));
    }
}
