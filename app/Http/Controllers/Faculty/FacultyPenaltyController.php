<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\LibraryDamageReport;
use App\Models\LibraryPenalty;
use Illuminate\Support\Facades\Auth;

class FacultyPenaltyController extends Controller
{
    public function index()
    {
        $faculty = Auth::guard('faculty')->user();
        $penalties = LibraryPenalty::with('book')
            ->where('faculty_id', $faculty->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $pendingTotal = LibraryPenalty::where('faculty_id', $faculty->id)
            ->where('status', 'pending')
            ->sum('amount');

        $suspendedUntil = $faculty->borrowing_suspended_until;
        $pendingDamageCount = LibraryDamageReport::where('faculty_id', $faculty->id)
            ->where('status', 'pending')
            ->count();

        return view('faculty.penalties.index', compact('penalties', 'pendingTotal', 'suspendedUntil', 'pendingDamageCount'));
    }
}
