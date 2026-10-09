<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookRequisition;
use App\Models\BookUsage;
use App\Models\Faculty;
use App\Models\LibraryDamageReport;
use App\Models\LibraryPenalty;
use App\Models\PatronBan;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationReadyMail;

class OperationsController extends Controller
{
    public function index()
    {
        // All filtering/sorting is done client-side via JS; load everything up front
        $damageReports = LibraryDamageReport::with(['book', 'student', 'faculty', 'bookUsage'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $damageHistory = LibraryDamageReport::with(['book', 'student', 'faculty', 'adminUser'])
            ->orderBy('updated_at', 'desc')
            ->limit(200)
            ->get();

        $penaltyHistory = LibraryPenalty::with(['book', 'student', 'faculty', 'adminUser'])
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        $activityLogs = ActivityLog::with('adminUser')
            ->orderBy('created_at', 'desc')
            ->limit(300)
            ->get();

        $requisitionCount = BookRequisition::whereIn('status', ['submitted'])->count();

        $pendingPenalties = LibraryPenalty::with(['book', 'student', 'faculty', 'bookUsage', 'adminUser'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $feeLevels = config('library_fees.damage_levels', []);

        $libraryConfig = [
            'damage_penalty_amount'        => Setting::getValue('damage_penalty_amount', 500),
            'lost_penalty_amount'          => Setting::getValue('lost_penalty_amount', 1000),
            'max_borrow_days_student'      => Setting::getValue('max_borrow_days_student', 7),
            'overdue_fines_enabled'        => Setting::getValue('overdue_fines_enabled', '0'),
            'overdue_fine_per_day_student' => Setting::getValue('overdue_fine_per_day_student', 10),
            'overdue_fine_per_day_faculty' => Setting::getValue('overdue_fine_per_day_faculty', 20),
            'student_take_home_allowed'    => Setting::getValue('student_take_home_allowed', '1'),
            'ban_duration_days'            => Setting::getValue('ban_duration_days', 0),
            'warning_ban_threshold'        => Setting::getValue('warning_ban_threshold', 3),
        ];

        $overdueBorrows = BookUsage::with(['book', 'student', 'faculty'])
            ->where('is_overdue_flagged', true)
            ->where('status', 'active')
            ->orderBy('time_in')
            ->get();

        $readyReservations = Reservation::with(['book', 'student', 'faculty'])
            ->where('status', 'ready')
            ->orderBy('updated_at')
            ->get();

        $pendingKioskReturns = BookUsage::with(['book', 'student', 'faculty'])
            ->where('status', 'completed')
            ->where('return_kiosk', true)
            ->whereNull('return_acknowledged_at')
            ->orderBy('time_out')
            ->limit(30)
            ->get();

        $banHistory = PatronBan::with(['student', 'faculty', 'bannedByAdmin', 'unbannedByAdmin'])
            ->orderByDesc('banned_at')
            ->get();

        $bannedStudents = Student::where('is_banned', true)
            ->with(['patronBans' => fn($q) => $q->whereNull('unbanned_at')->latest('banned_at')])
            ->get();
        $bannedFaculty = Faculty::where('is_banned', true)
            ->with(['patronBans' => fn($q) => $q->whereNull('unbanned_at')->latest('banned_at')])
            ->get();
        $bannedPatrons = $bannedStudents->map(fn($s) => [
            'type'  => 'student',
            'model' => $s,
            'ban'   => $s->patronBans->first(),
            'kind'  => 'banned',
        ])->merge($bannedFaculty->map(fn($f) => [
            'type'  => 'faculty',
            'model' => $f,
            'ban'   => $f->patronBans->first(),
            'kind'  => 'banned',
        ]))->sortByDesc(fn($p) => optional($p['ban'])->banned_at)->values();

        $suspendedStudents = Student::whereNotNull('borrowing_suspended_until')
            ->where('borrowing_suspended_until', '>', now())
            ->where('is_banned', false)
            ->get();
        $suspendedFaculty = Faculty::whereNotNull('borrowing_suspended_until')
            ->where('borrowing_suspended_until', '>', now())
            ->where('is_banned', false)
            ->get();
        $suspendedPatrons = $suspendedStudents->map(fn($s) => [
            'type'  => 'student',
            'model' => $s,
            'ban'   => null,
            'kind'  => 'suspended',
        ])->merge($suspendedFaculty->map(fn($f) => [
            'type'  => 'faculty',
            'model' => $f,
            'ban'   => null,
            'kind'  => 'suspended',
        ]))->sortByDesc(fn($p) => optional($p['model']->borrowing_suspended_until))->values();

        $warnedStudents = Student::where('damage_warning_count', '>', 0)
            ->where('is_banned', false)
            ->where(function ($q) {
                $q->whereNull('borrowing_suspended_until')
                  ->orWhere('borrowing_suspended_until', '<=', now());
            })
            ->get();
        $warnedFaculty = Faculty::where('damage_warning_count', '>', 0)
            ->where('is_banned', false)
            ->where(function ($q) {
                $q->whereNull('borrowing_suspended_until')
                  ->orWhere('borrowing_suspended_until', '<=', now());
            })
            ->get();
        $warnedPatrons = $warnedStudents->map(fn($s) => [
            'type'  => 'student',
            'model' => $s,
            'ban'   => null,
            'kind'  => 'warned',
        ])->merge($warnedFaculty->map(fn($f) => [
            'type'  => 'faculty',
            'model' => $f,
            'ban'   => null,
            'kind'  => 'warned',
        ]))->sortByDesc(fn($p) => $p['model']->damage_warning_count)->values();

        $restrictedPatrons = $bannedPatrons->concat($suspendedPatrons)->concat($warnedPatrons)->values();

        // Debug: data for the "Simulate Overdue Borrow" tool on the Configuration tab
        $debugBooks    = Book::where('status', '!=', 'archived')->orderBy('title')->get(['id', 'title', 'barcode', 'copies']);
        $debugStudents = Student::orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $debugFaculty  = Faculty::orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('admin.operations.index', compact(
            'damageReports',
            'damageHistory',
            'penaltyHistory',
            'activityLogs',
            'requisitionCount',
            'feeLevels',
            'pendingPenalties',
            'libraryConfig',
            'overdueBorrows',
            'readyReservations',
            'pendingKioskReturns',
            'banHistory',
            'bannedPatrons',
            'suspendedPatrons',
            'warnedPatrons',
            'restrictedPatrons',
            'debugBooks',
            'debugStudents',
            'debugFaculty'
        ));
    }

    public function damageReports()
    {
        $damageReports = LibraryDamageReport::with(['book', 'student', 'faculty', 'bookUsage'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
        $feeLevels = config('library_fees.damage_levels', []);
        $pendingKioskReturns = BookUsage::with(['book', 'student', 'faculty'])
            ->where('status', 'completed')
            ->where('return_kiosk', true)
            ->whereNull('return_acknowledged_at')
            ->orderBy('time_out')
            ->limit(30)
            ->get();
        $pendingPenalties = LibraryPenalty::with(['book', 'student', 'faculty', 'bookUsage', 'adminUser'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.operations.damage', compact('damageReports', 'feeLevels', 'pendingKioskReturns', 'pendingPenalties'));
    }

    public function overdueBans()
    {
        $overdueBorrows = BookUsage::with(['book', 'student', 'faculty'])
            ->where('is_overdue_flagged', true)
            ->where('status', 'active')
            ->orderBy('time_in')
            ->get();

        $banHistory = PatronBan::with(['student', 'faculty', 'bannedByAdmin', 'unbannedByAdmin'])
            ->orderByDesc('banned_at')
            ->get();

        $bannedStudents = Student::where('is_banned', true)
            ->with(['patronBans' => fn($q) => $q->whereNull('unbanned_at')->latest('banned_at')])
            ->get();
        $bannedFaculty = Faculty::where('is_banned', true)
            ->with(['patronBans' => fn($q) => $q->whereNull('unbanned_at')->latest('banned_at')])
            ->get();
        $bannedPatrons = $bannedStudents->map(fn($s) => [
            'type'  => 'student',
            'model' => $s,
            'ban'   => $s->patronBans->first(),
            'kind'  => 'banned',
        ])->merge($bannedFaculty->map(fn($f) => [
            'type'  => 'faculty',
            'model' => $f,
            'ban'   => $f->patronBans->first(),
            'kind'  => 'banned',
        ]))->sortByDesc(fn($p) => optional($p['ban'])->banned_at)->values();

        $suspendedStudents = Student::whereNotNull('borrowing_suspended_until')
            ->where('borrowing_suspended_until', '>', now())
            ->where('is_banned', false)
            ->get();
        $suspendedFaculty = Faculty::whereNotNull('borrowing_suspended_until')
            ->where('borrowing_suspended_until', '>', now())
            ->where('is_banned', false)
            ->get();
        $suspendedPatrons = $suspendedStudents->map(fn($s) => [
            'type'  => 'student',
            'model' => $s,
            'ban'   => null,
            'kind'  => 'suspended',
        ])->merge($suspendedFaculty->map(fn($f) => [
            'type'  => 'faculty',
            'model' => $f,
            'ban'   => null,
            'kind'  => 'suspended',
        ]))->sortByDesc(fn($p) => optional($p['model']->borrowing_suspended_until))->values();

        $warnedStudents = Student::where('damage_warning_count', '>', 0)
            ->where('is_banned', false)
            ->where(function ($q) {
                $q->whereNull('borrowing_suspended_until')
                  ->orWhere('borrowing_suspended_until', '<=', now());
            })
            ->get();
        $warnedFaculty = Faculty::where('damage_warning_count', '>', 0)
            ->where('is_banned', false)
            ->where(function ($q) {
                $q->whereNull('borrowing_suspended_until')
                  ->orWhere('borrowing_suspended_until', '<=', now());
            })
            ->get();
        $warnedPatrons = $warnedStudents->map(fn($s) => [
            'type'  => 'student',
            'model' => $s,
            'ban'   => null,
            'kind'  => 'warned',
        ])->merge($warnedFaculty->map(fn($f) => [
            'type'  => 'faculty',
            'model' => $f,
            'ban'   => null,
            'kind'  => 'warned',
        ]))->sortByDesc(fn($p) => $p['model']->damage_warning_count)->values();

        $restrictedPatrons = $bannedPatrons->concat($suspendedPatrons)->concat($warnedPatrons)->values();

        return view('admin.operations.overdue', compact('overdueBorrows', 'banHistory', 'bannedPatrons', 'suspendedPatrons', 'warnedPatrons', 'restrictedPatrons'));
    }

    public function logs()
    {
        $damageHistory = LibraryDamageReport::with(['book', 'student', 'faculty', 'adminUser'])
            ->orderBy('updated_at', 'desc')
            ->limit(200)
            ->get();

        $penaltyHistory = LibraryPenalty::with(['book', 'student', 'faculty', 'adminUser'])
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        $activityLogs = ActivityLog::with('adminUser')
            ->orderBy('created_at', 'desc')
            ->limit(300)
            ->get();

        return view('admin.operations.logs', compact('damageHistory', 'penaltyHistory', 'activityLogs'));
    }

    public function config()
    {
        $libraryConfig = [
            'damage_penalty_amount'        => Setting::getValue('damage_penalty_amount', 500),
            'lost_penalty_amount'          => Setting::getValue('lost_penalty_amount', 1000),
            'max_borrow_days_student'      => Setting::getValue('max_borrow_days_student', 7),
            'overdue_fines_enabled'        => Setting::getValue('overdue_fines_enabled', '0'),
            'overdue_fine_per_day_student' => Setting::getValue('overdue_fine_per_day_student', 10),
            'overdue_fine_per_day_faculty' => Setting::getValue('overdue_fine_per_day_faculty', 20),
            'student_take_home_allowed'    => Setting::getValue('student_take_home_allowed', '1'),
            'ban_duration_days'            => Setting::getValue('ban_duration_days', 0),
            'warning_ban_threshold'        => Setting::getValue('warning_ban_threshold', 3),
            'book_requests_enabled'        => Setting::getValue('book_requests_enabled', '1'),
            'book_requests_disabled_reason'=> Setting::getValue('book_requests_disabled_reason', 'Book requisitions are temporarily paused by the library administration.'),
        ];

        return view('admin.operations.config', compact('libraryConfig'));
    }

    public function resolveDamageReport(Request $request, LibraryDamageReport $report)
    {
        if ($report->status !== 'pending') {
            return back()->with('error', 'This report is no longer pending.');
        }

        $validated = $request->validate([
            'damage_level' => 'required|integer|min:1|max:4',
            'amount' => 'nullable|numeric|min:0',
            'condemn_book' => 'sometimes|boolean',
            'suspend_borrowing_days' => 'nullable|integer|min:0|max:365',
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $levels = config('library_fees.damage_levels', []);
        $defaultAmount = $levels[(int) $validated['damage_level']]['default_amount'] ?? 0;
        $amount = $validated['amount'] ?? $defaultAmount;

        DB::transaction(function () use ($report, $validated, $amount, $request) {
            $adminUser = $request->user();
            $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';
            $patron = $report->student ?? $report->faculty;
            $patronName = $patron ? ($patron->first_name . ' ' . $patron->last_name) : 'Patron';
            $bookTitle = $report->book?->title ?? 'Book #' . $report->book_id;

            if ($report->library_penalty_id && ($existingPenalty = LibraryPenalty::find($report->library_penalty_id))) {
                $penalty = $existingPenalty;
                $penalty->update([
                    'damage_level'  => (int) $validated['damage_level'],
                    'amount'        => $amount,
                    'status'        => 'pending',
                    'due_date'      => now()->addDays(30)->toDateString(),
                    'admin_user_id' => $adminUser?->id,
                    'patron_note'   => $report->patron_note,
                    'admin_note'    => $validated['admin_notes'] ?? null,
                ]);
            } else {
                $penalty = LibraryPenalty::create([
                    'student_id'    => $report->student_id,
                    'faculty_id'    => $report->faculty_id,
                    'book_id'       => $report->book_id,
                    'book_usage_id' => $report->book_usage_id,
                    'penalty_type'  => 'damage',
                    'damage_level'  => (int) $validated['damage_level'],
                    'amount'        => $amount,
                    'status'        => 'pending',
                    'due_date'      => now()->addDays(30)->toDateString(),
                    'admin_user_id' => $adminUser?->id,
                    'patron_note'   => $report->patron_note,
                    'admin_note'    => $validated['admin_notes'] ?? null,
                ]);
            }

            if ($request->boolean('condemn_book')) {
                $book = Book::find($report->book_id);
                if ($book) {
                    $book->is_condemned = true;
                    $book->condemned_at = now();
                    $book->condemnation_reason = $validated['admin_notes'] ?? 'Condemned after damage assessment.';
                    $book->copies = max(0, (int) $book->copies - 1);
                    if ($book->copies <= 0) {
                        $book->status = 'archived';
                    }
                    $book->save();
                }
            }

            $days = (int) ($validated['suspend_borrowing_days'] ?? 0);
            if ($days > 0) {
                if ($report->student_id) {
                    $student = Student::find($report->student_id);
                    if ($student) {
                        $student->borrowing_suspended_until = now()->addDays($days);
                        $student->save();
                    }
                }
                if ($report->faculty_id) {
                    $faculty = Faculty::find($report->faculty_id);
                    if ($faculty) {
                        $faculty->borrowing_suspended_until = now()->addDays($days);
                        $faculty->save();
                    }
                }
            }

            $report->update([
                'status' => 'processed',
                'damage_level' => (int) $validated['damage_level'],
                'admin_user_id' => $adminUser?->id,
                'library_penalty_id' => $penalty->id,
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]);

            ActivityLog::create([
                'action'            => 'damage_report_resolved',
                'description'       => "Damage report #{$report->id} resolved for \"{$bookTitle}\" ({$patronName}) — Level {$validated['damage_level']}, penalty ₱" . number_format($amount, 2) . " created.",
                'performed_by_type' => 'admin',
                'performed_by_id'   => $adminUser?->id,
                'metadata'          => [
                    'performer_name'   => $adminName,
                    'damage_report_id' => $report->id,
                    'penalty_id'       => $penalty->id,
                    'affected_user'    => $patronName . ($report->student_id ? ' (Student)' : ' (Faculty)'),
                    'affected_book'    => $bookTitle,
                    'damage_level'     => $validated['damage_level'],
                    'penalty_amount'   => $amount,
                    'remarks'          => $validated['admin_notes'] ?? null,
                    'suspended_days'   => $days,
                    'condemned'        => $request->boolean('condemn_book'),
                ],
            ]);

            if ($request->boolean('record_warning')) {
                if ($report->student_id) {
                    $warnPatron = Student::find($report->student_id);
                    $warnType = 'student';
                } elseif ($report->faculty_id) {
                    $warnPatron = Faculty::find($report->faculty_id);
                    $warnType = 'faculty';
                } else {
                    $warnPatron = null;
                    $warnType = null;
                }
                if ($warnPatron && $warnType) {
                    \App\Services\PatronBanService::addWarning(
                        $warnPatron,
                        $warnType,
                        'Damage Assessment on "' . $bookTitle . '" — Level ' . $validated['damage_level'],
                        $adminUser,
                        $report->book_usage_id,
                        $report->book_id
                    );
                }
            }
        });

        return back()->with('success', 'Damage report processed and penalty recorded.');
    }

    public function dismissDamageReport(Request $request, LibraryDamageReport $report)
    {
        if ($report->status !== 'pending') {
            return back()->with('error', 'This report is no longer pending.');
        }

        $request->validate(['admin_notes' => 'nullable|string|max:5000']);

        $adminUser = $request->user();
        $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';
        $patron = $report->student ?? $report->faculty;
        $patronName = $patron ? ($patron->first_name . ' ' . $patron->last_name) : 'Patron';
        $bookTitle = $report->book?->title ?? 'Book #' . $report->book_id;

        $report->update([
            'status' => 'dismissed',
            'admin_user_id' => $adminUser?->id,
            'admin_notes' => $request->input('admin_notes'),
        ]);

        ActivityLog::create([
            'action'            => 'damage_report_dismissed',
            'description'       => "Damage report #{$report->id} dismissed for \"{$bookTitle}\" ({$patronName}).",
            'performed_by_type' => 'admin',
            'performed_by_id'   => $adminUser?->id,
            'metadata'          => [
                'performer_name'   => $adminName,
                'damage_report_id' => $report->id,
                'affected_user'    => $patronName,
                'affected_book'    => $bookTitle,
                'remarks'          => $request->input('admin_notes'),
            ],
        ]);

        return back()->with('success', 'Report dismissed.');
    }

    public function updateRequisitionStatus(Request $request, BookRequisition $requisition)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,submitted,approved,ordered,received,rejected',
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $adminUser = $request->user();
        $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';
        $oldStatus = $requisition->status;

        $requisition->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $requisition->admin_notes,
            'handled_by' => $adminUser?->id,
        ]);

        ActivityLog::create([
            'action'            => 'requisition_status_updated',
            'description'       => "Book requisition #{$requisition->id} (\"{$requisition->title}\") status changed from {$oldStatus} to {$validated['status']}.",
            'performed_by_type' => 'admin',
            'performed_by_id'   => $adminUser?->id,
            'metadata'          => [
                'performer_name' => $adminName,
                'requisition_id' => $requisition->id,
                'affected_book'  => $requisition->title,
                'before'         => ['status' => $oldStatus],
                'after'          => ['status' => $validated['status']],
                'remarks'        => $validated['admin_notes'] ?? null,
            ],
        ]);

        return back()->with('success', 'Requisition updated.');
    }

    public function markPenaltyPaid(Request $request, LibraryPenalty $penalty)
    {
        $validated = $request->validate([
            'status'      => 'required|in:paid,recorded_cash,waived,resolved',
            'admin_note'  => 'nullable|string|max:1000',
            'remarks'     => 'nullable|string|max:1000',
        ]);

        $statusInput = $validated['status'];
        $resolvedStatus = ($statusInput === 'resolved') ? 'paid' : $statusInput;
        $note = $request->filled('remarks') ? $request->input('remarks') : ($request->filled('admin_note') ? $request->input('admin_note') : $penalty->admin_note);

        $adminUser = $request->user();
        $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';
        $patron = $penalty->student ?? $penalty->faculty;
        $patronName = $patron ? ($patron->first_name . ' ' . $patron->last_name) : 'Patron';
        $patronType = $penalty->student_id ? 'student' : 'faculty';
        $bookTitle = $penalty->book?->title ?? 'General/Usage';

        $oldValues = [
            'status'     => $penalty->status,
            'admin_note' => $penalty->admin_note,
        ];

        $penalty->update([
            'status'        => $resolvedStatus,
            'admin_user_id' => $adminUser?->id,
            'admin_note'    => $note,
        ]);

        $newValues = [
            'status'     => $resolvedStatus,
            'admin_note' => $note,
        ];

        ActivityLog::create([
            'action'            => 'penalty_resolved',
            'description'       => "Penalty #{$penalty->id} (₱" . number_format($penalty->amount, 2) . ", " . ucfirst($penalty->penalty_type) . ") for {$patronName} marked as " . ucfirst(str_replace('_', ' ', $resolvedStatus)) . ".",
            'performed_by_type' => 'admin',
            'performed_by_id'   => $adminUser?->id,
            'metadata'          => [
                'performer_name' => $adminName,
                'penalty_id'     => $penalty->id,
                'affected_user'  => "{$patronName} (" . ucfirst($patronType) . ")",
                'affected_book'  => $bookTitle,
                'amount'         => $penalty->amount,
                'penalty_type'   => $penalty->penalty_type,
                'remarks'        => $note,
                'before'         => $oldValues,
                'after'          => $newValues,
            ],
        ]);

        $label = ($resolvedStatus === 'paid') ? 'Paid / Resolved' : ucfirst(str_replace('_', ' ', $resolvedStatus));
        return back()->with('success', "Penalty #{$penalty->id} successfully updated to {$label}.");
    }

    public function recordDamage(Request $request, BookUsage $usage)
    {
        $validated = $request->validate([
            'damage_amount' => 'required|numeric|min:0',
            'damage_description' => 'required|string|max:1000',
        ]);

        $adminUser = $request->user();
        $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';
        $patron = $usage->student ?? $usage->faculty;
        $patronName = $patron ? ($patron->first_name . ' ' . $patron->last_name) : 'Patron';
        $bookTitle = $usage->book?->title ?? 'Unknown Book';

        $penalty = LibraryPenalty::create([
            'book_usage_id' => $usage->id,
            'book_id'       => $usage->book_id,
            'student_id'    => $usage->student_id,
            'faculty_id'    => $usage->faculty_id,
            'penalty_type'  => 'damage',
            'amount'        => $validated['damage_amount'],
            'admin_note'    => $validated['damage_description'],
            'status'        => 'pending',
            'admin_user_id' => $adminUser?->id,
        ]);

        ActivityLog::create([
            'action'            => 'damage_penalty_created',
            'description'       => "Damage penalty of ₱" . number_format($validated['damage_amount'], 2) . " created for {$patronName} on \"{$bookTitle}\".",
            'performed_by_type' => 'admin',
            'performed_by_id'   => $adminUser?->id,
            'metadata'          => [
                'performer_name' => $adminName,
                'penalty_id'     => $penalty->id,
                'affected_user'  => $patronName,
                'affected_book'  => $bookTitle,
                'amount'         => $validated['damage_amount'],
                'remarks'        => $validated['damage_description'],
            ],
        ]);

        return back()->with('success', 'Damage penalty recorded successfully.');
    }

    public function manualPenalty(Request $request)
    {
        $validated = $request->validate([
            'user_type'    => 'required|in:student,faculty',
            'user_id'      => 'required|integer',
            'penalty_type' => 'required|in:damage,lost',
            'amount'       => 'required|numeric|min:0',
            'description'  => 'required|string|max:500',
        ]);

        $adminUser = $request->user();
        $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';

        $data = [
            'penalty_type'  => $validated['penalty_type'],
            'amount'        => $validated['amount'],
            'admin_note'    => $validated['description'],
            'status'        => 'pending',
            'admin_user_id' => $adminUser?->id,
        ];

        if ($validated['user_type'] === 'student') {
            $data['student_id'] = $validated['user_id'];
            $patron = Student::find($validated['user_id']);
        } else {
            $data['faculty_id'] = $validated['user_id'];
            $patron = Faculty::find($validated['user_id']);
        }

        $patronName = $patron ? ($patron->first_name . ' ' . $patron->last_name) : 'Patron #' . $validated['user_id'];

        $penalty = LibraryPenalty::create($data);

        ActivityLog::create([
            'action'            => 'manual_penalty_created',
            'description'       => "Manual {$validated['penalty_type']} penalty of ₱" . number_format($validated['amount'], 2) . " issued to {$patronName}.",
            'performed_by_type' => 'admin',
            'performed_by_id'   => $adminUser?->id,
            'metadata'          => [
                'performer_name' => $adminName,
                'penalty_id'     => $penalty->id,
                'affected_user'  => "{$patronName} (" . ucfirst($validated['user_type']) . ")",
                'amount'         => $validated['amount'],
                'penalty_type'   => $validated['penalty_type'],
                'remarks'        => $validated['description'],
            ],
        ]);

        return back()->with('success', 'Penalty added successfully.');
    }

    public function userSearch(Request $request)
    {
        $q = $request->query('q', '');

        $students = Student::where(function ($query) use ($q) {
            $query->where('first_name', 'like', "%{$q}%")
                  ->orWhere('last_name', 'like', "%{$q}%")
                  ->orWhere('student_number', 'like', "%{$q}%");
        })->limit(10)->get()->map(fn ($s) => [
            'id'   => $s->id,
            'name' => $s->first_name . ' ' . $s->last_name . ' (' . $s->student_number . ')',
            'type' => 'student',
        ]);

        $faculty = Faculty::where(function ($query) use ($q) {
            $query->where('first_name', 'like', "%{$q}%")
                  ->orWhere('last_name', 'like', "%{$q}%")
                  ->orWhere('employee_id', 'like', "%{$q}%");
        })->limit(10)->get()->map(fn ($f) => [
            'id'   => $f->id,
            'name' => $f->first_name . ' ' . $f->last_name . ' (Faculty)',
            'type' => 'faculty',
        ]);

        return response()->json($students->merge($faculty)->values());
    }

    public function recordDamageWarning(Request $request)
    {
        $request->merge([
            'users' => json_decode($request->input('users_json', '[]'), true) ?: [],
        ]);

        $validated = $request->validate([
            'users'         => 'required|array|min:1',
            'users.*.id'    => 'required|integer',
            'users.*.type'  => 'required|in:student,faculty',
            'type'          => 'required|in:damage,warning',
            'amount'        => 'nullable|numeric|min:0',
            'description'   => 'required|string|max:500',
            'book_usage_id' => 'nullable|integer|exists:book_usages,id',
        ]);

        $adminUser = $request->user();
        $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';
        $bannedNames = [];

        $bookId = null;
        $bookTitle = null;
        if (!empty($validated['book_usage_id'])) {
            $usage     = BookUsage::with('book')->find($validated['book_usage_id']);
            $bookId    = $usage?->book_id;
            $bookTitle = $usage?->book?->title;
        }

        $recordedPatrons = [];

        foreach ($validated['users'] as $user) {
            $data = [
                'penalty_type'  => $validated['type'],
                'amount'        => $validated['type'] === 'warning' ? 0 : ($validated['amount'] ?? 0),
                'admin_note'    => $validated['description'] ?? null,
                'status'        => 'pending',
                'book_id'       => $bookId,
                'book_usage_id' => $validated['book_usage_id'] ?? null,
                'admin_user_id' => $adminUser?->id,
            ];

            if ($user['type'] === 'student') {
                $data['student_id'] = $user['id'];
                $student = Student::find($user['id']);
                $pName = $student ? ($student->first_name . ' ' . $student->last_name) : 'Student #' . $user['id'];
                $recordedPatrons[] = $pName;

                if ($validated['type'] === 'warning' && $student) {
                    $res = \App\Services\PatronBanService::addWarning(
                        $student,
                        'student',
                        $validated['description'],
                        $adminUser,
                        $validated['book_usage_id'] ?? null,
                        $bookId
                    );
                    if (!empty($res['auto_banned'])) {
                        $bannedNames[] = $pName;
                    }
                }
            } else {
                $data['faculty_id'] = $user['id'];
                $faculty = Faculty::find($user['id']);
                $pName = $faculty ? ($faculty->first_name . ' ' . $faculty->last_name) : 'Faculty #' . $user['id'];
                $recordedPatrons[] = $pName;

                if ($validated['type'] === 'warning' && $faculty) {
                    $res = \App\Services\PatronBanService::addWarning(
                        $faculty,
                        'faculty',
                        $validated['description'],
                        $adminUser,
                        $validated['book_usage_id'] ?? null,
                        $bookId
                    );
                    if (!empty($res['auto_banned'])) {
                        $bannedNames[] = $pName;
                    }
                }
            }

            LibraryPenalty::create($data);
        }

        ActivityLog::create([
            'action'            => 'damage_warning_recorded',
            'description'       => ucfirst($validated['type']) . " recorded for: " . implode(', ', $recordedPatrons) . ($bookTitle ? " on \"{$bookTitle}\"" : "") . ".",
            'performed_by_type' => 'admin',
            'performed_by_id'   => $adminUser?->id,
            'metadata'          => [
                'performer_name' => $adminName,
                'type'           => $validated['type'],
                'amount'         => $validated['amount'] ?? 0,
                'patrons'        => $recordedPatrons,
                'affected_book'  => $bookTitle,
                'remarks'        => $validated['description'],
            ],
        ]);

        $message = 'Damage/warning recorded for ' . count($validated['users']) . ' user(s).';
        if (! empty($bannedNames)) {
            $message .= ' Borrowing has been automatically banned for: ' . implode(', ', $bannedNames) . ' (Warning threshold reached).';
        }

        return back()->with('success', $message);
    }

    public function applyOverdueFine(Request $request)
    {
        $request->validate(['usage_id' => 'required|exists:book_usages,id']);

        $usage = BookUsage::with(['student', 'faculty', 'book'])->findOrFail($request->usage_id);

        $adminUser = $request->user();
        $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';
        $patron = $usage->student ?? $usage->faculty;
        $patronName = $patron ? ($patron->first_name . ' ' . $patron->last_name) : 'Patron';
        $bookTitle = $usage->book?->title ?? 'Unknown Book';

        $daysOverdue = (int) now()->diffInDays($usage->time_in);
        $userType    = $usage->student_id ? 'student' : 'faculty';
        $rateKey     = $userType === 'student' ? 'overdue_fine_per_day_student' : 'overdue_fine_per_day_faculty';
        $rate        = (float) Setting::getValue($rateKey, $userType === 'student' ? 10 : 20);
        $amount      = $daysOverdue * $rate;

        $penalty = LibraryPenalty::create([
            'student_id'    => $usage->student_id,
            'faculty_id'    => $usage->faculty_id,
            'book_id'       => $usage->book_id,
            'book_usage_id' => $usage->id,
            'penalty_type'  => 'late',
            'amount'        => $amount,
            'status'        => 'pending',
            'due_date'      => now()->addDays(30)->toDateString(),
            'admin_user_id' => $adminUser?->id,
            'admin_note'    => "Overdue fine: {$daysOverdue} day(s) × ₱{$rate}/day",
        ]);

        $usage->is_overdue_flagged = false;
        $usage->save();

        ActivityLog::create([
            'action'            => 'overdue_fine_applied',
            'description'       => "Overdue fine of ₱" . number_format($amount, 2) . " applied to {$patronName} for \"{$bookTitle}\" ({$daysOverdue} days).",
            'performed_by_type' => 'admin',
            'performed_by_id'   => $adminUser?->id,
            'metadata'          => [
                'performer_name' => $adminName,
                'penalty_id'     => $penalty->id,
                'affected_user'  => "{$patronName} (" . ucfirst($userType) . ")",
                'affected_book'  => $bookTitle,
                'amount'         => $amount,
                'days_overdue'   => $daysOverdue,
                'rate_per_day'   => $rate,
            ],
        ]);

        return back()->with('success', "Overdue fine of ₱" . number_format($amount, 2) . " applied.");
    }

    public function markOverdueResolved(Request $request)
    {
        $request->validate(['usage_id' => 'required|exists:book_usages,id']);

        $usage = BookUsage::with(['student', 'faculty', 'book'])->findOrFail($request->usage_id);
        $usage->is_overdue_flagged = false;
        $usage->save();

        $adminUser = $request->user();
        $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';
        $patron = $usage->student ?? $usage->faculty;
        $patronName = $patron ? ($patron->first_name . ' ' . $patron->last_name) : 'Patron';
        $bookTitle = $usage->book?->title ?? 'Unknown Book';

        ActivityLog::create([
            'action'            => 'overdue_flag_cleared',
            'description'       => "Overdue flag cleared for {$patronName} on \"{$bookTitle}\".",
            'performed_by_type' => 'admin',
            'performed_by_id'   => $adminUser?->id,
            'metadata'          => [
                'performer_name' => $adminName,
                'affected_user'  => $patronName,
                'affected_book'  => $bookTitle,
            ],
        ]);

        return back()->with('success', 'Overdue flag cleared.');
    }

    public function acknowledgeKioskReturn(Request $request, BookUsage $usage)
    {
        $request->validate([
            'action'            => 'required|in:ok,damaged',
            'condition_flags'   => 'nullable|array',
            'condition_flags.*' => 'string',
            'admin_notes'       => 'nullable|string|max:1000',
            'remarks'           => 'nullable|string|max:1000',
        ]);

        $adminUser = $request->user();
        $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';
        $patron = $usage->student ?? $usage->faculty;
        $patronName = $patron ? ($patron->first_name . ' ' . $patron->last_name) : 'Patron';
        $patronType = $usage->student_id ? 'student' : 'faculty';
        $bookTitle = $usage->book?->title ?? 'Unknown Book';

        $usage->update(['return_acknowledged_at' => now()]);

        if ($request->action === 'damaged') {
            $labelMap = [
                'warning'            => 'Warning Issued',
                'lost'               => 'Lost Book',
                'damaged'            => 'Damaged Condition',
                'others'             => 'Condition Issue / Others',
                'torn_pages'         => 'Torn pages',
                'water_damage'       => 'Water damage',
                'missing_pages'      => 'Missing pages',
                'cover_damage'       => 'Cover damage',
                'spine_damage'       => 'Spine damage',
                'writing'            => 'Writing/markings inside',
                'physically_damaged' => 'Physically damaged',
            ];
            $flags = array_filter((array) $request->input('condition_flags', []));
            $adminNotes = $request->input('remarks') ?? $request->input('admin_notes');

            $patronNote = !empty($flags)
                ? implode(', ', array_map(fn ($f) => $labelMap[$f] ?? ucfirst(str_replace('_', ' ', $f)), $flags))
                : 'Flagged during kiosk return review.';

            // If condition flags include warning, issue warning via PatronBanService
            if (in_array('warning', $flags) && $patron) {
                \App\Services\PatronBanService::addWarning(
                    $patron,
                    $patronType,
                    "Warning issued during return inspection for \"{$bookTitle}\": " . ($adminNotes ?: $patronNote),
                    $adminUser,
                    $usage->id,
                    $usage->book_id
                );
            }

            $report = LibraryDamageReport::create([
                'book_id'       => $usage->book_id,
                'book_usage_id' => $usage->id,
                'student_id'    => $usage->student_id,
                'faculty_id'    => $usage->faculty_id,
                'patron_note'   => $patronNote,
                'admin_notes'   => $adminNotes,
                'status'        => 'pending',
            ]);

            ActivityLog::create([
                'action'            => 'kiosk_return_reviewed',
                'description'       => "Kiosk return for \"{$bookTitle}\" ({$patronName}) flagged with issue: {$patronNote}.",
                'performed_by_type' => 'admin',
                'performed_by_id'   => $adminUser?->id,
                'metadata'          => [
                    'performer_name'   => $adminName,
                    'damage_report_id' => $report->id,
                    'affected_user'    => $patronName,
                    'affected_book'    => $bookTitle,
                    'flags'            => $flags,
                    'remarks'          => $adminNotes,
                ],
            ]);

            return back()->with('success', 'Condition issue reported and added to inspection queue.')->with('active_tab', 'damage');
        }

        ActivityLog::create([
            'action'            => 'kiosk_return_reviewed',
            'description'       => "Kiosk return for \"{$bookTitle}\" ({$patronName}) verified as OK.",
            'performed_by_type' => 'admin',
            'performed_by_id'   => $adminUser?->id,
            'metadata'          => [
                'performer_name' => $adminName,
                'affected_user'  => $patronName,
                'affected_book'  => $bookTitle,
                'result'         => 'OK',
            ],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Return verified as OK and shelving confirmed.']);
        }

        return back()->with('success', 'Return verified as OK and shelving confirmed.')->with('active_tab', 'damage');
    }

    public function resendReservationNotification(Request $request, Reservation $reservation)
    {
        $reserver = $reservation->student ?? $reservation->faculty;
        if (!$reserver || !$reserver->email) {
            return back()->with('error', 'No email address found for this reserver.');
        }

        Mail::to($reserver->email)->send(new ReservationReadyMail($reservation));

        $adminUser = $request->user();
        $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';

        ActivityLog::create([
            'action'            => 'reservation_ready_email',
            'description'       => "Reservation ready notification resent to {$reserver->email}.",
            'performed_by_type' => 'admin',
            'performed_by_id'   => $adminUser?->id,
            'metadata'          => [
                'performer_name' => $adminName,
                'reservation_id' => $reservation->id,
                'recipient'      => $reserver->email,
            ],
        ]);

        return back()->with('message', 'Reservation notification resent to ' . $reserver->email);
    }

    public function unbanPatron(Request $request)
    {
        $userType = $request->input('user_type') ?? $request->input('patron_type');
        $userId   = $request->input('user_id') ?? $request->input('patron_id');
        $unbanReason = $request->input('unban_reason') ?? $request->input('unban_notes') ?? $request->input('reason');

        if (!in_array($userType, ['student', 'faculty']) || !$userId) {
            return back()->with('error', 'Invalid patron information provided for unban.');
        }

        if (empty(trim($unbanReason ?? ''))) {
            return back()->with('error', 'A reason or justification is required to unban a patron (e.g., appeal letter, disciplinary resolution).');
        }

        \App\Services\PatronBanService::unbanPatron($userType, (int) $userId, $unbanReason, $request->user());

        $patron = $userType === 'student' ? Student::find($userId) : Faculty::find($userId);
        $name = $patron ? ($patron->first_name . ' ' . $patron->last_name) : 'Patron';

        return back()->with('success', "{$name} has been unbanned. Their warning history (count: {$patron?->damage_warning_count}) has been retained.")->with('active_tab', 'banned');
    }

    public function unsuspendPatron(Request $request)
    {
        $userType = $request->input('user_type') ?? $request->input('patron_type');
        $userId   = $request->input('user_id') ?? $request->input('patron_id');

        if (!in_array($userType, ['student', 'faculty']) || !$userId) {
            return back()->with('error', 'Invalid patron information provided for unsuspend.');
        }

        $patron = $userType === 'student'
            ? Student::findOrFail($userId)
            : Faculty::findOrFail($userId);

        $patron->borrowing_suspended_until = null;
        $patron->save();

        $adminUser = $request->user();
        $adminName = $adminUser?->name ?? $adminUser?->email ?? 'Admin';
        $name = $patron->first_name . ' ' . $patron->last_name;

        \App\Services\AuditLogger::log('patron_unsuspended', "{$name} (" . ucfirst($userType) . ") unsuspended by {$adminName}.", [
            'performer_name' => $adminName,
            'affected_user'  => "{$name} (" . ucfirst($userType) . ")",
            'user_type'      => $userType,
            'user_id'        => $userId,
        ], 'operations');

        return back()->with('success', "{$name} has been unsuspended and may borrow again.")->with('active_tab', 'banned');
    }

    public function banPatronManually(Request $request)
    {
        $userType = $request->input('user_type') ?? $request->input('patron_type');
        $userId   = $request->input('user_id') ?? $request->input('patron_id');
        $reason   = $request->input('reason') ?? $request->input('ban_reason') ?? 'Manual admin ban';

        if (!in_array($userType, ['student', 'faculty']) || !$userId) {
            return back()->with('error', 'Invalid patron information provided for ban.');
        }

        \App\Services\PatronBanService::banPatronManually($userType, (int) $userId, $reason, $request->user());

        $patron = $userType === 'student' ? Student::find($userId) : Faculty::find($userId);
        $name = $patron ? ($patron->first_name . ' ' . $patron->last_name) : 'Patron';

        return back()->with('success', "{$name} has been banned.")->with('active_tab', 'banned');
    }

    public function simulateOverdueLoan(Request $request)
    {
        // 1. Try finding an active non-flagged borrow
        $usage = BookUsage::where('status', 'active')->where('is_overdue_flagged', false)->first();

        // 2. Or any active borrow
        if (!$usage) {
            $usage = BookUsage::where('status', 'active')->first();
        }

        // 3. If no active borrow exists, create one using an existing book and student
        if (!$usage) {
            $book = Book::where('status', 'available')->first() ?? Book::first();
            $student = Student::first();
            if ($book && $student) {
                $usage = BookUsage::create([
                    'book_id'            => $book->id,
                    'student_id'         => $student->id,
                    'status'             => 'active',
                    'time_in'            => now()->subDays(7),
                    'due_date'           => now()->subDays(3)->toDateString(),
                    'is_overdue_flagged' => true,
                ]);
            }
        } else {
            $usage->is_overdue_flagged = true;
            $usage->time_in = now()->subDays(7);
            $usage->due_date = now()->subDays(3)->toDateString();
            $usage->save();
        }

        return redirect()->route('admin.operations.overdue')->with('success', 'Simulated overdue loan created/flagged successfully for testing.');
    }

    /**
     * Debug tool: create an active borrow for the chosen book & patron that is
     * already past its due date, so overdue handling and fines can be tested.
     */
    public function debugCreateOverdue(Request $request)
    {
        $validated = $request->validate([
            'book_id'      => 'required|exists:books,id',
            'patron_type'  => 'required|in:student,faculty',
            'patron_id'    => 'required|integer',
            'days_overdue' => 'required|integer|min:1|max:365',
        ]);

        $isFaculty = $validated['patron_type'] === 'faculty';
        $patron = $isFaculty
            ? Faculty::find($validated['patron_id'])
            : Student::find($validated['patron_id']);
        if (!$patron) {
            return back()->withErrors(['patron_id' => 'Selected patron was not found.'])->with('active_tab', 'config');
        }

        $book = Book::findOrFail($validated['book_id']);
        $daysOverdue = (int) $validated['days_overdue'];
        $borrowDays  = max(1, (int) Setting::getValue('max_borrow_days_student', 7));

        $usage = BookUsage::create([
            ($isFaculty ? 'faculty_id' : 'student_id') => $patron->id,
            'book_id'            => $book->id,
            'time_in'            => now()->subDays($daysOverdue + $borrowDays),
            'due_date'           => now()->subDays($daysOverdue)->toDateString(),
            'status'             => 'active',
            'usage_context'      => $isFaculty ? 'off_site' : 'kiosk',
            'is_overdue_flagged' => true,
            'remarks'            => '[DEBUG] Simulated overdue borrow',
        ]);

        if ($book->copies > 0) {
            $book->decrement('copies');
        }
        $book->update(['status' => $book->copies > 0 ? 'available' : 'borrowed']);

        $adminUser  = $request->user();
        $adminName  = $adminUser?->name ?? $adminUser?->email ?? 'Admin';
        $patronName = $patron->first_name . ' ' . $patron->last_name;

        \App\Services\AuditLogger::log('debug_overdue_created', "[DEBUG] {$adminName} simulated an overdue borrow of \"{$book->title}\" for {$patronName} ({$daysOverdue} day(s) overdue).", [
            'performer_name' => $adminName,
            'usage_id'       => $usage->id,
            'affected_book'  => $book->title,
            'affected_user'  => "{$patronName} (" . ucfirst($validated['patron_type']) . ")",
            'days_overdue'   => $daysOverdue,
        ], 'operations');

        return redirect()->route('admin.operations.index', ['tab' => 'overdue'])
            ->with('success', "Simulated overdue borrow created: \"{$book->title}\" for {$patronName}, {$daysOverdue} day(s) overdue.")
            ->with('active_tab', 'overdue');
    }
}
