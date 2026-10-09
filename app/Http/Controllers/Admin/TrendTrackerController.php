<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\TrendTrackerService;
use Illuminate\Http\Request;

class TrendTrackerController extends Controller
{
    protected $trendTracker;

    public function __construct(TrendTrackerService $trendTracker)
    {
        $this->trendTracker = $trendTracker;
    }

    public function index(Request $request)
    {
        $course = $request->get('course', 'all');
        $statistics = $this->trendTracker->getStatistics();
        $trends = $this->trendTracker->getTrendsByCourse($course);
        $topBooks = $this->trendTracker->getTopBooksByCourse($course);

        // Get unique courses for filter dropdown
        $courses = Student::select('program')
            ->distinct()
            ->orderBy('program')
            ->pluck('program');

        return view('admin.trend-tracker', compact(
            'statistics',
            'trends',
            'topBooks',
            'courses',
            'course'
        ));
    }
}
