<?php

namespace App\Services;

use App\Models\Faculty;
use App\Models\LibraryDamageReport;
use App\Models\LibraryPenalty;
use App\Models\Reservation;
use App\Models\Student;

class BorrowEligibilityService
{
    public static function studentBlockingReason(Student $student): ?string
    {
        if ($student->is_banned) {
            return 'Your borrowing privilege has been permanently suspended due to repeated warnings. Please contact the library administrator.';
        }
        if ($student->borrowing_suspended_until && $student->borrowing_suspended_until->isFuture()) {
            return 'Borrowing is suspended until '.$student->borrowing_suspended_until->format('M d, Y').'. Please contact the administrator.';
        }
        if (LibraryDamageReport::where('student_id', $student->id)->where('status', 'pending')->exists()) {
            return 'You have a pending damage report under review. Please contact the library administrator before borrowing.';
        }
        if (LibraryPenalty::where('student_id', $student->id)->where('status', 'pending')->exists()) {
            return 'You have unpaid library penalties. Please settle them with the administrator before borrowing.';
        }

        return null;
    }

    public static function facultyBlockingReason(Faculty $faculty): ?string
    {
        if ($faculty->is_banned) {
            return 'Your borrowing privilege has been permanently suspended due to repeated warnings. Please contact the library administrator.';
        }
        if ($faculty->borrowing_suspended_until && $faculty->borrowing_suspended_until->isFuture()) {
            return 'Borrowing is suspended until '.$faculty->borrowing_suspended_until->format('M d, Y').'. Please contact the administrator.';
        }
        if (LibraryDamageReport::where('faculty_id', $faculty->id)->where('status', 'pending')->exists()) {
            return 'You have a pending damage report under review. Please contact the library administrator before borrowing.';
        }
        if (LibraryPenalty::where('faculty_id', $faculty->id)->where('status', 'pending')->exists()) {
            return 'You have unpaid library penalties. Please settle them with the administrator before borrowing.';
        }

        return null;
    }

    public static function fulfillReservationForBorrow(int $bookId, ?int $studentId, ?int $facultyId): void
    {
        $q = Reservation::where('book_id', $bookId)->where('status', 'pending');
        if ($studentId) {
            $q->where('student_id', $studentId)->whereNull('faculty_id');
        } elseif ($facultyId) {
            $q->where('faculty_id', $facultyId)->whereNull('student_id');
        } else {
            return;
        }
        $q->update([
            'status' => 'fulfilled',
            'fulfilled_at' => now(),
        ]);
    }
}
