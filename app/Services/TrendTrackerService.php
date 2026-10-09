<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookUsage;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class TrendTrackerService
{
    /**
     * Get trending subjects/keywords per course
     */
    public function getTrendsByCourse($course = null, $limit = 10)
    {
        $query = BookUsage::join('students', 'book_usages.student_id', '=', 'students.id')
            ->join('books', 'book_usages.book_id', '=', 'books.id')
            ->where('book_usages.status', 'completed');

        if ($course && $course !== 'all') {
            $query->where('students.program', $course);
        }

        // Analyze by subject
        $subjects = $query->select(
            'students.program',
            'books.subject',
            DB::raw('COUNT(*) as borrow_count')
        )
            ->whereNotNull('books.subject')
            ->groupBy('students.program', 'books.subject')
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->get();

        // Analyze by keywords (if keywords are stored as comma-separated)
        $keywords = $query->select(
            'students.program',
            'books.keywords',
            DB::raw('COUNT(*) as borrow_count')
        )
            ->whereNotNull('books.keywords')
            ->groupBy('students.program', 'books.keywords')
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->get();

        return [
            'by_subject' => $subjects,
            'by_keyword' => $keywords,
        ];
    }

    /**
     * Get top borrowed books per course
     */
    public function getTopBooksByCourse($course = null, $limit = 10)
    {
        $query = BookUsage::join('students', 'book_usages.student_id', '=', 'students.id')
            ->join('books', 'book_usages.book_id', '=', 'books.id')
            ->where('book_usages.status', 'completed');

        if ($course && $course !== 'all') {
            $query->where('students.program', $course);
        }

        return $query->select(
            'students.program',
            'books.id',
            'books.title',
            'books.author',
            'books.subject',
            DB::raw('COUNT(*) as borrow_count')
        )
            ->groupBy('students.program', 'books.id', 'books.title', 'books.author', 'books.subject')
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get borrowing statistics for dashboard
     */
    public function getStatistics()
    {
        $totalBorrows = BookUsage::where('status', 'completed')->count();
        $activeBorrows = BookUsage::where('status', 'active')->count();
        $totalStudents = Student::count();
        $totalBooks = Book::sum('copies');

        // Borrows by course
        $borrowsByCourse = BookUsage::join('students', 'book_usages.student_id', '=', 'students.id')
            ->select('students.program', DB::raw('COUNT(*) as total'))
            ->groupBy('students.program')
            ->orderByDesc('total')
            ->get();

        // Monthly trend
        $monthlyTrend = BookUsage::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();

        return [
            'total_borrows' => $totalBorrows,
            'active_borrows' => $activeBorrows,
            'total_students' => $totalStudents,
            'total_books' => $totalBooks,
            'borrows_by_course' => $borrowsByCourse,
            'monthly_trend' => $monthlyTrend,
        ];
    }
}
