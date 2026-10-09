<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BookRequisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentRequisitionController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $requisitions = BookRequisition::where('student_id', $student->id)
            ->whereNull('faculty_id')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('student.requisitions.index', compact('requisitions'));
    }

    public function create()
    {
        $enabled = \App\Models\Setting::getValue('book_requests_enabled', '1') === '1';
        $disabledReason = \App\Models\Setting::getValue('book_requests_disabled_reason', 'Book requisitions are temporarily paused by the library administration.');
        return view('student.requisitions.create', compact('enabled', 'disabledReason'));
    }

    public function store(Request $request)
    {
        $enabled = \App\Models\Setting::getValue('book_requests_enabled', '1') === '1';
        if (!$enabled) {
            $reason = \App\Models\Setting::getValue('book_requests_disabled_reason', 'Book requisitions are temporarily paused.');
            return redirect()->route('student.requisitions.index')->with('error', $reason);
        }

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'author'        => 'nullable|string|max:255',
            'isbn'          => 'nullable|string|max:50',
            'publisher'     => 'nullable|string|max:255',
            'justification' => 'nullable|string|max:5000',
        ]);

        $student = Auth::guard('student')->user();

        BookRequisition::create([
            'student_id'    => $student->id,
            'faculty_id'    => null,
            'title'         => $data['title'],
            'isbn'          => $data['isbn'] ?? null,
            'author'        => $data['author'] ?? null,
            'publisher'     => $data['publisher'] ?? null,
            'justification' => $data['justification'] ?? null,
            'status'        => 'submitted',
        ]);

        return redirect()->route('student.requisitions.index')->with('success', 'Acquisition request submitted successfully. Estimated processing time is 1–2 weeks.');
    }
}
