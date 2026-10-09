<?php

namespace App\Services;

use App\Models\BookUsage;
use App\Models\LibraryPenalty;
use App\Models\Setting;
use Carbon\Carbon;

class PenaltyCalculationService
{
    /**
     * Formerly applied late_return penalties. Penalties are now damage/lost only.
     * Kept for call-site compatibility; always returns null.
     */
    public function calculateAndApply(): ?LibraryPenalty
    {
        return null;
    }

    /**
     * Return due-date info for a faculty borrow (informational, no penalty created).
     */
    public function getPenaltyInfo(BookUsage $usage): ?array
    {
        if (! $usage->faculty_id) {
            return null;
        }

        $maxDays = (int) Setting::getValue('max_borrow_days_student', 7);
        $borrowedAt = $usage->time_in ?? $usage->created_at;
        $dueDate = $usage->due_date
            ? new Carbon($usage->due_date)
            : (new Carbon($borrowedAt))->addDays($maxDays);
        $now = now();

        $overdueDays = $now->gt($dueDate) ? (int) $dueDate->diffInDays($now) : 0;

        return [
            'max_days' => $maxDays,
            'due_date' => $dueDate->toDateString(),
            'overdue_days' => $overdueDays,
            'is_overdue' => $overdueDays > 0,
        ];
    }
}
