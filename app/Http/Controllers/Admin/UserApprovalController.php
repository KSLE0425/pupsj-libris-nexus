<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Faculty;
use App\Mail\StudentApprovedMail;
use App\Mail\FacultyApprovedMail;
use App\Mail\RegistrationRejectedMail;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Mail;

class UserApprovalController extends Controller
{
    public function index()
    {
        $students = Student::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        $faculties = Faculty::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        return view('admin.pending-users', compact('students', 'faculties'));
    }

    public function approveStudent($id)
    {
        $student = Student::findOrFail($id);
        $student->update(['status' => 'active']);
        
        AuditLogger::log('student_approved', 'Approved registration request for student ' . $student->first_name . ' ' . $student->last_name . ' (' . ($student->student_number ?? $student->email) . ')', [
            'affected_user' => $student->first_name . ' ' . $student->last_name,
            'student_id'    => $student->id,
            'student_number'=> $student->student_number,
            'email'         => $student->email,
        ], 'users');

        Mail::to($student->email)->send(new StudentApprovedMail($student));
        return back()->with('message', 'Student approved and notified.');
    }

    public function rejectStudent($id)
    {
        $student = Student::findOrFail($id);
        $name = $student->first_name . ' ' . $student->last_name;
        $email = $student->email;
        $studentNumber = $student->student_number;

        AuditLogger::log('student_rejected', 'Rejected registration request for student ' . $name . ' (' . ($studentNumber ?? $email) . ')', [
            'affected_user' => $name,
            'student_id'    => $student->id,
            'student_number'=> $studentNumber,
            'email'         => $email,
        ], 'users');

        Mail::to($student->email)->send(new RegistrationRejectedMail($student->first_name, 'student'));
        $student->delete();
        return back()->with('message', 'Student rejected and notified.');
    }

    public function approveFaculty($id)
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->update(['status' => 'active']);

        AuditLogger::log('faculty_approved', 'Approved registration request for faculty ' . $faculty->first_name . ' ' . $faculty->last_name . ' (' . ($faculty->employee_id ?? $faculty->email) . ')', [
            'affected_user' => $faculty->first_name . ' ' . $faculty->last_name,
            'faculty_id'    => $faculty->id,
            'employee_id'   => $faculty->employee_id,
            'department'    => $faculty->department,
            'email'         => $faculty->email,
        ], 'users');

        Mail::to($faculty->email)->send(new FacultyApprovedMail($faculty));
        return back()->with('message', 'Faculty approved and notified.');
    }

    public function rejectFaculty($id)
    {
        $faculty = Faculty::findOrFail($id);
        $name = $faculty->first_name . ' ' . $faculty->last_name;
        $email = $faculty->email;
        $empId = $faculty->employee_id;

        AuditLogger::log('faculty_rejected', 'Rejected registration request for faculty ' . $name . ' (' . ($empId ?? $email) . ')', [
            'affected_user' => $name,
            'faculty_id'    => $faculty->id,
            'employee_id'   => $empId,
            'email'         => $email,
        ], 'users');

        Mail::to($faculty->email)->send(new RegistrationRejectedMail($faculty->first_name, 'faculty'));
        $faculty->delete();
        return back()->with('message', 'Faculty rejected and notified.');
    }
}
