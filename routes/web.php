<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\OperationsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Faculty\FacultyPenaltyController;
use App\Http\Controllers\Faculty\FacultyRequisitionController;
use App\Http\Controllers\Faculty\FacultyReservationController;
use App\Http\Controllers\Student\StudentPenaltyController;
use App\Http\Controllers\Student\StudentRequisitionController;
use App\Http\Controllers\Student\StudentReservationController;
use App\Http\Controllers\Admin\AIPurchaseSuggestionsController;
use App\Http\Controllers\Admin\TrendTrackerController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\CollectionTypeController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Student\AIRecommendationsController;
use App\Http\Controllers\Student\StudentAuthController;
use App\Http\Controllers\Student\StudentBorrowController;
use App\Http\Controllers\Faculty\FacultyAuthController;
use App\Http\Controllers\Faculty\FacultyBorrowController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\FacultyApprovalController;
use App\Models\Book;
use App\Models\BookUsage;
use App\Models\CollectionType;
use App\Models\Course;
use App\Models\Student;
use App\Services\ReceiptPrinterService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuestBookController;
use App\Models\BookNotification;
use App\Models\LibraryDamageReport;
use App\Models\LibraryPenalty;
use App\Mail\BookAvailableMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Admin\GlobalArchiveController;
use App\Http\Controllers\ReportPresetController;

Route::get('/', function () {
    return view('landing');
})->name('landing');
Route::get('/login', function () {
    return view('login-selection');
})->name('login');

// optional alias for backward compatibility
Route::get('/login-selection', function () {
    return view('login-selection');
})->name('login.selection');

// ============================================
// FORGOT PASSWORD
// ============================================
use App\Http\Controllers\ForgotPasswordController;

Route::get('/password/forgot/{guard?}', [ForgotPasswordController::class, 'showForgotForm'])->name('password.forgot.form');
Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLink'])->name('password.forgot.send');
Route::get('/password/reset/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/password/reset', [ForgotPasswordController::class, 'updatePassword'])->name('password.reset.update');

// ============================================
// ADMIN AUTHENTICATION
// ============================================
Route::middleware('guest:web')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    Route::get('/admin/verify-otp', [AdminAuthController::class, 'showOtpForm'])->name('admin.otp.form');
    Route::post('/admin/verify-otp', [AdminAuthController::class, 'verifyOtp'])->name('admin.otp.verify');
    Route::get('/admin/resend-otp', [AdminAuthController::class, 'resendOtp'])->name('admin.otp.resend');
});

Route::middleware('auth:web')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// ============================================
// GUEST WEB ROUTES
// ============================================
Route::get('/guest/books', [GuestBookController::class, 'index'])->name('guest.books');
Route::get('/guest/books/{book}', [GuestBookController::class, 'show'])->name('guest.book.show');

// ============================================
// STUDENT WEB ROUTES
// ============================================
Route::get('/student/login', [StudentAuthController::class, 'showLogin'])->name('student.login');
Route::post('/student/login', [StudentAuthController::class, 'login'])->name('student.login.post');
Route::get('/student/register', [StudentAuthController::class, 'showRegister'])->name('student.register');
Route::post('/student/register', [StudentAuthController::class, 'register'])->name('student.register.post');

Route::get('book-by-hash/{hash}', [StudentBorrowController::class, 'getBookByHash'])->name('book.by-hash');

Route::prefix('student')->name('student.')->group(function () {
    Route::middleware('auth:student')->group(function () {
        Route::get('dashboard', [StudentAuthController::class, 'dashboard'])->name('dashboard');
        Route::get('profile', [StudentAuthController::class, 'profile'])->name('profile');
        Route::post('profile', [StudentAuthController::class, 'updateProfile'])->name('profile.update');
        Route::post('logout', [StudentAuthController::class, 'logout'])->name('logout');
        Route::get('borrow', [StudentBorrowController::class, 'showBorrow'])->name('borrow');
        Route::get('history', [StudentBorrowController::class, 'history'])->name('history');
        Route::post('notify/{book}', [StudentBorrowController::class, 'notify'])->name('notify');
        Route::get('books/{book}', [StudentBorrowController::class, 'getBook'])->name('book.show');
        Route::get('book-by-barcode/{barcode}', [StudentBorrowController::class, 'getBookByBarcode'])->name('book.by-barcode');
        Route::get('active-borrow', [StudentBorrowController::class, 'getActiveBorrow'])->name('active-borrow');
        Route::post('borrow', [StudentBorrowController::class, 'borrow'])->name('borrow.post');
        Route::post('return', [StudentBorrowController::class, 'returnBook'])->name('return');

        Route::get('reservations', [StudentReservationController::class, 'index'])->name('reservations.index');
        Route::post('reservations', [StudentReservationController::class, 'store'])->name('reservations.store');
        Route::post('reservations/{reservation}/cancel', [StudentReservationController::class, 'destroy'])->name('reservations.destroy');

        Route::get('requisitions', [StudentRequisitionController::class, 'index'])->name('requisitions.index');
        Route::get('requisitions/create', [StudentRequisitionController::class, 'create'])->name('requisitions.create');
        Route::post('requisitions', [StudentRequisitionController::class, 'store'])->name('requisitions.store');

        Route::get('penalties', [StudentPenaltyController::class, 'index'])->name('penalties.index');
        
    });
});

// ============================================
// KIOSK MODE (NO AUTH REQUIRED, LONG SESSION)
// ============================================
Route::get('/kiosk', function () {
    return view('kiosk');
})->name('kiosk');

// ============================================
// ADMIN DASHBOARD & REPORTS (PROTECTED)
// ============================================
use App\Http\Controllers\Admin\AnalyticsController;

Route::middleware('auth:web')->group(function () {
    // ADMIN DASHBOARD (Real-time / Operational)
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('admin.dashboard');
    
    // ADMIN ANALYTICS (Trends, Historical & Strategic Insights)
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');
    Route::get('/admin/analytics', [AnalyticsController::class, 'index']);

    Route::get('/reports', [ReportController::class, 'dashboard'])->name('admin.reports.index');
    Route::get('/reports/export', [ReportController::class, 'exportPDF'])->name('admin.reports.export');
    Route::get('/reports/export-raw', [ReportController::class, 'exportRaw'])->name('admin.reports.export-raw');
    Route::get('/reports/export/raw', [ReportController::class, 'exportRaw'])->name('admin.reports.export.raw');
    
    Route::get('/reports/general', [ReportController::class, 'generalPDF'])->name('admin.reports.general');
    Route::get('/reports/students', [ReportController::class, 'studentsPDF'])->name('admin.reports.students');
    Route::get('/reports/faculty', [ReportController::class, 'facultyPDF'])->name('admin.reports.faculty');
    Route::get('/reports/acquired', [ReportController::class, 'acquiredPDF'])->name('admin.reports.acquired');
    Route::get('/reports/condemned', [ReportController::class, 'condemnedPDF'])->name('admin.reports.condemned');
    Route::get('/reports/daily', [ReportController::class, 'dailyPDF'])->name('admin.reports.daily');
    Route::get('/reports/circulation', [ReportController::class, 'circulationPDF'])->name('admin.reports.circulation');
    Route::get('/reports/book-recommendations', [ReportController::class, 'bookRecommendationsPDF'])->name('admin.reports.book-recommendations');
    
    // Commented out: Seasonal Books Feature
    // Route::get('/reports/seasonal', [ReportController::class, 'seasonalPDF'])->name('admin.reports.seasonal');

    // Report Presets API
    Route::get('/admin/report-presets', [ReportPresetController::class, 'index'])->name('admin.report-presets.index');
    Route::post('/admin/report-presets', [ReportPresetController::class, 'store'])->name('admin.report-presets.store');
    Route::delete('/admin/report-presets/{preset}', [ReportPresetController::class, 'destroy'])->name('admin.report-presets.destroy');
    Route::post('/admin/report-presets/{preset}/default', [ReportPresetController::class, 'setDefault'])->name('admin.report-presets.default');

    // Commented out: Seasonal Books Config API
    // Route::post('/admin/seasonal/config', [ReportController::class, 'updateSeasonalConfig'])->name('admin.seasonal.config');
    // Route::post('/reports/seasonal/config', [ReportController::class, 'updateSeasonalConfig'])->name('admin.reports.seasonal.config');

    // Global Archive Scanner API
    Route::post('/admin/archive-scanner/scan', [GlobalArchiveController::class, 'scan'])->name('admin.archive-scanner.scan');

    // Book CSV/Excel Import API
    Route::post('/admin/books/import/preview', [BookController::class, 'importPreview'])->name('admin.books.import.preview');
    Route::post('/admin/books/import/process', [BookController::class, 'importProcess'])->name('admin.books.import.process');

    Route::get('/admin/operations', [OperationsController::class, 'index'])->name('admin.operations.index');
    Route::get('/admin/operations/damage', [OperationsController::class, 'damageReports'])->name('admin.operations.damage');
    Route::get('/admin/operations/overdue', [OperationsController::class, 'overdueBans'])->name('admin.operations.overdue');
    Route::get('/admin/operations/logs', function () {
        return redirect()->route('admin.audit-logs.index');
    })->name('admin.operations.logs');
    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');
    Route::get('/admin/operations/config', [OperationsController::class, 'config'])->name('admin.operations.config');
    Route::post('/admin/operations/damage-reports/{report}/resolve', [OperationsController::class, 'resolveDamageReport'])->name('admin.operations.damage.resolve');
    Route::post('/admin/operations/damage-reports/{report}/dismiss', [OperationsController::class, 'dismissDamageReport'])->name('admin.operations.damage.dismiss');
    Route::post('/admin/operations/requisitions/{requisition}/status', [OperationsController::class, 'updateRequisitionStatus'])->name('admin.operations.requisition.status');
    Route::post('/admin/operations/penalties/{penalty}/status', [OperationsController::class, 'markPenaltyPaid'])->name('admin.operations.penalty.status');
    Route::post('/admin/borrow-history/{usage}/damage', [OperationsController::class, 'recordDamage'])->name('admin.penalty.damage');
    Route::post('/admin/operations/manual-penalty', [OperationsController::class, 'manualPenalty'])->name('admin.operations.manual-penalty');
    Route::post('/admin/operations/record-damage-warning', [OperationsController::class, 'recordDamageWarning'])->name('admin.operations.record-damage-warning');
    Route::post('/admin/operations/apply-overdue-fine', [OperationsController::class, 'applyOverdueFine'])->name('admin.operations.apply-overdue-fine');
    Route::post('/admin/operations/mark-overdue-resolved', [OperationsController::class, 'markOverdueResolved'])->name('admin.operations.mark-overdue-resolved');
    Route::get('/admin/users/search', [OperationsController::class, 'userSearch'])->name('admin.users.search');
    Route::post('/admin/operations/returns/{usage}/acknowledge', [OperationsController::class, 'acknowledgeKioskReturn'])->name('admin.operations.return.acknowledge');
    Route::post('/admin/operations/reservations/{reservation}/resend', [OperationsController::class, 'resendReservationNotification'])->name('admin.operations.reservation.resend');
    Route::post('/admin/operations/unban', [OperationsController::class, 'unbanPatron'])->name('admin.operations.unban');
    Route::post('/admin/operations/unsuspend', [OperationsController::class, 'unsuspendPatron'])->name('admin.operations.unsuspend');
    Route::post('/admin/operations/ban-patron', [OperationsController::class, 'banPatronManually'])->name('admin.operations.ban-patron');
    Route::post('/admin/operations/simulate-overdue', [OperationsController::class, 'simulateOverdueLoan'])->name('admin.operations.simulate-overdue');
    Route::post('/admin/operations/debug-overdue', [OperationsController::class, 'debugCreateOverdue'])->name('admin.operations.debug-overdue');
});

// ============================================
// ADMIN AUTHENTICATED ROUTES (PREFIXED)
// ============================================
Route::prefix('admin')->name('admin.')->middleware('auth:web')->group(function () {
    // Courses
    Route::get('/courses', [App\Http\Controllers\Admin\CourseController::class, 'index'])->name('courses.index');
    Route::post('/courses', [App\Http\Controllers\Admin\CourseController::class, 'store'])->name('courses.store');
    Route::put('/courses/{course}', [App\Http\Controllers\Admin\CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [App\Http\Controllers\Admin\CourseController::class, 'destroy'])->name('courses.destroy');


    // Faculty Management
    Route::get('/faculties', [FacultyController::class, 'index'])->name('faculties.index');
    Route::get('/faculties/{faculty}/edit', [FacultyController::class, 'edit'])->name('faculties.edit');
    Route::put('/faculties/{faculty}', [FacultyController::class, 'update'])->name('faculties.update');
    Route::delete('/faculties/{faculty}', [FacultyController::class, 'destroy'])->name('faculties.destroy');

// Unified User Approval Requests (students + faculty)
Route::get('/account-requests', [App\Http\Controllers\Admin\UserApprovalController::class, 'index'])->name('account.requests');

// Student approval routes (preserve old names)
Route::post('/account-requests/students/{id}/approve', [App\Http\Controllers\Admin\UserApprovalController::class, 'approveStudent'])->name('account.approve');
Route::post('/account-requests/students/{id}/reject', [App\Http\Controllers\Admin\UserApprovalController::class, 'rejectStudent'])->name('account.reject');

// Faculty approval routes (preserve old names so sidebar faculty badge still works)
Route::post('/account-requests/faculties/{id}/approve', [App\Http\Controllers\Admin\UserApprovalController::class, 'approveFaculty'])->name('faculty.approve');
Route::post('/account-requests/faculties/{id}/reject', [App\Http\Controllers\Admin\UserApprovalController::class, 'rejectFaculty'])->name('faculty.reject');

// Staff approval routes removed — staff accounts no longer managed through this UI

// Optional: redirect old faculty-requests page to new unified page
Route::get('/faculty-requests', function () {
    return redirect()->route('admin.account.requests');
})->name('faculty.requests');

    // Books Management
    Route::get('/books', function () {
        $books = Book::with('course')
            ->withCount(['reservations as pending_reservations_count' => fn($q) => $q->where('status', 'pending')])
            ->orderBy('title')->get();
        $collectionTypes = CollectionType::whereNull('archived_at')->orderBy('name')->get();
        $courses = Course::orderBy('name')->get();
        $usages = \App\Models\BookUsage::with(['book', 'student', 'faculty'])->orderByDesc('created_at')->get();
        return view('books', compact('books', 'collectionTypes', 'courses', 'usages'));
    })->name('books');

    Route::get('/books/create', function () {
        $courses = Course::orderBy('name')->get();
        $collectionTypes = CollectionType::whereNull('archived_at')->orderBy('name')->get();
        return view('add-book', compact('courses', 'collectionTypes'));
    })->name('books.create');

    Route::post('/books/store', [BookController::class, 'store'])->name('books.store');

    Route::get('/books/fetch-by-isbn/{isbn}', [BookController::class, 'fetchByIsbn'])->name('books.fetch-by-isbn');
Route::get('/books/fetch-by-callnumber/{callNumber}', [BookController::class, 'fetchByCallNumber'])->name('books.fetch-by-callnumber');

    Route::get('/books/edit/{id}', function ($id) {
        $book = Book::findOrFail($id);
        $courses = Course::orderBy('name')->get();
        $collectionTypes = CollectionType::whereNull('archived_at')->orderBy('name')->get();
        return view('edit-book', compact('book', 'courses', 'collectionTypes'));
    })->name('books.edit');

    Route::post('/books/update/{book}', [BookController::class, 'update'])->name('books.update');
    Route::post('/books/delete/{book}', [BookController::class, 'destroy'])->name('books.delete');
    Route::patch('/admin/books/{book}/edition', function (\App\Models\Book $book, \Illuminate\Http\Request $request) {
        $request->validate(['edition' => 'nullable|string|max:100']);
        $book->update(['edition' => $request->input('edition') ?: null]);
        return response()->json(['ok' => true]);
    })->name('admin.books.edition.update');

    Route::get('/books/deleted', function () {
        $books = Book::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        return view('archived-books', compact('books'));
    })->name('books.deleted');

    Route::post('/books/restore/{id}', function ($id) {
        $book = Book::onlyTrashed()->findOrFail($id);
        $book->restore();
        return redirect('/admin/books/deleted')->with('message', 'Book restored.');
    })->name('books.restore');

    Route::post('/books/archive/{book}', [BookController::class, 'archive'])->name('books.archive');
    Route::post('/books/unarchive/{book}', [BookController::class, 'unarchive'])->name('books.unarchive');

    Route::post('/books/condemn/{book}', [BookController::class, 'condemn'])->name('books.condemn');
    Route::post('/books/uncondemn/{book}', [BookController::class, 'uncondemn'])->name('books.uncondemn');

    // Archive Scanner
    Route::get('/archive-scanner', function () {
        return view('admin.archive-scanner');
    })->name('archive.scanner');

    // Printing
    Route::post('/books/{book}/print-label', function ($bookId, \App\Services\ReceiptPrinterService $printer) {
        $book = \App\Models\Book::findOrFail($bookId);
        try {
            $printer->printBookLabel($book->barcode, $book->title, $book->author);
            return back()->with('message', 'Label printed!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Printer error: ' . $e->getMessage());
        }
    })->name('books.print-label');

    // Trend Tracker
    Route::get('/trend-tracker', [TrendTrackerController::class, 'index'])->name('trend-tracker');

    // Book Purchase Suggestions (renamed from AI Purchase Suggestions)
    Route::get('/book-suggestions', [AIPurchaseSuggestionsController::class, 'index'])->name('book.suggestions');
    Route::get('/book-suggestions/check-edition/{book}', [AIPurchaseSuggestionsController::class, 'checkNewEditions'])->name('book.suggestions.check-edition');
    Route::post('/book-suggestions/requisitions/{requisition}/status', [AIPurchaseSuggestionsController::class, 'updateRequisitionStatus'])->name('book.suggestions.requisition.status');

    // Admin Impersonation — uses dedicated test accounts (pupsjlibrisnexus@gmail.com)
    Route::get('/impersonate/student', function () {
        $student = App\Models\Student::where('email', 'pupsjlibrisnexus@gmail.com')
            ->whereNull('deleted_at')->first()
            ?? App\Models\Student::whereNull('deleted_at')->first();
        if (!$student) {
            return redirect()->back()->with('error', 'No student account found to view as.');
        }
        session(['impersonated_by' => auth()->id()]);
        auth()->guard('student')->loginUsingId($student->id);
        return redirect('/student/dashboard')->with('success', 'Viewing as student: ' . $student->first_name . ' ' . $student->last_name);
    })->name('impersonate.test');

    Route::post('/impersonate/stop', function () {
        if (session('impersonated_by')) {
            $adminId = session('impersonated_by');
            session()->forget('impersonated_by');
            auth()->loginUsingId($adminId);
            return redirect('/dashboard')->with('success', 'Returned to admin dashboard.');
        }
        return redirect('/dashboard')->with('error', 'No impersonation session found.');
    })->name('impersonate.stop');

    Route::get('/impersonate/faculty', function () {
        $faculty = App\Models\Faculty::where('email', 'pupsjlibrisnexus@gmail.com')
            ->whereNull('deleted_at')->first()
            ?? App\Models\Faculty::whereNull('deleted_at')->first();
        if (!$faculty) {
            return redirect()->back()->with('error', 'No faculty account found to view as.');
        }
        session(['impersonated_by' => auth()->id()]);
        auth()->guard('faculty')->login($faculty);
        return redirect('/faculty/dashboard')->with('success', 'Viewing as faculty: ' . $faculty->first_name . ' ' . $faculty->last_name);
    })->name('impersonate.faculty');

// Programs & Specialties (merged courses + specialties)
// Programs & Specialties (merged courses + specialties)
Route::get('/programs', [App\Http\Controllers\Admin\ProgramController::class, 'index'])->name('programs.index');
Route::post('/programs/courses', [App\Http\Controllers\Admin\ProgramController::class, 'storeCourse'])->name('programs.courses.store');
Route::put('/programs/courses/{course}', [App\Http\Controllers\Admin\ProgramController::class, 'updateCourse'])->name('programs.courses.update');
Route::delete('/programs/courses/{course}', [App\Http\Controllers\Admin\ProgramController::class, 'destroyCourse'])->name('programs.courses.destroy');
Route::post('/programs/specialties', [App\Http\Controllers\Admin\ProgramController::class, 'storeSpecialty'])->name('programs.specialties.store');
Route::put('/programs/specialties/{specialty}', [App\Http\Controllers\Admin\ProgramController::class, 'updateSpecialty'])->name('programs.specialties.update');
Route::delete('/programs/specialties/{specialty}', [App\Http\Controllers\Admin\ProgramController::class, 'destroySpecialty'])->name('programs.specialties.destroy');

    // Archived faculties
    Route::get('/faculties/archived', [FacultyController::class, 'archived'])->name('faculties.archived');
    Route::post('/faculties/{id}/restore', [FacultyController::class, 'restore'])->name('faculties.restore');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Process Documentation & User Manual
    Route::get('/documentation', function () {
        return view('admin.documentation');
    })->name('documentation');

    Route::get('/user-manual', function () {
        return view('admin.user-manual');
    })->name('user-manual');

    // Koha ILS Integration
    Route::get('/koha', [\App\Http\Controllers\Admin\KohaController::class, 'index'])->name('koha.index');
    Route::post('/koha/config', [\App\Http\Controllers\Admin\KohaController::class, 'saveConfig'])->name('koha.config');
    Route::post('/koha/sync-books', [\App\Http\Controllers\Admin\KohaController::class, 'syncBooks'])->name('koha.sync-books');
    Route::post('/koha/sync-circulation', [\App\Http\Controllers\Admin\KohaController::class, 'syncCirculation'])->name('koha.sync-circulation');
    Route::post('/koha/test', [\App\Http\Controllers\Admin\KohaController::class, 'testConnection'])->name('koha.test');

    // Collection Types
    Route::get('/collection-types', [CollectionTypeController::class, 'index'])->name('collection-types.index');
    Route::post('/collection-types', [CollectionTypeController::class, 'store'])->name('collection-types.store');
    Route::put('/collection-types/{collectionType}', [CollectionTypeController::class, 'update'])->name('collection-types.update');
    Route::patch('/collection-types/{collectionType}/flags', [CollectionTypeController::class, 'updateFlags'])->name('collection-types.flags');
    Route::post('/collection-types/{collectionType}/archive', [CollectionTypeController::class, 'archive'])->name('collection-types.archive');
    Route::post('/collection-types/{collectionType}/restore', [CollectionTypeController::class, 'restore'])->name('collection-types.restore');
    Route::delete('/collection-types/{collectionType}', [CollectionTypeController::class, 'destroy'])->name('collection-types.destroy');
});

// ============================================
// STUDENT MANAGEMENT (ADMIN) - also protected
// ============================================
Route::middleware('auth:web')->group(function () {
    // Merged Users Management (Students + Faculty)
    Route::get('/users', function () {
        $students = \App\Models\Student::with('usages')->orderBy('last_name')->orderBy('first_name')->get();
        $faculties = \App\Models\Faculty::with(['specialties', 'bookUsages'])->orderBy('last_name')->orderBy('first_name')->get();
        $programMap = \App\Models\Program::all()->flatMap(function ($p) {
            return [$p->name => $p->code, $p->code => $p->code];
        })->all();
        return view('admin.users', compact('students', 'faculties', 'programMap'));
    })->name('admin.users');

    Route::get('/users/archived', function () {
    $archivedStudents = \App\Models\Student::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
    $archivedFaculties = \App\Models\Faculty::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
    return view('admin.users-archived', compact('archivedStudents', 'archivedFaculties'));
})->name('admin.users.archived');

    Route::get('/students', function () {
        $students = Student::orderBy('last_name')->orderBy('first_name')->get();
        // Build a lookup so both full names AND codes map to their code
        $programMap = \App\Models\Program::all()->flatMap(function ($p) {
            return [$p->name => $p->code, $p->code => $p->code];
        })->all();
        return view('students', compact('students', 'programMap'));
    })->name('admin.students');


    Route::post('/students/delete/{id}', function ($id) {
        $student = Student::findOrFail($id);
        $name = $student->first_name . ' ' . $student->last_name;
        $num = $student->student_number;
        $student->delete();

        \App\Services\AuditLogger::log('user_archived', 'Archived student: ' . $name . ' (' . ($num ?? 'ID: ' . $id) . ')', [
            'affected_user'  => $name,
            'student_id'     => $id,
            'student_number' => $num,
        ], 'users');

        return redirect('/students')->with('message', 'Student removed (can be restored from Deleted Students).');
    })->name('admin.students.delete');

    Route::post('/students/archive/{id}', function ($id) {
        $student = Student::findOrFail($id);
        $name = $student->first_name . ' ' . $student->last_name;
        $num = $student->student_number;
        $student->delete();

        \App\Services\AuditLogger::log('user_archived', 'Archived student: ' . $name . ' (' . ($num ?? 'ID: ' . $id) . ')', [
            'affected_user'  => $name,
            'student_id'     => $id,
            'student_number' => $num,
        ], 'users');

        return redirect()->back()->with('message', 'Student archived successfully.');
    })->name('admin.students.archive');

    Route::get('/students/deleted', function () {
        $students = Student::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        return view('archived-students', compact('students'));
    })->name('admin.students.deleted');

    Route::post('/students/restore/{id}', function ($id) {
        $student = Student::onlyTrashed()->findOrFail($id);
        $student->restore();

        \App\Services\AuditLogger::log('user_restored', 'Restored student: ' . $student->first_name . ' ' . $student->last_name . ' (' . ($student->student_number ?? 'ID: ' . $id) . ')', [
            'affected_user'  => $student->first_name . ' ' . $student->last_name,
            'student_id'     => $id,
            'student_number' => $student->student_number,
        ], 'users');

        return redirect()->route('admin.users.archived')->with('message', 'Student restored.');
    })->name('admin.students.restore');

    // Borrow History
    Route::get('/borrow-history', function () {
        $usages = BookUsage::with(['student', 'book', 'faculty'])->orderByDesc('created_at')->get();
        return view('borrow-history', compact('usages'));
    })->name('admin.borrow-history');

    Route::post('/borrow-history/{usage}/acknowledge', function (\App\Models\BookUsage $usage, \Illuminate\Http\Request $request) {
        $admin = auth()->user();
        $adminName = $admin->name ?? $admin->email ?? 'Admin';
        
        // Find the first empty acknowledge slot
        if (!$usage->acknowledged_by_admin1) {
            $usage->update([
                'acknowledged_by_admin1' => $adminName,
                'acknowledged_at_admin1' => now(),
            ]);
        } elseif (!$usage->acknowledged_by_admin2) {
            $usage->update([
                'acknowledged_by_admin2' => $adminName,
                'acknowledged_at_admin2' => now(),
            ]);
        } elseif (!$usage->acknowledged_by_admin3) {
            $usage->update([
                'acknowledged_by_admin3' => $adminName,
                'acknowledged_at_admin3' => now(),
            ]);
        } else {
            return back()->with('error', 'Maximum 3 acknowledgments reached.');
        }

        \App\Services\AuditLogger::log('borrow_acknowledged', 'Acknowledged circulation record for book "' . ($usage->book->title ?? 'Book #' . $usage->book_id) . '" by ' . $adminName, [
            'usage_id'      => $usage->id,
            'affected_book' => $usage->book->title ?? 'Book #' . $usage->book_id,
            'book_id'       => $usage->book_id,
            'admin_name'    => $adminName,
        ], 'circulation');
        
        return back()->with('message', 'Borrow acknowledged successfully.');
    })->name('admin.borrow-history.acknowledge');
});

// ============================================
// PUBLIC RETURN SCANNER (NO LOGIN REQUIRED)
// ============================================
Route::get('/return-scanner', function () {
    return view('return-scanner');
})->name('public.return');

Route::post('/return-scanner/return', function () {
    $book = Book::findOrFail(request('book_id'));

    $usage = BookUsage::where('book_id', $book->id)
        ->where('status', 'active')
        ->first();

    if (!$usage) {
        return response()->json(['message' => 'This book is not currently borrowed.']);
    }

    $usage->update([
        'status' => 'completed',
        'time_out' => now(),
    ]);

    $book->increment('copies');

    $stillActive = BookUsage::where('book_id', $book->id)
        ->where('status', 'active')
        ->exists();

    if (!$stillActive) {
        $book->status = 'available';
        $book->save();
    }

    // ── Create damage report if condition flags were submitted ──
    $conditionFlags = array_filter((array) request()->input('condition_flags', []));
    if (!empty($conditionFlags)) {
        $existingReport = LibraryDamageReport::where('book_usage_id', $usage->id)
            ->where('status', 'pending')
            ->first();

        if (!$existingReport) {
            LibraryDamageReport::create([
                'book_usage_id' => $usage->id,
                'student_id'    => $usage->student_id,
                'faculty_id'    => $usage->faculty_id,
                'book_id'       => $usage->book_id,
                'patron_note'   => implode(', ', $conditionFlags),
                'status'        => 'pending',
            ]);
        }
    }

    // ── Notify users who want this book ──
    $notifications = BookNotification::where('book_id', $book->id)->get();

    foreach ($notifications as $notification) {
        if ($notification->notifier_email) {
            try {
                Mail::to($notification->notifier_email)
                    ->send(new BookAvailableMail($notification));
            } catch (\Exception $e) {
                \Log::error('Notify email failed: ' . $e->getMessage());
            }
        }
    }

    // Delete all notifications for this book (they've been notified)
    BookNotification::where('book_id', $book->id)->delete();

    return response()->json(['message' => 'Book returned successfully!']);
});


Route::get('/books/{book}/usage-history', function ($bookId) {
    $usages = \App\Models\BookUsage::with(['student', 'faculty', 'book'])
        ->where('book_id', $bookId)
        ->orderByDesc('time_in')
        ->get();
    return response()->json($usages);
});
// ============================================
// AJAX ENDPOINTS (safe to leave without auth)
// ============================================
Route::get('/books/next-barcode', function () {
    $collection = request('collection');
    $locClass = request('loc_class');
    $prefix = match($collection) {
        'Filipiniana' => 'FIL',
        'Library of Congress' => 'LoC' . ($locClass ? '-' . $locClass : ''),
        'Thesis Collection' => 'THS',
        'Fictions' => 'FIC',
        'Special Collections' => 'SPC',
        default => 'GEN'
    };
    $lastBook = Book::where('barcode', 'LIKE', $prefix . '-%')
        ->orderByRaw('LENGTH(barcode) DESC, barcode DESC')->first();
    $nextNumber = 1;
    if ($lastBook) {
        $lastNumber = (int) substr($lastBook->barcode, strrpos($lastBook->barcode, '-') + 1);
        $nextNumber = $lastNumber + 1;
    }
    $suggestedBarcode = $prefix . '-' . $nextNumber;
    return response()->json(['barcode' => $suggestedBarcode]);
});

Route::get('/books/barcode/{barcode}', function ($barcode) {
    $book = \App\Models\Book::where('barcode', $barcode)->first();
    if (!$book) abort(404);
    return response()->json($book);
});

Route::get('/books/json/{id}', function ($id) {
    $book = \App\Models\Book::find($id);
    if (!$book) abort(404);
    return response()->json($book);
});

// ============================================
// AI-POWERED RECOMMENDATIONS (Student)
// ============================================
Route::prefix('student')->name('student.')->middleware('auth:student')->group(function () {
    Route::get('/ai-recommendations', [AIRecommendationsController::class, 'index'])->name('ai.recommendations');
});

// ============================================
// FACULTY AUTH
// ============================================
Route::get('/faculty/login', [FacultyAuthController::class, 'showLogin'])->name('faculty.login');
Route::post('/faculty/login', [FacultyAuthController::class, 'login'])->name('faculty.login.post');
Route::get('/faculty/register', [FacultyAuthController::class, 'showRegister'])->name('faculty.register');
Route::post('/faculty/register', [FacultyAuthController::class, 'register'])->name('faculty.register.post');


Route::middleware('auth:faculty')->prefix('faculty')->name('faculty.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [FacultyAuthController::class, 'dashboard'])->name('dashboard');

    // Auth
    Route::post('/logout', [FacultyAuthController::class, 'logout'])->name('logout');

    // Catalog
    Route::get('/borrow', [FacultyBorrowController::class, 'showBorrow'])->name('catalog');

    // Book detail
    Route::get('/books/{book}', [FacultyBorrowController::class, 'getBook'])->name('book.show');

    // Actions
    Route::post('/borrow', [FacultyBorrowController::class, 'borrow'])->name('borrow');
    Route::post('/return', [FacultyBorrowController::class, 'returnBook'])->name('return');
    Route::post('/notify/{book}', [FacultyBorrowController::class, 'notify'])->name('notify');
    Route::post('/extend-borrow', [FacultyBorrowController::class, 'extendBorrow'])->name('extend-borrow');

    // AJAX
    Route::get('/active-borrows', [FacultyBorrowController::class, 'getActiveBorrows'])->name('active-borrows');

    // History
    Route::get('/history', [FacultyBorrowController::class, 'history'])->name('history');

    Route::get('reservations', [FacultyReservationController::class, 'index'])->name('reservations.index');
    Route::post('reservations', [FacultyReservationController::class, 'store'])->name('reservations.store');
    Route::post('reservations/{reservation}/cancel', [FacultyReservationController::class, 'destroy'])->name('reservations.destroy');

    Route::get('requisitions', [FacultyRequisitionController::class, 'index'])->name('requisitions.index');
    Route::get('requisitions/create', [FacultyRequisitionController::class, 'create'])->name('requisitions.create');
    Route::post('requisitions', [FacultyRequisitionController::class, 'store'])->name('requisitions.store');

    Route::get('penalties', [FacultyPenaltyController::class, 'index'])->name('penalties.index');

    // Profile
    Route::get('/profile', [FacultyAuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [FacultyAuthController::class, 'updateProfile'])->name('profile.update');



});

// ============================================
// KIOSK PLATFORM
// ============================================
use App\Http\Controllers\KioskController;

Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/',                   [KioskController::class, 'index'])->name('index');
    Route::get('/sign-in',            [KioskController::class, 'showLogin'])->name('sign-in');
    Route::post('/login',             [KioskController::class, 'login'])->name('login');
    Route::post('/logout',            [KioskController::class, 'logout'])->name('logout');

    Route::get('/return-public',      [KioskController::class, 'showPublicReturn'])->name('return-public');
    Route::post('/return-public',     [KioskController::class, 'publicReturn'])->name('return-public.post');
    Route::get('/return-lookup',      [KioskController::class, 'returnLookup'])->name('return-lookup');

    Route::get('/dashboard',          [KioskController::class, 'dashboard'])->name('dashboard')->middleware('kiosk.auth');
    Route::post('/borrow',            [KioskController::class, 'borrow'])->name('borrow')->middleware('kiosk.auth');
    Route::post('/return',            [KioskController::class, 'return'])->name('return')->middleware('kiosk.auth');
    Route::post('/smart-scan',        [KioskController::class, 'smartScan'])->name('smart-scan')->middleware('kiosk.auth');
    Route::post('/report-damage',     [KioskController::class, 'reportDamage'])->name('report-damage')->middleware('kiosk.auth');

    Route::get('/register',           [KioskController::class, 'showRegister'])->name('register');
    Route::post('/register',          [KioskController::class, 'register'])->name('register.post');

    Route::get('/forgot-password',    [KioskController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/send-otp',          [KioskController::class, 'sendOtp'])->name('send-otp');
    Route::get('/verify-otp',         [KioskController::class, 'showVerifyOtp'])->name('verify-otp');
    Route::post('/verify-otp',        [KioskController::class, 'verifyOtp'])->name('verify-otp.post');
    Route::get('/reset-password',     [KioskController::class, 'showResetPassword'])->name('reset-password');
    Route::post('/reset-password',    [KioskController::class, 'resetPassword'])->name('reset-password.post');
});