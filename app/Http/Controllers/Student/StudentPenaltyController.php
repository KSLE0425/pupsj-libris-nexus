<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LibraryDamageReport;
use App\Models\LibraryPenalty;
use Illuminate\Support\Facades\Auth;

class StudentPenaltyController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $penalties = LibraryPenalty::with('book')
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $pendingTotal = LibraryPenalty::where('student_id', $student->id)
            ->where('status', 'pending')
            ->sum('amount');

        $suspendedUntil = $student->borrowing_suspended_until;
        $pendingDamageCount = LibraryDamageReport::where('student_id', $student->id)
            ->where('status', 'pending')
            ->count();

        return view('student.penalties.index', compact('penalties', 'pendingTotal', 'suspendedUntil', 'pendingDamageCount'));
    }
}
