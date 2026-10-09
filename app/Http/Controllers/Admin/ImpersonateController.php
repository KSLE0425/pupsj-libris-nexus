<?php

// app/Http/Controllers/Admin/ImpersonateController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateController extends Controller
{
    public function impersonate(Student $student)
    {
        // Store original admin ID before switching
        Session::put('impersonate_admin_id', Auth::guard('web')->id());

        // Logout admin and login as the student
        Auth::guard('web')->logout();
        Auth::guard('student')->login($student);

        return redirect()->route('student.dashboard')
            ->with('success', "You are now viewing as {$student->first_name} {$student->last_name}");
    }

    public function leave()
    {
        $adminId = Session::get('impersonate_admin_id');
        if (! $adminId) {
            return redirect()->route('admin.dashboard');
        }

        // Logout student and re‑login admin
        Auth::guard('student')->logout();
        Auth::guard('web')->loginUsingId($adminId);
        Session::forget('impersonate_admin_id');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Returned to admin panel.');
    }

    public function impersonateTest()
    {
        $student = Student::where('email', 'test@student.com')->first();
        if (! $student) {
            return redirect()->route('admin.dashboard')->with('error', 'Test student not found. Run seeder.');
        }
        Session::put('impersonate_admin_id', Auth::guard('web')->id());
        Auth::guard('web')->logout();
        Auth::guard('student')->login($student);

        return redirect()->route('student.dashboard')->with('success', 'You are now viewing as Test Student.');
    }
}
