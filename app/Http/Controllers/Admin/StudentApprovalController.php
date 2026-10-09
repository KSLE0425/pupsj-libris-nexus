<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Faculty;
use App\Mail\StudentApprovedMail;
use Illuminate\Support\Facades\Mail;

class StudentApprovalController extends Controller
{
 public function index()
{
    $students = Student::where('status', 'pending')->orderBy('created_at', 'desc')->get();
    $faculties = Faculty::where('status', 'pending')->orderBy('created_at', 'desc')->get();
    return view('admin.pending-students', compact('students', 'faculties'));
}
    public function approve($id)
    {
        $student = Student::findOrFail($id);
        $student->update(['status' => 'active']);

        // Send approval email
        Mail::to($student->email)->send(new StudentApprovedMail($student));

        return back()->with('message', 'Student approved and notified.');
    }

    public function reject($id)
    {
        $student = Student::findOrFail($id);
        $student->delete(); // or set status to 'rejected' if you add that

        return back()->with('message', 'Student rejected.');
    }
}