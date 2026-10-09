<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Specialty;
use App\Models\BookUsage;
use App\Services\AISuggestionService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationPendingMail;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class FacultyAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('faculty')->check()) return redirect()->route('faculty.dashboard');
        return view('faculty.login');
    }

    public function showRegister()
    {
        if (Auth::guard('faculty')->check()) return redirect()->route('faculty.dashboard');
        $specialties = Specialty::orderBy('name')->get();
        return view('faculty.register', compact('specialties'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|unique:faculties,employee_id',
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'email'       => 'required|email|unique:faculties,email',
            'password'    => 'required|confirmed|min:6',
            'department'  => 'nullable|string',
            'specialty_id' => 'nullable|exists:specialties,id',
            'verification_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'employee_id.required' => 'Employee ID is required. Please fill in all fields.',
            'employee_id.unique'   => 'This user already exists with this employee ID.',
            'first_name.required'  => 'First name is required. Please fill in all fields.',
            'last_name.required'   => 'Last name is required. Please fill in all fields.',
            'email.required'       => 'Email address is required.',
            'email.email'          => 'Please enter a valid email address.',
            'email.unique'         => 'This user already exists with this email address.',
            'password.required'    => 'Password is required.',
            'password.min'         => 'Password must be at least 6 characters.',
            'password.confirmed'   => 'Passwords do not match. Please verify your password confirmation.',
            'verification_doc.mimes' => 'The verification document must be a file of type: JPG, JPEG, PNG, or PDF.',
        ]);

        $docPath = $request->file('verification_doc')?->store('faculty_docs', 'public');

        $faculty = Faculty::create([
            'employee_id'         => $data['employee_id'],
            'first_name'          => $data['first_name'],
            'last_name'           => $data['last_name'],
            'email'               => $data['email'],
            'password'            => Hash::make($data['password']),
            'department'          => $data['department'] ?? null,
            'verification_doc_path' => $docPath,
            'status'              => 'pending',
        ]);

        if (!empty($data['specialty_id'])) {
            $faculty->specialties()->sync([$data['specialty_id']]);
        }

        AuditLogger::log('auth_registered_faculty', 'Faculty ' . $data['first_name'] . ' ' . $data['last_name'] . ' submitted a registration application', [
            'affected_user' => $data['first_name'] . ' ' . $data['last_name'],
            'faculty_id'    => $faculty->id,
            'employee_id'   => $data['employee_id'],
            'department'    => $data['department'] ?? null,
            'email'         => $data['email'],
        ], 'auth', 'faculty', $faculty->id);

        Mail::to($data['email'])->send(new RegistrationPendingMail($data['first_name'], 'faculty'));

        return redirect()->route('faculty.login')
            ->with('success', 'Your account has been created and is pending admin approval. You will be notified by email once approved.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'employee_id' => 'required',
            'password'    => 'required',
        ]);

        $faculty = Faculty::where('employee_id', $credentials['employee_id'])->first();

        if (!$faculty || !Hash::check($credentials['password'], $faculty->password)) {
            return back()->withErrors(['employee_id' => 'Invalid credentials.']);
        }

        // Check if faculty is soft-deleted (archived)
        if ($faculty->trashed()) {
            return back()->withErrors(['employee_id' => 'Your account has been archived. Please speak to the admin about this.']);
        }

        if ($faculty->status === 'pending') {
            return back()->withErrors(['employee_id' => 'Account pending approval.']);
        }
        if ($faculty->status === 'rejected') {
            return back()->withErrors(['employee_id' => 'Account rejected.']);
        }

        Auth::guard('faculty')->login($faculty);
        $faculty->update(['last_activity_at' => now()]);

        AuditLogger::log('auth_login_faculty', 'Faculty member ' . $faculty->first_name . ' ' . $faculty->last_name . ' logged into faculty portal', [
            'faculty_id'     => $faculty->id,
            'employee_id'    => $faculty->employee_id,
            'performer_name' => $faculty->first_name . ' ' . $faculty->last_name,
        ], 'auth', 'faculty', $faculty->id);

        return redirect()->route('faculty.dashboard');
    }

    public function dashboard()
    {
        $faculty = Auth::guard('faculty')->user();

        $recentHistory = BookUsage::with('book')
            ->where('faculty_id', $faculty->id)
            ->where('status', 'completed')
            ->latest('time_out')
            ->take(10)
            ->get();

        // ── Popular Among Faculty (top 5 books borrowed by any faculty) ──
        $facultyPopularBooks = \Illuminate\Support\Facades\Cache::remember('faculty_popular_books', 900, function () {
            return BookUsage::whereNotNull('faculty_id')
                ->join('books', 'book_usages.book_id', '=', 'books.id')
                ->select('books.id', 'books.title', 'books.author', DB::raw('count(*) as total'))
                ->groupBy('books.id', 'books.title', 'books.author')
                ->orderByDesc('total')
                ->limit(5)
                ->get();
        });

        // AI Recommendations — cached per faculty for 10 minutes
        $recommendations = \Illuminate\Support\Facades\Cache::remember(
            "faculty_recs_{$faculty->id}",
            600,
            fn () => app(AISuggestionService::class)->getFacultyRecommendations($faculty->id, 3)
        );

        return view('faculty.dashboard', compact(
            'recentHistory',
            'facultyPopularBooks',
            'recommendations'
        ));
    }

    public function profile()
    {
        $faculty = Auth::guard('faculty')->user();
        $specialties = Specialty::orderBy('name')->get();
        return view('faculty.profile', compact('faculty', 'specialties'));
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\Faculty $faculty */
        $faculty = Faculty::findOrFail(Auth::guard('faculty')->id());

        $data = $request->validate([
            'name'        => 'nullable|string',
            'first_name'  => 'nullable|string',
            'last_name'   => 'nullable|string',
            'phone'       => 'nullable|string',
            'email'       => 'required|email|unique:faculties,email,' . $faculty->id,
            'department'  => 'nullable|string',
            'specialties' => 'nullable|array',
            'specialties.*' => 'exists:specialties,id',
        ]);

        if ($request->filled('name')) {
            $parts = explode(' ', trim($request->name), 2);
            $data['first_name'] = $parts[0];
            $data['last_name'] = $parts[1] ?? '';
        }
        unset($data['name']);

        $faculty->update($data);
        $faculty->specialties()->sync($request->specialties ?? []);

        AuditLogger::log('user_updated', 'Faculty member ' . $faculty->first_name . ' ' . $faculty->last_name . ' updated their profile', [
            'faculty_id'     => $faculty->id,
            'employee_id'    => $faculty->employee_id,
            'performer_name' => $faculty->first_name . ' ' . $faculty->last_name,
        ], 'users', 'faculty', $faculty->id);

        return back()->with('success', 'Profile updated.');
    }

    public function logout()
    {
        if (session('impersonated_by')) {
            $adminId = session('impersonated_by');
            session()->forget('impersonated_by');
            Auth::guard('faculty')->logout();
            Auth::guard('web')->loginUsingId($adminId);
            return redirect()->route('admin.dashboard');
        }

        $faculty = Auth::guard('faculty')->user();
        if ($faculty) {
            AuditLogger::log('auth_logout_faculty', 'Faculty member ' . $faculty->first_name . ' ' . $faculty->last_name . ' logged out', [
                'faculty_id'     => $faculty->id,
                'employee_id'    => $faculty->employee_id,
                'performer_name' => $faculty->first_name . ' ' . $faculty->last_name,
            ], 'auth', 'faculty', $faculty->id);
        }

        Auth::guard('faculty')->logout();
        return redirect()->route('faculty.login');
    }
}