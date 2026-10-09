<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookRequisition;
use App\Models\BookUsage;
use App\Models\Faculty;
use App\Models\ReportPreset;
use App\Models\Reservation;
use App\Models\Student;
use App\Services\AISuggestionService;
use App\Services\ReportExportService;
use App\Services\SeasonalBookService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
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
            'periodStart'    => $startDate, // backward compatibility
            'periodEnd'      => $endDate,
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

    protected function countActiveBorrowers(?Carbon $startDate, ?Carbon $endDate = null): int
    {
        $query = BookUsage::where('status', 'active');
        if ($startDate) {
            $query->where('time_in', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('time_in', '<=', $endDate);
        }

        return $query->get()
            ->map(fn ($usage) => $usage->student_id
                ? 's:'.$usage->student_id
                : 'f:'.$usage->faculty_id)
            ->unique()
            ->count();
    }

    /**
     * Resolve report filter options from preset or request parameters.
     */
    protected function resolveReportOptions(Request $request, string $reportType): array
    {
        $options = [];

        // Check if custom report options are applied
        if ($request->has('custom_options_applied') || $request->has('custom_report') || $request->has('options')) {
            $options['custom_options_applied'] = true;
        }

        // Direct query / input parameter overrides (e.g. ?inc_most_borrowed=1)
        foreach ($request->all() as $k => $v) {
            if (str_starts_with($k, 'inc_')) {
                $options[substr($k, 4)] = filter_var($v, FILTER_VALIDATE_BOOLEAN);
                $options['custom_options_applied'] = true;
            } elseif (str_starts_with($k, 'opt_')) {
                $options[substr($k, 4)] = filter_var($v, FILTER_VALIDATE_BOOLEAN);
            }
        }

        // Check preset first
        $presetId = $request->query('preset_id') ?: $request->input('preset_id');
        if ($presetId) {
            $preset = ReportPreset::find($presetId);
            if ($preset && is_array($preset->options)) {
                $options = array_merge($options, $preset->options);
                $options['custom_options_applied'] = true;
            }
        } elseif ($request->has('options')) {
            $rawOptions = $request->input('options');
            if (is_string($rawOptions)) {
                $decoded = json_decode($rawOptions, true) ?: [];
                $options = array_merge($options, $decoded);
            } elseif (is_array($rawOptions)) {
                $options = array_merge($options, $rawOptions);
            }
            $options['custom_options_applied'] = true;
        }

        return $options;
    }

    public function index(Request $request)
    {
        $ctx = $this->periodContext($request);
        $periodKey = $ctx['periodKey'];
        $startDate = $ctx['startDate'];
        $endDate = $ctx['endDate'];
        $periodLabel = $ctx['periodLabel'];

        $usageQuery = BookUsage::query();
        $this->applyUsagePeriodFilter($usageQuery, $startDate, $endDate, 'time_in');

        $dailyBorrows = (clone $usageQuery)->count();
        $activeUsers = $this->countActiveBorrowers($startDate, $endDate);

        $monthBorrows = BookUsage::whereMonth('time_in', now()->month)
            ->whereYear('time_in', now()->year)
            ->count();

        $avgDailyBorrows = $periodKey === 'week'
            ? $dailyBorrows / 7
            : ($periodKey === 'today' ? $dailyBorrows : BookUsage::whereBetween('time_in', [
                now()->startOfWeek(Carbon::MONDAY),
                now()->endOfWeek(Carbon::SUNDAY),
            ])->count() / 7);

        $mostBorrowedBooks = BookUsage::select('book_id', DB::raw('count(*) as total'))
            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
            ->groupBy('book_id')
            ->with('book')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $programFilter = $request->query('program_filter');
        $programUsage = BookUsage::join('students', 'book_usages.student_id', '=', 'students.id')
            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
            ->when($programFilter, fn ($q) => $q->where('students.program', $programFilter))
            ->select('students.program', DB::raw('count(*) as total'))
            ->groupBy('students.program')
            ->orderByDesc('total')
            ->get();
        $allCourses = \App\Models\Course::orderBy('name')->get();

        $borrowTrend = BookUsage::select(
            DB::raw('DATE(time_in) as day'),
            DB::raw('count(*) as total')
        )
            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

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

        $newAcquisitions = Book::when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // ── Circulation Report data ─────────────────────────────────────
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

        $neverBorrowedCount = Book::where('status', '!=', 'archived')
            ->where('is_condemned', false)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('book_usages')
                  ->whereColumn('book_usages.book_id', 'books.id');
            })->count();

        $currentlyOnLoan = BookUsage::where('status', 'active')->count();

        $totalReturns = $this->applyUsagePeriodFilter(
            BookUsage::where('status', 'completed'), $startDate, $endDate, 'time_out'
        )->count();

        $collectionUsage = BookUsage::join('books', 'books.id', '=', 'book_usages.book_id')
            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
            ->select('books.collection', DB::raw('count(*) as total'))
            ->groupBy('books.collection')
            ->orderByDesc('total')
            ->get();

        $totalBooks     = Book::sum('copies');
        $totalStudents  = Student::count();
        $totalFaculty   = Faculty::count();
        $condemnedBooks = Book::where('is_condemned', true)->count();
        $archivedBooks  = Book::where('status', 'archived')->count();

        $facultyPopularBooks = BookUsage::whereNotNull('faculty_id')
            ->join('books', 'book_usages.book_id', '=', 'books.id')
            ->select('books.id', 'books.title', 'books.author', 'books.accession_number', DB::raw('count(*) as total'))
            ->groupBy('books.id', 'books.title', 'books.author', 'books.accession_number')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ── Real-time Operational Records for Dashboard ──
        $todayBorrowsList = BookUsage::with(['book', 'student', 'faculty'])
            ->whereDate('time_in', Carbon::today())
            ->orderByDesc('time_in')
            ->limit(10)
            ->get();

        $todayReturnsList = BookUsage::with(['book', 'student', 'faculty'])
            ->where('status', 'completed')
            ->whereDate('time_out', Carbon::today())
            ->orderByDesc('time_out')
            ->limit(10)
            ->get();

        $activeLoansList = BookUsage::with(['book', 'student', 'faculty'])
            ->where('status', 'active')
            ->orderByDesc('time_in')
            ->limit(10)
            ->get();

        // Overdue count (past due date or flagged overdue while active)
        $overdueBooksCount = BookUsage::where('status', 'active')
            ->where(function ($q) {
                $q->where('is_overdue_flagged', true)
                  ->orWhere(function ($sq) {
                      $sq->whereNotNull('due_date')
                         ->where('due_date', '<', Carbon::today());
                  });
            })
            ->count();

        // Recent System Activity Feed (last 10 actions)
        $recentActivities = \App\Models\ActivityLog::with('adminUser')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Top 5 Borrowed This Week
        $weekStart = Carbon::now('Asia/Manila')->startOfWeek(Carbon::MONDAY);
        $topBorrowedThisWeek = BookUsage::where('time_in', '>=', $weekStart)
            ->select('book_id', DB::raw('count(*) as total'))
            ->groupBy('book_id')
            ->with('book')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Recently Added Books (last 5 added)
        $recentAddedBooks = Book::where('status', '!=', 'archived')
            ->where('is_condemned', false)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $pendingDamageReports  = \App\Models\LibraryDamageReport::where('status', 'pending')->count();
        $pendingPenaltiesCount = \App\Models\LibraryPenalty::where('status', 'pending')->count();
        $pendingRequisitions   = \App\Models\BookRequisition::where('status', 'submitted')->count();
        $pendingUserApprovals  = Student::where('status', 'pending')->count() + Faculty::where('status', 'pending')->count();

        $totalPendingNotifications = $pendingUserApprovals + $pendingDamageReports + $pendingPenaltiesCount + $overdueBooksCount + $pendingRequisitions;

        // ── Seasonal Books Feature (Commented out per user request) ──
        // $activeSeason = SeasonalBookService::getActiveSeason();
        // $seasonalBooks = SeasonalBookService::getSuggestedBooks(8);
        // $seasonalConfig = SeasonalBookService::getConfig();
        $activeSeason = null;
        $seasonalBooks = collect();
        $seasonalConfig = [];

        return view('dashboard', [
            'totalBooks'                => $totalBooks,
            'totalStudents'             => $totalStudents,
            'totalFaculty'              => $totalFaculty,
            'dailyBorrows'              => $dailyBorrows,
            'activeUsers'               => $activeUsers,
            'monthBorrows'              => $monthBorrows,
            'periodBorrows'             => $dailyBorrows,
            'avgDailyBorrows'           => $avgDailyBorrows,
            'mostBorrowedBooks'         => $mostBorrowedBooks,
            'leastBorrowedBooks'        => $leastBorrowedBooks,
            'neverBorrowedCount'        => $neverBorrowedCount,
            'currentlyOnLoan'           => $currentlyOnLoan,
            'totalReturns'              => $totalReturns,
            'condemnedBooks'            => $condemnedBooks,
            'archivedBooks'             => $archivedBooks,
            'collectionUsage'           => $collectionUsage,
            'programUsage'              => $programUsage,
            'borrowTrend'               => $borrowTrend,
            'topSubjects'               => $topSubjects,
            'newAcquisitions'           => $newAcquisitions,
            'facultyPopularBooks'       => $facultyPopularBooks,
            'todayBorrowsList'          => $todayBorrowsList,
            'todayReturnsList'          => $todayReturnsList,
            'activeLoansList'           => $activeLoansList,
            'overdueBooksCount'         => $overdueBooksCount,
            'recentActivities'          => $recentActivities,
            'topBorrowedThisWeek'       => $topBorrowedThisWeek,
            'recentAddedBooks'          => $recentAddedBooks,
            'pendingDamageReports'      => $pendingDamageReports,
            'pendingPenaltiesCount'     => $pendingPenaltiesCount,
            'pendingRequisitions'       => $pendingRequisitions,
            'pendingUserApprovals'      => $pendingUserApprovals,
            'totalPendingNotifications' => $totalPendingNotifications,
            'period'                    => $periodKey,
            'periodLabel'               => $periodLabel,
            'startDate'                 => $startDate ? $startDate->format('Y-m-d') : '',
            'endDate'                   => $endDate ? $endDate->format('Y-m-d') : '',
            'allCourses'                => $allCourses,
            'programFilter'             => $programFilter,
            'activeSeason'              => $activeSeason,
            'seasonalBooks'             => $seasonalBooks,
            'seasonalConfig'            => $seasonalConfig,
        ]);
    }

    public function dashboard(Request $request)
    {
        return $this->index($request);
    }

    public function exportPDF(Request $request)
    {
        return $this->generalPDF($request);
    }

    public function generalPDF(Request $request)
    {
        $ctx = $this->periodContext($request);
        $startDate = $ctx['startDate'];
        $endDate = $ctx['endDate'];
        $options = $this->resolveReportOptions($request, 'general');

        $totalBooks = Book::sum('copies');
        $totalStudents = Student::count();
        $totalFaculty = Faculty::count();
        $totalBorrows = $this->applyUsagePeriodFilter(BookUsage::query(), $startDate, $endDate)->count();
        $totalReturns = $this->applyUsagePeriodFilter(
            BookUsage::where('status', 'completed'),
            $startDate,
            $endDate,
            'time_out'
        )->count();
        $archivedBooks = Book::where('status', 'archived')->count();

        $activeUsers = $this->countActiveBorrowers($startDate, $endDate);

        $courseBreakdown = Student::select('program', DB::raw('count(*) as total'))
            ->groupBy('program')
            ->orderByDesc('total')
            ->get();

        $mostUsedBooksByCourse = BookUsage::join('students', 'book_usages.student_id', '=', 'students.id')
            ->join('books', 'book_usages.book_id', '=', 'books.id')
            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
            ->select('students.program', 'books.title', DB::raw('count(*) as borrow_count'))
            ->groupBy('students.program', 'books.title')
            ->orderBy('students.program')
            ->orderByDesc('borrow_count')
            ->get()
            ->groupBy('program');

        $mostBorrowedBooks = BookUsage::select('book_id', DB::raw('count(*) as total'))
            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
            ->groupBy('book_id')
            ->with('book')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

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

        $neverBorrowedCount = Book::where('status', '!=', 'archived')
            ->where('is_condemned', false)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('book_usages')
                  ->whereColumn('book_usages.book_id', 'books.id');
            })->count();

        $currentlyOnLoan = BookUsage::where('status', 'active')->count();

        $collectionUsage = BookUsage::join('books', 'books.id', '=', 'book_usages.book_id')
            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
            ->select('books.collection', DB::raw('count(*) as total'))
            ->groupBy('books.collection')
            ->orderByDesc('total')
            ->get();

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

        $borrowTrend = BookUsage::select(
            DB::raw('DATE(time_in) as day'),
            DB::raw('count(*) as total')
        )
            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $newAcquisitions = Book::when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
            ->where('status', '!=', 'archived')
            ->where('is_condemned', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $data = compact(
            'totalBooks',
            'totalStudents',
            'totalFaculty',
            'totalBorrows',
            'totalReturns',
            'archivedBooks',
            'activeUsers',
            'courseBreakdown',
            'mostUsedBooksByCourse',
            'mostBorrowedBooks',
            'leastBorrowedBooks',
            'neverBorrowedCount',
            'currentlyOnLoan',
            'collectionUsage',
            'topSubjects',
            'borrowTrend',
            'newAcquisitions',
            'options'
        );
        $data['generated_at'] = now();
        $data['periodLabel'] = $ctx['periodLabel'];

        $pdf = Pdf::loadView('reports.general', $data);

        return $pdf->download('general_report.pdf');
    }

    public function circulationPDF(Request $request)
    {
        $ctx = $this->periodContext($request);
        $startDate = $ctx['startDate'];
        $endDate = $ctx['endDate'];
        $options = $this->resolveReportOptions($request, 'circulation');

        $totalBorrows = $this->applyUsagePeriodFilter(BookUsage::query(), $startDate, $endDate)->count();
        $totalReturns = $this->applyUsagePeriodFilter(
            BookUsage::where('status', 'completed'), $startDate, $endDate, 'time_out'
        )->count();
        $currentlyOnLoan = BookUsage::where('status', 'active')->count();

        $mostBorrowedBooks = BookUsage::select('book_id', DB::raw('count(*) as total'))
            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
            ->groupBy('book_id')
            ->with('book')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

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
            ->limit(20)
            ->get();

        $neverBorrowedCount = Book::where('status', '!=', 'archived')
            ->where('is_condemned', false)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('book_usages')
                  ->whereColumn('book_usages.book_id', 'books.id');
            })->count();

        $collectionUsage = BookUsage::join('books', 'books.id', '=', 'book_usages.book_id')
            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
            ->select('books.collection', DB::raw('count(*) as total'))
            ->groupBy('books.collection')
            ->orderByDesc('total')
            ->get();

        $borrowTrend = BookUsage::select(
            DB::raw('DATE(time_in) as day'),
            DB::raw('count(*) as total')
        )
            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $pdf = Pdf::loadView('reports.circulation', [
            'totalBorrows'       => $totalBorrows,
            'totalReturns'       => $totalReturns,
            'currentlyOnLoan'    => $currentlyOnLoan,
            'mostBorrowedBooks'  => $mostBorrowedBooks,
            'leastBorrowedBooks' => $leastBorrowedBooks,
            'neverBorrowedCount' => $neverBorrowedCount,
            'collectionUsage'    => $collectionUsage,
            'borrowTrend'        => $borrowTrend,
            'periodLabel'        => $ctx['periodLabel'],
            'generated_at'       => now(),
            'options'            => $options,
        ]);

        return $pdf->download('circulation_report.pdf');
    }

    public function studentsPDF(Request $request)
    {
        $ctx = $this->periodContext($request);
        $startDate = $ctx['startDate'];
        $endDate = $ctx['endDate'];
        $options = $this->resolveReportOptions($request, 'students');

        $statusFilter = $options['status'] ?? $request->query('status');
        $programFilter = $options['program'] ?? $request->query('program');

        $query = BookUsage::with(['student', 'book'])
            ->whereNotNull('student_id')
            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate));

        if (!empty($options['custom_options_applied'])) {
            if (!empty($options['active_loans']) && empty($options['completed_returns'])) {
                $query->where('status', 'active');
            } elseif (!empty($options['completed_returns']) && empty($options['active_loans'])) {
                $query->where('status', 'completed');
            }
        } elseif ($statusFilter === 'active') {
            $query->where('status', 'active');
        } elseif ($statusFilter === 'completed') {
            $query->where('status', 'completed');
        }

        if ($programFilter) {
            $query->whereHas('student', fn($q) => $q->where('program', $programFilter));
        }

        $records = $query->orderByDesc('time_in')->get();

        $courseBreakdown = Student::select('program', DB::raw('count(*) as total'))
            ->groupBy('program')
            ->orderByDesc('total')
            ->get();

        $pdf = Pdf::loadView('reports.students', [
            'records'         => $records,
            'courseBreakdown' => $courseBreakdown,
            'periodLabel'     => $ctx['periodLabel'],
            'options'         => $options,
        ]);

        return $pdf->download('students_report.pdf');
    }

    public function facultyPDF(Request $request)
    {
        $ctx = $this->periodContext($request);
        $startDate = $ctx['startDate'];
        $endDate = $ctx['endDate'];
        $options = $this->resolveReportOptions($request, 'faculty');

        $statusFilter = $options['status'] ?? $request->query('status');

        $query = BookUsage::with(['faculty', 'book'])
            ->whereNotNull('faculty_id')
            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate));

        if (!empty($options['custom_options_applied'])) {
            if (!empty($options['active_loans']) && empty($options['completed_returns'])) {
                $query->where('status', 'active');
            } elseif (!empty($options['completed_returns']) && empty($options['active_loans'])) {
                $query->where('status', 'completed');
            }
        } elseif ($statusFilter === 'active') {
            $query->where('status', 'active');
        } elseif ($statusFilter === 'completed') {
            $query->where('status', 'completed');
        }

        $records = $query->orderByDesc('time_in')->get();

        $departmentBreakdown = Faculty::select('department', DB::raw('count(*) as total'))
            ->groupBy('department')
            ->orderByDesc('total')
            ->get();

        $pdf = Pdf::loadView('reports.faculty', [
            'records'             => $records,
            'departmentBreakdown' => $departmentBreakdown,
            'periodLabel'         => $ctx['periodLabel'],
            'options'             => $options,
        ]);

        return $pdf->download('faculty_report.pdf');
    }

    public function acquiredPDF(Request $request)
    {
        $ctx = $this->periodContext($request);
        $startDate = $ctx['startDate'];
        $endDate = $ctx['endDate'];
        $options = $this->resolveReportOptions($request, 'acquired');

        $donationFilter = !empty($options['donations_only']) || ($options['donation_only'] ?? $request->query('donation_only'));
        $collectionFilter = $options['collection'] ?? $request->query('collection');

        $query = Book::when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate));

        if ($donationFilter) {
            $query->where('is_donation', true);
        }
        if ($collectionFilter) {
            $query->where('collection', $collectionFilter);
        }

        $books = $query->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('reports.acquired', [
            'books'       => $books,
            'periodLabel' => $ctx['periodLabel'],
            'options'     => $options,
        ]);

        return $pdf->download('acquired_books.pdf');
    }

    public function condemnedPDF(Request $request)
    {
        $ctx = $this->periodContext($request);
        $startDate = $ctx['startDate'];
        $endDate = $ctx['endDate'];
        $options = $this->resolveReportOptions($request, 'condemned');

        $books = Book::where('is_condemned', true)
            ->when($startDate, fn ($q) => $q->where('condemned_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('condemned_at', '<=', $endDate))
            ->orderByDesc('condemned_at')
            ->get();

        $pdf = Pdf::loadView('reports.condemned', [
            'books'        => $books,
            'periodLabel'  => $ctx['periodLabel'],
            'generated_at' => now(),
            'options'      => $options,
        ]);

        return $pdf->download('condemned_books.pdf');
    }

    public function bookRecommendationsPDF(AISuggestionService $ai)
    {
        try {
            $aiSuggestions = collect($ai->getPurchaseSuggestions(15));
        } catch (\Exception $e) {
            \Log::error('AI suggestions failed for PDF: ' . $e->getMessage());
            $aiSuggestions = collect();
        }

        $pdf = Pdf::loadView('reports.book-recommendations', [
            'aiSuggestions' => $aiSuggestions,
            'generated_at'  => now('Asia/Manila'),
        ]);

        return $pdf->download('book-recommendations-' . now()->format('Y-m-d') . '.pdf');
    }

    public function seasonalPDF(Request $request)
    {
        $activeSeason = SeasonalBookService::getActiveSeason();
        $suggestedBooks = SeasonalBookService::getSuggestedBooks(20);

        $pdf = Pdf::loadView('reports.seasonal', [
            'season'         => $activeSeason,
            'suggestedBooks' => $suggestedBooks,
            'generated_at'   => now('Asia/Manila'),
        ]);

        return $pdf->download('seasonal-book-recommendations-' . now()->format('Y-m-d') . '.pdf');
    }

    public function dailyPDF()
    {
        $today = now('Asia/Manila')->startOfDay();

        $borrowsList = BookUsage::with(['book', 'student', 'faculty'])
            ->where('time_in', '>=', $today)
            ->orderByDesc('time_in')
            ->get();

        $returnsList = BookUsage::with(['book', 'student', 'faculty'])
            ->where('time_out', '>=', $today)
            ->where('status', 'completed')
            ->orderByDesc('time_out')
            ->get();

        $pdf = Pdf::loadView('reports.daily', [
            'borrows_today'   => $borrowsList->count(),
            'returns_today'   => $returnsList->count(),
            'new_books_today' => Book::where('created_at', '>=', $today)->count(),
            'new_donations'   => Book::where('created_at', '>=', $today)->where('is_donation', true)->count(),
            'penalties_today' => \App\Models\LibraryPenalty::where('created_at', '>=', $today)->count(),
            'active_borrows'  => BookUsage::where('status', 'active')->count(),
            'borrows_list'    => $borrowsList,
            'returns_list'    => $returnsList,
            'generated_at'    => now('Asia/Manila'),
        ]);

        return $pdf->download('daily-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export raw report file in CSV, Excel (.xlsx/.xml), or JSON format.
     */
    public function exportRaw(Request $request)
    {
        $type = $request->query('type', 'general');
        $format = $request->query('format', 'csv');
        $ctx = $this->periodContext($request);
        $startDate = $ctx['startDate'];
        $endDate = $ctx['endDate'];
        $periodLabel = $ctx['periodLabel'];
        $options = $this->resolveReportOptions($request, $type);
        $isCustom = !empty($options['custom_options_applied']);

        $headers = [];
        $rows = [];
        $reportTitle = 'Report';

        switch ($type) {
            case 'general':
                $reportTitle = "General Statistics Report";
                if ($isCustom) {
                    $headers = ['Section', 'Item / Indicator', 'Metric / Count', 'Additional Information'];

                    if (!empty($options['summary'])) {
                        $rows[] = ['Overview & KPIs', 'Total Books (Copies)', Book::sum('copies'), 'Catalog Inventory'];
                        $rows[] = ['Overview & KPIs', 'Registered Students', Student::count(), 'Active & Approved'];
                        $rows[] = ['Overview & KPIs', 'Registered Faculty', Faculty::count(), 'Active & Approved'];
                        $rows[] = ['Overview & KPIs', 'Total Borrows', $this->applyUsagePeriodFilter(BookUsage::query(), $startDate, $endDate)->count(), $periodLabel];
                        $rows[] = ['Overview & KPIs', 'Total Returns', $this->applyUsagePeriodFilter(BookUsage::where('status', 'completed'), $startDate, $endDate, 'time_out')->count(), $periodLabel];
                        $rows[] = ['Overview & KPIs', 'Currently on Loan', BookUsage::where('status', 'active')->count(), 'Real-time'];
                        $rows[] = ['Overview & KPIs', 'Active Borrowers', $this->countActiveBorrowers($startDate, $endDate), 'Unique patrons'];
                        $rows[] = ['Overview & KPIs', 'Archived Books', Book::where('status', 'archived')->count(), 'Archived'];
                        $rows[] = ['Overview & KPIs', 'Never Borrowed Books', Book::where('status', '!=', 'archived')->where('is_condemned', false)->whereNotExists(fn($q) => $q->select(DB::raw(1))->from('book_usages')->whereColumn('book_usages.book_id', 'books.id'))->count(), 'Catalog Review'];
                    }
                    if (!empty($options['collection_dist'])) {
                        $colls = BookUsage::join('books', 'books.id', '=', 'book_usages.book_id')
                            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
                            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
                            ->select('books.collection', DB::raw('count(*) as total'))
                            ->groupBy('books.collection')
                            ->orderByDesc('total')
                            ->get();
                        foreach ($colls as $c) {
                            $rows[] = ['Collection Distribution', $c->collection ?: 'General / Uncategorised', $c->total, 'Circulation share'];
                        }
                    }
                    if (!empty($options['most_borrowed'])) {
                        $most = BookUsage::select('book_id', DB::raw('count(*) as total'))
                            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
                            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
                            ->groupBy('book_id')->with('book')->orderByDesc('total')->limit(10)->get();
                        foreach ($most as $i => $b) {
                            $rows[] = ['Most Borrowed Titles', ($b->book->title ?? 'Unknown'), $b->total . ' borrows', 'Acc: ' . ($b->book->accession_number ?? '—') . ' | ' . ($b->book->author ?? '—')];
                        }
                    }
                    if (!empty($options['least_borrowed'])) {
                        $least = Book::leftJoin('book_usages', function ($join) use ($startDate, $endDate) {
                            $join->on('books.id', '=', 'book_usages.book_id');
                            if ($startDate) $join->where('book_usages.time_in', '>=', $startDate);
                            if ($endDate) $join->where('book_usages.time_in', '<=', $endDate);
                        })->where('books.status', '!=', 'archived')->where('books.is_condemned', false)
                        ->select('books.id', 'books.title', 'books.author', 'books.accession_number', DB::raw('count(book_usages.id) as total'))
                        ->groupBy('books.id', 'books.title', 'books.author', 'books.accession_number')
                        ->orderBy('total')->limit(10)->get();
                        foreach ($least as $b) {
                            $rows[] = ['Least Borrowed / Unused', $b->title, $b->total == 0 ? 'Never' : $b->total . ' borrows', 'Acc: ' . ($b->accession_number ?? '—') . ' | ' . ($b->author ?? '—')];
                        }
                    }
                    if (!empty($options['top_subjects'])) {
                        $rawSubs = BookUsage::join('books', 'books.id', '=', 'book_usages.book_id')
                            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
                            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
                            ->whereNotNull('books.subject')
                            ->select('books.subject', DB::raw('count(*) as total'))
                            ->groupBy('books.subject')->get();
                        $subCounts = [];
                        foreach ($rawSubs as $r) {
                            foreach (array_map('trim', explode(',', $r->subject)) as $p) {
                                if ($p !== '') $subCounts[$p] = ($subCounts[$p] ?? 0) + $r->total;
                            }
                        }
                        arsort($subCounts);
                        foreach (array_slice($subCounts, 0, 10, true) as $subj => $cnt) {
                            $rows[] = ['Top Subjects / Categories', $subj, $cnt . ' borrows', 'Subject classification'];
                        }
                    }
                    if (!empty($options['program_usage'])) {
                        $courses = Student::select('program', DB::raw('count(*) as total'))->groupBy('program')->orderByDesc('total')->get();
                        foreach ($courses as $c) {
                            $rows[] = ['Program / Course Usage', $c->program ?: 'Unspecified', $c->total . ' students', 'Registered patron count'];
                        }
                    }
                    if (!empty($options['borrowing_trends'])) {
                        $trends = BookUsage::select(DB::raw('DATE(time_in) as day'), DB::raw('count(*) as total'))
                            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
                            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
                            ->groupBy('day')->orderBy('day')->get();
                        foreach ($trends as $t) {
                            $rows[] = ['Borrowing Trends Timeline', $t->day, $t->total . ' borrows', 'Daily borrow volume'];
                        }
                    }
                    if (!empty($options['acquisitions'])) {
                        $acqs = Book::when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
                            ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
                            ->where('status', '!=', 'archived')->where('is_condemned', false)
                            ->orderByDesc('created_at')->limit(10)->get();
                        foreach ($acqs as $a) {
                            $rows[] = ['New Acquisitions', $a->title, 'Acc: ' . ($a->accession_number ?? '—'), 'Author: ' . ($a->author ?? '—') . ' | Added: ' . ($a->created_at ? $a->created_at->format('Y-m-d') : '—')];
                        }
                    }
                } else {
                    $headers = ['Category', 'Metric', 'Value', 'Notes'];
                    $rows[] = ['Overview', 'Total Books (Copies)', Book::sum('copies'), 'Catalog Inventory'];
                    $rows[] = ['Overview', 'Registered Students', Student::count(), 'Active & Approved'];
                    $rows[] = ['Overview', 'Registered Faculty', Faculty::count(), 'Active & Approved'];
                    $rows[] = ['Circulation', 'Total Borrows', $this->applyUsagePeriodFilter(BookUsage::query(), $startDate, $endDate)->count(), $periodLabel];
                    $rows[] = ['Circulation', 'Total Returns', $this->applyUsagePeriodFilter(BookUsage::where('status', 'completed'), $startDate, $endDate, 'time_out')->count(), $periodLabel];
                    $rows[] = ['Circulation', 'Currently on Loan', BookUsage::where('status', 'active')->count(), 'Real-time'];
                    $rows[] = ['Circulation', 'Never Borrowed Books', Book::where('status', '!=', 'archived')->where('is_condemned', false)->whereNotExists(fn($q) => $q->select(DB::raw(1))->from('book_usages')->whereColumn('book_usages.book_id', 'books.id'))->count(), 'Catalog Review'];
                    $rows[] = ['Catalog', 'Archived Books', Book::where('status', 'archived')->count(), 'Archived'];
                    $rows[] = ['Catalog', 'Condemned Books', Book::where('is_condemned', true)->count(), 'Condemned'];
                }
                break;

            case 'circulation':
                $reportTitle = "Circulation & Usage Report";
                if ($isCustom) {
                    $headers = ['Section', 'Accession / Date', 'Title / Indicator', 'Author / Category', 'Borrows / Count'];
                    if (!empty($options['summary'])) {
                        $rows[] = ['Summary KPIs', 'Total Borrows', $this->applyUsagePeriodFilter(BookUsage::query(), $startDate, $endDate)->count(), 'All Loans', 'Period Total'];
                        $rows[] = ['Summary KPIs', 'Total Returns', $this->applyUsagePeriodFilter(BookUsage::where('status', 'completed'), $startDate, $endDate, 'time_out')->count(), 'Completed', 'Period Total'];
                        $rows[] = ['Summary KPIs', 'Currently On Loan', BookUsage::where('status', 'active')->count(), 'Active', 'Real-time'];
                        $rows[] = ['Summary KPIs', 'Never Borrowed', Book::where('status', '!=', 'archived')->where('is_condemned', false)->whereNotExists(fn($q) => $q->select(DB::raw(1))->from('book_usages')->whereColumn('book_usages.book_id', 'books.id'))->count(), 'Uncirculated', 'Catalog Total'];
                    }
                    if (!empty($options['collection_dist'])) {
                        $colls = BookUsage::join('books', 'books.id', '=', 'book_usages.book_id')
                            ->when($startDate, fn ($q) => $q->where('book_usages.time_in', '>=', $startDate))
                            ->when($endDate, fn ($q) => $q->where('book_usages.time_in', '<=', $endDate))
                            ->select('books.collection', DB::raw('count(*) as total'))->groupBy('books.collection')->orderByDesc('total')->get();
                        foreach ($colls as $c) {
                            $rows[] = ['Collection Distribution', '—', $c->collection ?: 'Uncategorised', 'Collection Share', $c->total];
                        }
                    }
                    if (!empty($options['borrowing_trends'])) {
                        $trends = BookUsage::select(DB::raw('DATE(time_in) as day'), DB::raw('count(*) as total'))
                            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
                            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
                            ->groupBy('day')->orderBy('day')->get();
                        foreach ($trends as $t) {
                            $rows[] = ['Daily Borrow Trend', $t->day, 'Daily Total', 'Circulation', $t->total];
                        }
                    }
                    if (!empty($options['most_borrowed'])) {
                        $most = BookUsage::select('book_id', DB::raw('count(*) as total'))
                            ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
                            ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
                            ->groupBy('book_id')->with('book')->orderByDesc('total')->limit(20)->get();
                        foreach ($most as $i => $b) {
                            $rows[] = ['Most Borrowed (Top 20)', $b->book->accession_number ?? '—', $b->book->title ?? 'Unknown', $b->book->author ?? '—', $b->total];
                        }
                    }
                    if (!empty($options['least_borrowed'])) {
                        $least = Book::leftJoin('book_usages', function ($join) use ($startDate, $endDate) {
                            $join->on('books.id', '=', 'book_usages.book_id');
                            if ($startDate) $join->where('book_usages.time_in', '>=', $startDate);
                            if ($endDate) $join->where('book_usages.time_in', '<=', $endDate);
                        })->where('books.status', '!=', 'archived')->where('books.is_condemned', false)
                        ->select('books.id', 'books.title', 'books.author', 'books.collection', 'books.accession_number', DB::raw('count(book_usages.id) as total'))
                        ->groupBy('books.id', 'books.title', 'books.author', 'books.collection', 'books.accession_number')
                        ->orderBy('total')->limit(20)->get();
                        foreach ($least as $b) {
                            $rows[] = ['Least Borrowed (Bottom 20)', $b->accession_number ?? '—', $b->title, $b->author ?? '—', $b->total == 0 ? 'Never' : $b->total];
                        }
                    }
                } else {
                    $headers = ['#', 'Accession Number', 'Title', 'Author', 'Collection', 'Borrows Count'];
                    $books = BookUsage::select('book_id', DB::raw('count(*) as total'))
                        ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
                        ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate))
                        ->groupBy('book_id')
                        ->with('book')
                        ->orderByDesc('total')
                        ->get();
                    foreach ($books as $i => $b) {
                        $rows[] = [
                            $i + 1,
                            $b->book->accession_number ?? 'N/A',
                            $b->book->title ?? 'Unknown',
                            $b->book->author ?? 'Unknown',
                            $b->book->collection ?? 'General',
                            $b->total,
                        ];
                    }
                }
                break;

            case 'students':
                $reportTitle = "Student Borrowing Records";
                $headers = ['Transaction ID', 'Student Number', 'Student Name', 'Program', 'Book Title', 'Accession No.', 'Time In', 'Time Out', 'Status'];
                $query = BookUsage::with(['student', 'book'])
                    ->whereNotNull('student_id')
                    ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
                    ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate));

                if ($isCustom) {
                    if (!empty($options['active_loans']) && empty($options['completed_returns'])) {
                        $query->where('status', 'active');
                    } elseif (!empty($options['completed_returns']) && empty($options['active_loans'])) {
                        $query->where('status', 'completed');
                    }
                }

                $usages = $query->orderByDesc('time_in')->get();
                foreach ($usages as $u) {
                    $rows[] = [
                        $u->id,
                        $u->student->student_number ?? 'N/A',
                        $u->student ? ($u->student->first_name . ' ' . $u->student->last_name) : 'N/A',
                        $u->student->program ?? 'N/A',
                        $u->book->title ?? 'N/A',
                        $u->book->accession_number ?? 'N/A',
                        $u->time_in ? $u->time_in->format('Y-m-d H:i') : 'N/A',
                        $u->time_out ? $u->time_out->format('Y-m-d H:i') : ($u->status === 'active' ? 'Not Returned' : 'N/A'),
                        ucfirst($u->status ?? 'unknown'),
                    ];
                }
                break;

            case 'faculty':
                $reportTitle = "Faculty Borrowing Records";
                $headers = ['Transaction ID', 'Employee ID', 'Faculty Name', 'Department', 'Book Title', 'Accession No.', 'Time In', 'Time Out', 'Status'];
                $query = BookUsage::with(['faculty', 'book'])
                    ->whereNotNull('faculty_id')
                    ->when($startDate, fn ($q) => $q->where('time_in', '>=', $startDate))
                    ->when($endDate, fn ($q) => $q->where('time_in', '<=', $endDate));

                if ($isCustom) {
                    if (!empty($options['active_loans']) && empty($options['completed_returns'])) {
                        $query->where('status', 'active');
                    } elseif (!empty($options['completed_returns']) && empty($options['active_loans'])) {
                        $query->where('status', 'completed');
                    }
                }

                $usages = $query->orderByDesc('time_in')->get();
                foreach ($usages as $u) {
                    $rows[] = [
                        $u->id,
                        $u->faculty->employee_id ?? 'N/A',
                        $u->faculty ? ($u->faculty->first_name . ' ' . $u->faculty->last_name) : 'N/A',
                        $u->faculty->department ?? 'Faculty',
                        $u->book->title ?? 'N/A',
                        $u->book->accession_number ?? 'N/A',
                        $u->time_in ? $u->time_in->format('Y-m-d H:i') : 'N/A',
                        $u->time_out ? $u->time_out->format('Y-m-d H:i') : ($u->status === 'active' ? 'Not Returned' : 'N/A'),
                        ucfirst($u->status ?? 'unknown'),
                    ];
                }
                break;

            case 'acquired':
                $reportTitle = "Acquired Books Report";
                $query = Book::when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
                    ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate));

                if ($isCustom && !empty($options['donations_only'])) {
                    $query->where('is_donation', true);
                }

                $books = $query->orderByDesc('created_at')->get();

                if ($isCustom) {
                    $headers = ['Title', 'Author'];
                    if (!empty($options['accession'])) array_unshift($headers, 'Accession Number');
                    if (!empty($options['publisher_info'])) {
                        $headers[] = 'Publisher';
                        $headers[] = 'Publication Year';
                    }
                    if (!empty($options['date_acquired'])) {
                        $headers[] = 'Date Acquired';
                    }

                    foreach ($books as $b) {
                        $row = [$b->title, $b->author ?? 'Unknown'];
                        if (!empty($options['accession'])) array_unshift($row, $b->accession_number ?? 'N/A');
                        if (!empty($options['publisher_info'])) {
                            $row[] = $b->publisher ?? 'N/A';
                            $row[] = $b->publication_year ?? 'N/A';
                        }
                        if (!empty($options['date_acquired'])) {
                            $row[] = $b->created_at ? $b->created_at->format('Y-m-d') : 'N/A';
                        }
                        $rows[] = $row;
                    }
                } else {
                    $headers = ['Accession Number', 'Title', 'Author', 'Collection', 'Publisher', 'Publication Year', 'Copies', 'Date Acquired'];
                    foreach ($books as $b) {
                        $rows[] = [
                            $b->accession_number ?? 'N/A',
                            $b->title,
                            $b->author ?? 'Unknown',
                            $b->collection ?? 'General',
                            $b->publisher ?? 'N/A',
                            $b->publication_year ?? 'N/A',
                            $b->copies ?? 1,
                            $b->created_at ? $b->created_at->format('Y-m-d') : 'N/A',
                        ];
                    }
                }
                break;

            case 'condemned':
                $reportTitle = "Condemned Books Report";
                $books = Book::where('is_condemned', true)
                    ->when($startDate, fn ($q) => $q->where('condemned_at', '>=', $startDate))
                    ->when($endDate, fn ($q) => $q->where('condemned_at', '<=', $endDate))
                    ->orderByDesc('condemned_at')
                    ->get();

                if ($isCustom) {
                    $headers = ['Accession Number', 'Title', 'Author', 'Condemned Date'];
                    if (!empty($options['call_number'])) $headers[] = 'Call Number';
                    if (!empty($options['condemned_by'])) $headers[] = 'Condemned By';
                    if (!empty($options['reason'])) $headers[] = 'Reason';
                    if (!empty($options['last_borrower'])) $headers[] = 'Last Borrower';

                    foreach ($books as $b) {
                        $lastBorrower = is_array($b->last_borrower_info) ? ($b->last_borrower_info['name'] ?? 'N/A') : ($b->last_borrower_info ?: 'N/A');
                        $row = [
                            $b->accession_number ?? 'N/A',
                            $b->title,
                            $b->author ?? 'Unknown',
                            $b->condemned_at ? Carbon::parse($b->condemned_at)->format('Y-m-d') : 'N/A',
                        ];
                        if (!empty($options['call_number'])) $row[] = $b->loc_number ?? $b->call_number ?? 'N/A';
                        if (!empty($options['condemned_by'])) $row[] = $b->condemned_by ?? 'Admin';
                        if (!empty($options['reason'])) $row[] = $b->condemnation_reason ?? 'Damaged/Obsolete';
                        if (!empty($options['last_borrower'])) $row[] = $lastBorrower;
                        $rows[] = $row;
                    }
                } else {
                    $headers = ['Accession Number', 'Title', 'Author', 'Call Number', 'Condemned Date', 'Condemned By', 'Reason', 'Last Borrower'];
                    foreach ($books as $b) {
                        $lastBorrower = is_array($b->last_borrower_info) ? ($b->last_borrower_info['name'] ?? 'N/A') : ($b->last_borrower_info ?: 'N/A');
                        $rows[] = [
                            $b->accession_number ?? 'N/A',
                            $b->title,
                            $b->author ?? 'Unknown',
                            $b->loc_number ?? $b->call_number ?? 'N/A',
                            $b->condemned_at ? Carbon::parse($b->condemned_at)->format('Y-m-d') : 'N/A',
                            $b->condemned_by ?? 'Admin',
                            $b->condemnation_reason ?? 'Damaged/Obsolete',
                            $lastBorrower,
                        ];
                    }
                }
                break;

            case 'seasonal':
                $activeSeason = SeasonalBookService::getActiveSeason();
                $suggested = SeasonalBookService::getSuggestedBooks(50);
                $reportTitle = "Seasonal Books Curation ({$activeSeason['name']})";
                $headers = ['#', 'Accession Number', 'Title', 'Author', 'Collection', 'Subject', 'Copies Available', 'Seasonal Score', 'Recommendation Reason'];
                foreach ($suggested as $i => $sb) {
                    $rows[] = [
                        $i + 1,
                        $sb->accession_number ?? 'N/A',
                        $sb->title,
                        $sb->author ?? 'Unknown',
                        $sb->collection ?? 'General',
                        $sb->subject ?? 'N/A',
                        $sb->copies ?? 0,
                        $sb->seasonal_score ?? 0,
                        $sb->seasonal_reason ?? 'Seasonal Theme',
                    ];
                }
                break;

            case 'daily':
            default:
                $today = now('Asia/Manila')->startOfDay();
                $reportTitle = "Daily Activity Report (" . now('Asia/Manila')->format('Y-m-d') . ")";
                $headers = ['Transaction Type', 'Patron Type', 'Patron Name', 'Book Title', 'Accession No.', 'Timestamp'];
                $borrows = BookUsage::with(['book', 'student', 'faculty'])->where('time_in', '>=', $today)->get();
                foreach ($borrows as $u) {
                    $patronType = $u->student ? 'Student' : ($u->faculty ? 'Faculty' : 'Unknown');
                    $patronName = $u->student ? ($u->student->first_name . ' ' . $u->student->last_name) : ($u->faculty ? ($u->faculty->first_name . ' ' . $u->faculty->last_name) : 'N/A');
                    $rows[] = ['Borrow', $patronType, $patronName, $u->book->title ?? 'N/A', $u->book->accession_number ?? 'N/A', $u->time_in ? $u->time_in->format('H:i:s') : 'N/A'];
                }
                $returns = BookUsage::with(['book', 'student', 'faculty'])->where('time_out', '>=', $today)->where('status', 'completed')->get();
                foreach ($returns as $u) {
                    $patronType = $u->student ? 'Student' : ($u->faculty ? 'Faculty' : 'Unknown');
                    $patronName = $u->student ? ($u->student->first_name . ' ' . $u->student->last_name) : ($u->faculty ? ($u->faculty->first_name . ' ' . $u->faculty->last_name) : 'N/A');
                    $rows[] = ['Return', $patronType, $patronName, $u->book->title ?? 'N/A', $u->book->accession_number ?? 'N/A', $u->time_out ? $u->time_out->format('H:i:s') : 'N/A'];
                }
                break;
        }

        return ReportExportService::export($reportTitle, $headers, $rows, $format, ['period' => $periodLabel]);
    }

    /**
     * Update Seasonal configuration (Auto vs Manual, and season dates).
     */
    public function updateSeasonalConfig(Request $request)
    {
        $request->validate([
            'mode'              => 'required|in:auto,manual',
            'manual_season_key' => 'nullable|string',
            'seasons'           => 'nullable|array',
        ]);

        $current = SeasonalBookService::getConfig();
        $current['mode'] = $request->mode;
        if ($request->filled('manual_season_key')) {
            $current['manual_season_key'] = $request->manual_season_key;
        }
        if ($request->has('seasons') && is_array($request->seasons)) {
            $current['seasons'] = array_replace_recursive($current['seasons'], $request->seasons);
        }

        SeasonalBookService::saveConfig($current);

        return response()->json([
            'success' => true,
            'message' => 'Seasonal settings updated successfully.',
            'active_season' => SeasonalBookService::getActiveSeason(),
        ]);
    }
}
