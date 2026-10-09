<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BookUsage;
use App\Models\Student;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationPendingMail;
use App\Services\AISuggestionService;

class StudentAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }

        return view('student.login');
    }

    public function showRegister()
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }

        return view('student.register');
    }

    public function register(Request $request)
    {
        if (!$request->has('program') && $request->has('course')) {
            $request->merge(['program' => $request->course]);
        }

        $data = $request->validate([
            'student_number' => 'required|unique:students,student_number',
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'program'        => 'required|string',
            'year_level'     => 'required|in:1,2,3,4',
            'email'          => 'required|email|unique:students,email',
            'password'       => 'required|confirmed|min:6',
            'cor_file'       => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'student_number.required' => 'Student number is required. Please fill in all fields.',
            'student_number.unique'   => 'This user already exists with this student number.',
            'first_name.required'     => 'First name is required. Please fill in all fields.',
            'last_name.required'      => 'Last name is required. Please fill in all fields.',
            'program.required'        => 'Please select your program.',
            'year_level.required'     => 'Please select your year level.',
            'email.required'          => 'Email address is required.',
            'email.email'             => 'Please enter a valid email address.',
            'email.unique'            => 'This user already exists with this email address.',
            'password.required'       => 'Password is required.',
            'password.min'            => 'Password must be at least 6 characters.',
            'password.confirmed'      => 'Passwords do not match. Please verify your password confirmation.',
            'cor_file.required'       => 'Please upload your Certificate of Registration (COR).',
            'cor_file.mimes'          => 'The COR must be a file of type: JPG, JPEG, PNG, or PDF.',
        ]);

        $corPath = $request->file('cor_file')->store('cor_uploads', 'public');

        $student = Student::create([
            'student_number' => $data['student_number'],
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'program'        => $data['program'],
            'year_level'     => $data['year_level'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'cor_file_path'  => $corPath,
            'status'         => 'pending',
        ]);

        AuditLogger::log('auth_registered_student', 'Student ' . $data['first_name'] . ' ' . $data['last_name'] . ' submitted a registration application', [
            'affected_user'  => $data['first_name'] . ' ' . $data['last_name'],
            'student_id'     => $student->id,
            'student_number' => $data['student_number'],
            'program'        => $data['program'],
            'email'          => $data['email'],
        ], 'auth', 'student', $student->id);

        Mail::to($data['email'])->send(new RegistrationPendingMail($data['first_name'], 'student'));

        return redirect()->route('student.login')
            ->with('success', 'Your account has been created and is pending admin approval. You will be notified by email once approved.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'student_number' => 'required',
            'password'       => 'required',
        ]);

        // Find student by student number
        $student = Student::where('student_number', $request->student_number)->first();

        // Check student exists, password matches, and account is approved
        if (! $student || ! Hash::check($request->password, $student->password)) {
            return back()->withErrors([
                'student_number' => 'Invalid student number or password.',
            ])->withInput();
        }

        // Check if student is soft-deleted (archived)
        if ($student->trashed()) {
            return back()->withErrors([
                'student_number' => 'Your account has been archived. Please speak to the admin about this.',
            ])->withInput();
        }

        if ($student->status === 'pending') {
            return back()->withErrors([
                'student_number' => 'Your account is still pending approval.',
            ])->withInput();
        }

        if ($student->status === 'rejected') {
            return back()->withErrors([
                'student_number' => 'Your account has been rejected. Please contact the library.',
            ])->withInput();
        }

        Auth::guard('student')->login($student);
        $student->update(['last_activity_at' => now()]);

        AuditLogger::log('auth_login_student', 'Student ' . $student->first_name . ' ' . $student->last_name . ' logged into student portal', [
            'student_id'     => $student->id,
            'student_number' => $student->student_number,
            'performer_name' => $student->first_name . ' ' . $student->last_name,
        ], 'auth', 'student', $student->id);

        return redirect()->route('student.dashboard');
    }

    public function dashboard()
    {
        $student = Auth::guard('student')->user();

        // Full program name from courses table (fallback to acronym)
        $courseFullName = $student->program;
        $course = \App\Models\Course::where('code', $student->program)->first();
        if ($course) {
            $courseFullName = $course->name;
        }

        // Recent completed borrows
        $recentHistory = BookUsage::with('book')
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->latest('time_out')
            ->take(5)
            ->get();

        // AI Recommendations — cached per student for 10 minutes
        try {
            $aiService = app(\App\Services\AISuggestionService::class);
            $recommendations = \Illuminate\Support\Facades\Cache::remember(
                "student_recs_{$student->id}",
                600,
                fn () => $aiService->getStudentRecommendations($student->id, 3)
            );
        } catch (\Exception $e) {
            \Log::warning('Dashboard AI recommendations failed: ' . $e->getMessage());
            $recommendations = [];
        }

        return view('student.dashboard', compact(
            'recentHistory',
            'courseFullName',
            'recommendations'
        ));
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        $data = $request->validate([
            'name'       => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name'  => 'nullable|string',
            'course'     => 'nullable|string',
            'program'    => 'nullable|string',
            'year_level' => 'required',
            'email'      => 'required|email|unique:students,email,' . $student->id,
            'pup_email'  => 'nullable|email',
        ]);

        if ($request->filled('name')) {
            $parts = explode(' ', trim($request->name), 2);
            $data['first_name'] = $parts[0];
            $data['last_name'] = $parts[1] ?? '';
        }
        if ($request->filled('course') && !$request->filled('program')) {
            $data['program'] = $request->course;
        }
        unset($data['name'], $data['course']);

        $student->update($data);

        AuditLogger::log('user_updated', 'Student ' . $student->first_name . ' ' . $student->last_name . ' updated their profile', [
            'student_id'     => $student->id,
            'student_number' => $student->student_number,
            'performer_name' => $student->first_name . ' ' . $student->last_name,
        ], 'users', 'student', $student->id);

        return back()->with('success', 'Profile updated!');
    }

    public function profile()
    {
        $student = Auth::guard('student')->user();

        return view('student.profile', compact('student'));
    }

    public function logout()
    {
        if (session('impersonated_by')) {
            $adminId = session('impersonated_by');
            session()->forget('impersonated_by');
            auth()->guard('student')->logout();
            auth()->guard('web')->loginUsingId($adminId);
            return redirect('/dashboard');
        }

        $student = Auth::guard('student')->user();
        if ($student) {
            AuditLogger::log('auth_logout_student', 'Student ' . $student->first_name . ' ' . $student->last_name . ' logged out', [
                'student_id'     => $student->id,
                'student_number' => $student->student_number,
                'performer_name' => $student->first_name . ' ' . $student->last_name,
            ], 'auth', 'student', $student->id);
        }

        Auth::guard('student')->logout();
        return redirect()->route('student.login');
    }
}