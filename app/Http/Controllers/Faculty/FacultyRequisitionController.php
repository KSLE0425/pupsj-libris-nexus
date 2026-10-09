<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\BookRequisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacultyRequisitionController extends Controller
{
    public function index()
    {
        $faculty = Auth::guard('faculty')->user();
        $requisitions = BookRequisition::where('faculty_id', $faculty->id)
            ->whereNull('student_id')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('faculty.requisitions.index', compact('requisitions'));
    }

    public function create()
    {
        $enabled = \App\Models\Setting::getValue('book_requests_enabled', '1') === '1';
        $disabledReason = \App\Models\Setting::getValue('book_requests_disabled_reason', 'Book requisitions are temporarily paused by the library administration.');
        return view('faculty.requisitions.create', compact('enabled', 'disabledReason'));
    }

    public function store(Request $request)
    {
        $enabled = \App\Models\Setting::getValue('book_requests_enabled', '1') === '1';
        if (!$enabled) {
            $reason = \App\Models\Setting::getValue('book_requests_disabled_reason', 'Book requisitions are temporarily paused.');
            return redirect()->route('faculty.requisitions.index')->with('error', $reason);
        }

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'author'        => 'nullable|string|max:255',
            'isbn'          => 'nullable|string|max:50',
            'publisher'     => 'nullable|string|max:255',
            'justification' => 'nullable|string|max:5000',
        ]);

        $faculty = Auth::guard('faculty')->user();

        BookRequisition::create([
            'student_id'    => null,
            'faculty_id'    => $faculty->id,
            'title'         => $data['title'],
            'isbn'          => $data['isbn'] ?? null,
            'author'        => $data['author'] ?? null,
            'publisher'     => $data['publisher'] ?? null,
            'justification' => $data['justification'] ?? null,
            'status'        => 'submitted',
        ]);

        return redirect()->route('faculty.requisitions.index')->with('success', 'Acquisition request submitted successfully. Estimated processing time is 1–2 weeks.');
    }
}
