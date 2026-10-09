<?php

namespace App\Http\Controllers;

use App\Mail\KioskOtpMail;
use App\Mail\RegistrationPendingMail;
use App\Mail\ReservationReadyMail;
use App\Models\Book;
use App\Models\BookUsage;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\KioskOtpCode;
use App\Models\LibraryDamageReport;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\Specialty;
use App\Models\Student;
use App\Services\BorrowEligibilityService;
use App\Services\AuditLogger;
use App\Services\QrCryptoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class KioskController extends Controller
{
    public function index()
    {
        if (session('kiosk_authenticated') && session('kiosk_user_id')) {
            return redirect()->route('kiosk.dashboard');
        }
        return view('kiosk.portal');
    }

    public function showLogin()
    {
        if (session('kiosk_authenticated') && session('kiosk_user_id')) {
            return redirect()->route('kiosk.dashboard');
        }
        return view('kiosk.index');
    }

    public function showPublicReturn()
    {
        return view('kiosk.return-public');
    }

    public function publicReturn(Request $request)
    {
        $request->validate([
            'book_id'           => 'required|string',
            'condition_flags'   => 'nullable|array',
            'condition_flags.*' => 'string',
        ]);

        $input = trim(QrCryptoService::decrypt($request->book_id));
        if (preg_match('/^BOOK:\s*(.+)$/i', $input, $m)) {
            $input = trim($m[1]);
        }
        $book  = Book::where('barcode', $input)->first()
              ?? (is_numeric($input) ? Book::find((int) $input) : null);

        if (!$book) {
            return back()->with('kiosk_error', 'Book not found for that barcode.');
        }

        $usage = BookUsage::with('book')
            ->where('book_id', $book->id)
            ->where('status', 'active')
            ->first();

        if (!$usage) {
            return back()->with('kiosk_error', 'No active borrow found for that book.');
        }

        $usage->update(['status' => 'completed', 'time_out' => now(), 'return_kiosk' => true]);

        $book = $usage->book;
        $book->increment('copies');
        $book->update(['status' => $book->copies > 0 ? 'available' : 'borrowed']);
        $this->triggerNextReservation($book->id);

        $patronName = $usage->student ? ($usage->student->first_name . ' ' . $usage->student->last_name) : ($usage->faculty ? ($usage->faculty->first_name . ' ' . $usage->faculty->last_name) : 'Patron');

        AuditLogger::log('kiosk_return_completed', 'Book "' . $book->title . '" returned via public kiosk by ' . $patronName, [
            'usage_id'      => $usage->id,
            'affected_book' => $book->title,
            'affected_user' => $patronName,
            'book_id'       => $book->id,
            'channel'       => 'kiosk_public',
        ], 'kiosk');

        $conditionFlags = array_filter((array) $request->input('condition_flags', []));
        if (!empty($conditionFlags)) {
            $labelMap = [
                'torn_pages'    => 'Torn pages',
                'water_damage'  => 'Water damage',
                'missing_pages' => 'Missing pages',
                'cover_damage'  => 'Cover damage',
                'spine_damage'  => 'Spine damage',
                'writing'       => 'Writing/markings inside',
            ];
            $patronNote = implode(', ', array_map(
                fn ($f) => $labelMap[$f] ?? ucfirst(str_replace('_', ' ', $f)),
                $conditionFlags
            ));
            LibraryDamageReport::create([
                'book_id'       => $usage->book_id,
                'book_usage_id' => $usage->id,
                'student_id'    => $usage->student_id,
                'faculty_id'    => $usage->faculty_id,
                'patron_note'   => $patronNote,
                'status'        => 'pending',
            ]);
        }

        return back()->with('kiosk_success', '"' . $book->title . '" returned successfully!');
    }

    public function reportDamage(Request $request)
    {
        $request->validate([
            'book_id'           => 'required|integer',
            'usage_id'          => 'required|integer',
            'condition_flags'   => 'required|array|min:1',
            'condition_flags.*' => 'string',
        ]);

        $labelMap = [
            'torn_pages'    => 'Torn pages',
            'water_damage'  => 'Water damage',
            'missing_pages' => 'Missing pages',
            'cover_damage'  => 'Cover damage',
            'spine_damage'  => 'Spine damage',
            'writing'       => 'Writing/markings inside',
            'physically_damaged' => 'Physically damaged',
        ];

        $conditionFlags = array_filter((array) $request->input('condition_flags', []));
        $patronNote = implode(', ', array_map(
            fn ($f) => $labelMap[$f] ?? ucfirst(str_replace('_', ' ', $f)),
            $conditionFlags
        ));

        $usage = BookUsage::find($request->usage_id);
        LibraryDamageReport::create([
            'book_id'       => $request->book_id,
            'book_usage_id' => $request->usage_id,
            'student_id'    => $usage?->student_id,
            'faculty_id'    => $usage?->faculty_id,
            'patron_note'   => $patronNote,
            'status'        => 'pending',
        ]);

        return back()->with('kiosk_success', 'Damage report submitted. Thank you for letting us know!');
    }

    public function returnLookup(Request $request)
    {
        $input = trim(QrCryptoService::decrypt($request->query('book_id', '')));

        if (!$input) {
            return response()->json(['found' => false, 'message' => 'Missing book ID.']);
        }

        if (preg_match('/^BOOK:\s*(.+)$/i', $input, $m)) {
            $input = trim($m[1]);
        }

        // Mirror smartScan: look up by barcode string first, fall back to numeric DB ID
        $book = Book::where('barcode', $input)->first()
             ?? (is_numeric($input) ? Book::find((int) $input) : null);

        if (!$book) {
            return response()->json(['found' => false, 'message' => 'Book not found for that barcode.']);
        }

        $usage = BookUsage::with(['book', 'student', 'faculty'])
            ->where('book_id', $book->id)
            ->where('status', 'active')
            ->first();

        if (!$usage) {
            return response()->json(['found' => false, 'message' => 'No active borrow found for this book.']);
        }

        $person = $usage->student ?? $usage->faculty;

        return response()->json([
            'found'         => true,
            'book_id'       => $book->id,
            'book_title'    => $book->title,
            'borrower_name' => $person ? $person->first_name . ' ' . $person->last_name : 'Unknown patron',
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ]);

        $rawId = trim(QrCryptoService::decrypt($request->identifier));
        if (preg_match('/^STUDENT:\s*(\d+)$/i', $rawId, $m)) {
            $studentObj = Student::find((int) $m[1]);
            $identifier = $studentObj ? $studentObj->student_number : $rawId;
        } elseif (preg_match('/^FACULTY:\s*(\d+)$/i', $rawId, $m)) {
            $facultyObj = Faculty::find((int) $m[1]);
            $identifier = $facultyObj ? $facultyObj->employee_id : $rawId;
        } else {
            $identifier = $rawId;
        }
        $password   = $request->password;

        // Try student
        $student = Student::where('student_number', $identifier)->first();
        if ($student && !$student->trashed() && Hash::check($password, $student->password)) {
            if (!in_array($student->status, ['approved', 'active', 'inactive'])) {
                return back()->withErrors(['identifier' => 'Your account is not yet approved.']);
            }
            // Restore dormant users when they log in again
            $updates = ['last_activity_at' => now()];
            if ($student->status === 'inactive') {
                $updates['status'] = 'approved';
            }
            $student->update($updates);
            session([
                'kiosk_authenticated' => true,
                'kiosk_user_id'       => $student->id,
                'kiosk_guard'         => 'student',
            ]);
            return redirect()->route('kiosk.dashboard');
        }

        // Try faculty
        $faculty = Faculty::where('employee_id', $identifier)->first();
        if ($faculty && !$faculty->trashed() && Hash::check($password, $faculty->password)) {
            if (!in_array($faculty->status, ['approved', 'active', 'inactive'])) {
                return back()->withErrors(['identifier' => 'Your account is not yet approved.']);
            }
            // Restore dormant users when they log in again
            $updates = ['last_activity_at' => now()];
            if ($faculty->status === 'inactive') {
                $updates['status'] = 'approved';
            }
            $faculty->update($updates);
            session([
                'kiosk_authenticated' => true,
                'kiosk_user_id'       => $faculty->id,
                'kiosk_guard'         => 'faculty',
            ]);
            return redirect()->route('kiosk.dashboard');
        }

        return back()->withErrors(['identifier' => 'Invalid student number / employee ID or password.']);
    }

    public function logout()
    {
        session()->forget(['kiosk_authenticated', 'kiosk_user_id', 'kiosk_guard']);
        return redirect()->route('kiosk.sign-in');
    }

    public function dashboard()
    {
        $user = $this->kioskUser();
        $activeBorrows = BookUsage::with('book')
            ->where($this->userIdColumn(), $user->id)
            ->where('status', 'active')
            ->get();

        return view('kiosk.dashboard', compact('user', 'activeBorrows'));
    }

    public function borrow(Request $request)
    {
        $request->validate([
            'book_id'         => 'required|integer',
            'confirm_borrow'  => 'nullable|in:1',
            'borrow_days'     => 'nullable|integer|min:1',
            'in_library'      => 'nullable|in:1',
        ]);

        $user     = $this->kioskUser();
        $guard    = session('kiosk_guard', 'student');
        $book     = Book::find($request->book_id);

        if (!$book) {
            return back()->with('kiosk_error', 'Book not found.');
        }
        if ($book->copies <= 0 || $book->status !== 'available' || $book->is_condemned) {
            return back()->with('kiosk_error', 'This book is not available for borrowing.');
        }

        $alreadyBorrowed = BookUsage::where($this->userIdColumn(), $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'active')
            ->exists();

        if ($alreadyBorrowed) {
            return back()->with('kiosk_error', 'You have already borrowed this book.');
        }

        // Block if another patron has a ready reservation for this book
        $userCol  = $this->userIdColumn();
        $reservedForOther = Reservation::where('book_id', $book->id)
            ->where('status', 'ready')
            ->where(function ($q) use ($user, $userCol) {
                $q->where($userCol, '!=', $user->id)->orWhereNull($userCol);
            })->exists();
        if ($reservedForOther) {
            return back()->with('kiosk_error', '"' . $book->title . '" is currently reserved for another patron.');
        }

        // Check eligibility
        if ($guard === 'student') {
            $block = BorrowEligibilityService::studentBlockingReason($user);
        } else {
            $block = BorrowEligibilityService::facultyBlockingReason($user);
        }
        if ($block) {
            return back()->with('kiosk_error', $block);
        }

        $finesEnabled = Setting::getValue('overdue_fines_enabled', '0') === '1';
        if ($finesEnabled && !$request->boolean('confirm_borrow')) {
            return $this->flashPendingBorrow($book, $user, $guard);
        }

        $duration = $this->resolveBorrowDuration($request, $user, $guard);

        $usage = BookUsage::create([
            $this->userIdColumn() => $user->id,
            'book_id'       => $book->id,
            'time_in'       => now(),
            'due_date'      => $duration['due_date'],
            'status'        => 'active',
            'usage_context' => $duration['usage_context'],
        ]);

        $user->update(['last_activity_at' => now(), 'last_borrow_at' => now()]);
        $book->decrement('copies');
        $book->update(['status' => $book->copies > 0 ? 'available' : 'borrowed']);

        $patronName = $user->first_name . ' ' . $user->last_name;
        AuditLogger::log('kiosk_borrow_created', 'Patron ' . $patronName . ' borrowed "' . $book->title . '" via Kiosk (' . $duration['usage_context'] . ')', [
            'usage_id'      => $usage->id,
            'affected_book' => $book->title,
            'affected_user' => $patronName,
            'book_id'       => $book->id,
            'user_id'       => $user->id,
            'guard'         => $guard,
            'due_date'      => $duration['due_date'],
            'usage_context' => $duration['usage_context'],
        ], 'kiosk', $guard, $user->id);

        return back()->with('kiosk_success', "\"" . $book->title . "\" borrowed successfully!");
    }

    public function return(Request $request)
    {
        $request->validate(['book_id' => 'required|integer']);

        $user  = $this->kioskUser();
        $usage = BookUsage::with('book')
            ->where($this->userIdColumn(), $user->id)
            ->where('book_id', $request->book_id)
            ->where('status', 'active')
            ->first();

        if (!$usage) {
            return back()->with('kiosk_error', 'No active borrow found for this book.');
        }

        $usage->update(['status' => 'completed', 'time_out' => now(), 'return_kiosk' => true]);

        $book = $usage->book;
        $book->increment('copies');
        $book->update(['status' => $book->copies > 0 ? 'available' : 'borrowed']);
        $this->triggerNextReservation($book->id);

        $patronName = $user->first_name . ' ' . $user->last_name;
        $guard = session('kiosk_guard', 'student');

        AuditLogger::log('kiosk_return_completed', 'Patron ' . $patronName . ' returned "' . $book->title . '" via Kiosk', [
            'usage_id'      => $usage->id,
            'affected_book' => $book->title,
            'affected_user' => $patronName,
            'book_id'       => $book->id,
            'user_id'       => $user->id,
            'guard'         => $guard,
        ], 'kiosk', $guard, $user->id);

        return back()->with('kiosk_success', "\"" . $book->title . "\" returned successfully!");
    }

    /**
     * Smart scan — auto-detect borrow vs return based on current loans.
     * If the patron already has this book checked out → return it.
     * If not → borrow it.
     */
    public function smartScan(Request $request)
    {
        $request->validate([
            'book_id'        => 'required|string|max:100',
            'confirm_borrow' => 'nullable|in:1',
            'borrow_days'    => 'nullable|integer|min:1',
            'in_library'     => 'nullable|in:1',
        ]);

        $user  = $this->kioskUser();
        $guard = session('kiosk_guard', 'student');
        $input = trim(QrCryptoService::decrypt($request->book_id));

        // Normalize QR payloads like "BOOK:123"
        if (preg_match('/^BOOK:\s*(.+)$/i', $input, $m)) {
            $input = trim($m[1]);
        }

        // Look up by barcode first, then fall back to numeric DB ID
        $book = Book::where('barcode', $input)->first()
            ?? (is_numeric($input) ? Book::find((int) $input) : null);

        if (!$book) {
            return back()->with('kiosk_error', 'Book not found for barcode "' . $input . '". Please try again.');
        }

        try {
            return DB::transaction(function () use ($book, $user, $guard, $request) {
                // If patron has an active loan for this book → return it
                $activeUsage = BookUsage::where($this->userIdColumn(), $user->id)
                    ->where('book_id', $book->id)
                    ->where('status', 'active')
                    ->first();

                if ($activeUsage) {
                    $activeUsage->update(['status' => 'completed', 'time_out' => now(), 'return_kiosk' => true]);
                    $book->increment('copies');
                    $book->update(['status' => $book->copies > 0 ? 'available' : 'borrowed']);
                    $this->triggerNextReservation($book->id);
                    return back()
                        ->with('kiosk_success', '"' . $book->title . '" returned successfully!')
                        ->with('kiosk_return_book_id', $book->id)
                        ->with('kiosk_return_usage_id', $activeUsage->id);
                }

                // Otherwise attempt to borrow
                if ($book->copies <= 0 || $book->status !== 'available' || $book->is_condemned) {
                    return back()->with('kiosk_error', '"' . $book->title . '" is not available for borrowing.');
                }

                // Block if another patron has a ready reservation for this book
                $userCol = $this->userIdColumn();
                $reservedForOther = Reservation::where('book_id', $book->id)
                    ->where('status', 'ready')
                    ->where(function ($q) use ($user, $userCol) {
                        $q->where($userCol, '!=', $user->id)->orWhereNull($userCol);
                    })->exists();
                if ($reservedForOther) {
                    return back()->with('kiosk_error', '"' . $book->title . '" is currently reserved for another patron.');
                }

                $block = $guard === 'student'
                    ? BorrowEligibilityService::studentBlockingReason($user)
                    : BorrowEligibilityService::facultyBlockingReason($user);

                if ($block) {
                    return back()->with('kiosk_error', $block);
                }

                $finesEnabled = Setting::getValue('overdue_fines_enabled', '0') === '1';
                if ($finesEnabled && !$request->boolean('confirm_borrow')) {
                    return $this->flashPendingBorrow($book, $user, $guard);
                }

                $duration = $this->resolveBorrowDuration($request, $user, $guard);

                BookUsage::create([
                    $this->userIdColumn() => $user->id,
                    'book_id'       => $book->id,
                    'time_in'       => now(),
                    'due_date'      => $duration['due_date'],
                    'status'        => 'active',
                    'usage_context' => $duration['usage_context'],
                ]);

                $user->update(['last_activity_at' => now(), 'last_borrow_at' => now()]);
                $book->decrement('copies');
                $book->update(['status' => $book->copies > 0 ? 'available' : 'borrowed']);

                return back()->with('kiosk_success', '"' . $book->title . '" borrowed successfully!');
            });
        } catch (\Throwable $e) {
            return back()->with('kiosk_error', 'Scan failed: ' . $e->getMessage() . '. Please try again.');
        }
    }

    /**
     * Flash data so the kiosk dashboard can show the borrow-duration modal.
     */
    private function flashPendingBorrow(Book $book, $user, string $guard)
    {
        $maxDays = max(1, (int) Setting::getValue('max_borrow_days_student', 7));
        $studentTakeHomeAllowed = Setting::getValue('student_take_home_allowed', '1') === '1';
        $inLibraryOnly = ($guard === 'student') && !$studentTakeHomeAllowed;

        return back()
            ->with('kiosk_pending_borrow', true)
            ->with('kiosk_pending_book_id', $book->id)
            ->with('kiosk_pending_book_title', $book->title)
            ->with('kiosk_pending_max_days', $maxDays)
            ->with('kiosk_pending_in_library_only', $inLibraryOnly);
    }

    /**
     * Resolve due date + usage_context from confirm form (or defaults when fines off).
     */
    private function resolveBorrowDuration(Request $request, $user, string $guard): array
    {
        $maxDays = max(1, (int) Setting::getValue('max_borrow_days_student', 7));
        $studentTakeHomeAllowed = Setting::getValue('student_take_home_allowed', '1') === '1';
        $inLibraryOnly = ($guard === 'student') && !$studentTakeHomeAllowed;

        if ($request->boolean('in_library') || $inLibraryOnly) {
            return [
                'due_date'       => now()->toDateString(),
                'usage_context'  => 'in_library',
            ];
        }

        $days = (int) ($request->input('borrow_days') ?: $maxDays);
        $days = max(1, min($days, $maxDays));

        return [
            'due_date'      => now()->addDays($days)->toDateString(),
            'usage_context' => $guard === 'faculty' ? 'off_site' : 'kiosk',
        ];
    }

    public function showRegister()
    {
        if (session('kiosk_authenticated') && session('kiosk_user_id')) {
            return redirect()->route('kiosk.dashboard');
        }
        $programs    = Course::orderBy('name')->get();
        $specialties = Specialty::orderBy('name')->get();
        return view('kiosk.register', compact('programs', 'specialties'));
    }

    public function register(Request $request)
    {
        $request->validate(['user_type' => 'required|in:student,faculty']);

        if ($request->user_type === 'student') {
            $data = $request->validate([
                'student_number' => 'required|unique:students,student_number',
                'first_name'     => 'required|string|max:100',
                'last_name'      => 'required|string|max:100',
                'email'          => 'required|email|unique:students,email',
                'program'        => 'required|string',
                'year_level'     => 'required|in:1,2,3,4',
                'password'       => 'required|confirmed|min:6',
                'cor_file'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ], [
                'student_number.required' => 'Student number is required. Please fill in all fields.',
                'student_number.unique'   => 'This user already exists with this student number.',
                'first_name.required'     => 'First name is required. Please fill in all fields.',
                'last_name.required'      => 'Last name is required. Please fill in all fields.',
                'email.required'          => 'Email address is required.',
                'email.email'             => 'Please enter a valid email address.',
                'email.unique'            => 'This user already exists with this email address.',
                'program.required'        => 'Please select your program.',
                'year_level.required'     => 'Please select your year level.',
                'password.required'       => 'Password is required.',
                'password.min'            => 'Password must be at least 6 characters.',
                'password.confirmed'      => 'Passwords do not match. Please verify your password confirmation.',
                'cor_file.mimes'          => 'The COR must be a file of type: JPG, JPEG, PNG, or PDF.',
            ]);

            $corPath = $request->hasFile('cor_file')
                ? $request->file('cor_file')->store('cor_uploads', 'public')
                : null;

            Student::create([
                'student_number' => $data['student_number'],
                'first_name'     => $data['first_name'],
                'last_name'      => $data['last_name'],
                'email'          => $data['email'],
                'program'        => $data['program'],
                'year_level'     => $data['year_level'],
                'password'       => Hash::make($data['password']),
                'cor_file_path'  => $corPath,
                'status'         => 'pending',
            ]);

            Mail::to($data['email'])->send(new RegistrationPendingMail($data['first_name'], 'student'));
        } else {
            $data = $request->validate([
                'employee_id'              => 'required|unique:faculties,employee_id',
                'faculty_first_name'       => 'required|string|max:100',
                'faculty_last_name'        => 'required|string|max:100',
                'faculty_email'            => 'required|email|unique:faculties,email',
                'faculty_password'         => 'required|min:6|same:faculty_password_confirmation',
                'faculty_password_confirmation' => 'required|string',
                'specialty_ids'            => 'nullable|array',
                'specialty_ids.*'          => 'integer|exists:specialties,id',
                'verification_doc'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ], [
                'employee_id.required'     => 'Employee ID is required. Please fill in all fields.',
                'employee_id.unique'       => 'This user already exists with this employee ID.',
                'faculty_first_name.required' => 'First name is required. Please fill in all fields.',
                'faculty_last_name.required'  => 'Last name is required. Please fill in all fields.',
                'faculty_email.required'   => 'Email address is required.',
                'faculty_email.email'      => 'Please enter a valid email address.',
                'faculty_email.unique'     => 'This user already exists with this email address.',
                'faculty_password.required' => 'Password is required.',
                'faculty_password.min'     => 'Password must be at least 6 characters.',
                'faculty_password.same'    => 'Passwords do not match. Please verify your password confirmation.',
                'verification_doc.mimes'   => 'The verification document must be a file of type: JPG, JPEG, PNG, or PDF.',
            ]);

            $docPath = $request->hasFile('verification_doc')
                ? $request->file('verification_doc')->store('faculty_docs', 'public')
                : null;

            $selectedSpecialties = Specialty::whereIn('id', $data['specialty_ids'] ?? [])->pluck('name')->all();
            $deptValue = !empty($selectedSpecialties) ? implode(', ', $selectedSpecialties) : null;

            $faculty = Faculty::create([
                'employee_id'           => $data['employee_id'],
                'first_name'            => $data['faculty_first_name'],
                'last_name'             => $data['faculty_last_name'],
                'email'                 => $data['faculty_email'],
                'password'              => Hash::make($data['faculty_password']),
                'department'            => $deptValue,
                'verification_doc_path' => $docPath,
                'status'                => 'pending',
            ]);

            if (!empty($data['specialty_ids'])) {
                $faculty->specialties()->sync($data['specialty_ids']);
            }

            Mail::to($data['faculty_email'])->send(new RegistrationPendingMail($data['faculty_first_name'], 'faculty'));
        }

        $successMsg = 'Your account has been created and is pending admin approval. You will be notified by email once approved.';

        // Return JSON for AJAX requests, redirect for standard form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return back()->with('kiosk_success', $successMsg);
    }

    public function showForgotPassword()
    {
        return view('kiosk.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['identifier' => 'required|string']);

        $identifier = trim($request->identifier);
        $user       = null;
        $guard      = null;

        $student = Student::where('student_number', $identifier)->first();
        if ($student) { $user = $student; $guard = 'student'; }

        if (!$user) {
            $faculty = Faculty::where('employee_id', $identifier)->first();
            if ($faculty) { $user = $faculty; $guard = 'faculty'; }
        }

        if (!$user || empty($user->email)) {
            return back()->withErrors(['identifier' => 'No account found with that ID, or no email on file.']);
        }

        // Invalidate old codes for this email
        KioskOtpCode::where('email', $user->email)->whereNull('used_at')->update(['used_at' => now()]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        KioskOtpCode::create([
            'email'      => $user->email,
            'code'       => $code,
            'guard'      => $guard,
            'expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($user->email)->send(new KioskOtpMail($code, $user->first_name));
        } catch (\Exception $e) {
            \Log::error('KioskOTP email failed: ' . $e->getMessage());
            return back()->with('kiosk_error', 'Failed to send email. Please try again.');
        }

        session(['otp_identifier' => $identifier, 'otp_email' => $user->email]);
        return redirect()->route('kiosk.verify-otp')->with('kiosk_success', 'Verification code sent to your email.');
    }

    public function showVerifyOtp()
    {
        if (!session('otp_email')) return redirect()->route('kiosk.forgot-password');
        return view('kiosk.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $email = session('otp_email');
        if (!$email) return redirect()->route('kiosk.forgot-password');

        $otp = KioskOtpCode::where('email', $email)
            ->where('code', $request->code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return back()->withErrors(['code' => 'Invalid or expired code. Please try again.']);
        }

        $otp->update(['used_at' => now()]);

        session([
            'otp_verified_email' => $email,
            'otp_verified_guard' => $otp->guard,
        ]);
        session()->forget(['otp_identifier', 'otp_email']);

        return redirect()->route('kiosk.reset-password');
    }

    public function showResetPassword()
    {
        if (!session('otp_verified_email')) return redirect()->route('kiosk.forgot-password');
        return view('kiosk.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $email = session('otp_verified_email');
        $guard = session('otp_verified_guard');

        if (!$email) return redirect()->route('kiosk.forgot-password');

        if ($guard === 'student') {
            Student::where('email', $email)->update(['password' => Hash::make($request->password)]);
        } else {
            Faculty::where('email', $email)->update(['password' => Hash::make($request->password)]);
        }

        session()->forget(['otp_verified_email', 'otp_verified_guard']);

        return redirect()->route('kiosk.sign-in')->with('kiosk_success', 'Password reset successfully. You may now log in.');
    }

    // ── Helpers ──

    private function kioskUser()
    {
        $guard = session('kiosk_guard');
        $userId = session('kiosk_user_id');
        if (!$userId || !$guard) {
            return null;
        }
        if ($guard === 'student') {
            return Student::find($userId);
        }
        if ($guard === 'faculty') {
            return Faculty::find($userId);
        }
        return null;
    }

    private function userIdColumn(): string
    {
        return session('kiosk_guard') === 'faculty' ? 'faculty_id' : 'student_id';
    }

    /**
     * After a book is returned, promote the next pending reservation to 'ready'
     * and notify the reserver by email.
     */
    private function triggerNextReservation(int $bookId): void
    {
        $next = Reservation::where('book_id', $bookId)
            ->where('status', 'pending')
            ->orderBy('position')
            ->first();

        if (!$next) {
            return;
        }

        $days = (int) config('library_fees.reservation_default_expiry_days', 3);
        $next->update(['status' => 'ready', 'expires_at' => now()->addDays($days)]);

        $email = $next->reserver_email;
        if ($email) {
            try {
                Mail::to($email)->send(new ReservationReadyMail($next));
            } catch (\Throwable $e) {
                // Mail failure should not break the return flow
            }
        }
    }
}
