<?php

namespace App\Console\Commands;

use App\Mail\OverdueNotificationMail;
use App\Models\BookUsage;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class FlagOverdueBooks extends Command
{
    protected $signature   = 'library:flag-overdue';
    protected $description = 'Flag active borrows that have exceeded the overdue threshold as possibly lost';

    public function handle(): int
    {
        $maxDays = (int) Setting::getValue('max_borrow_days_student', 7);
        $cutoff  = now()->subDays($maxDays)->startOfDay();
        $today   = now()->toDateString();

        // Prefer explicit due_date when set; otherwise fall back to max borrow days from time_in
        $studentFlagged = BookUsage::where('status', 'active')
            ->whereNotNull('student_id')
            ->whereNull('faculty_id')
            ->where('is_overdue_flagged', false)
            ->where(function ($q) use ($today, $cutoff) {
                $q->where(function ($q2) use ($today) {
                    $q2->whereNotNull('due_date')->whereDate('due_date', '<', $today);
                })->orWhere(function ($q2) use ($cutoff) {
                    $q2->whereNull('due_date')->where('time_in', '<=', $cutoff);
                });
            })
            ->update(['is_overdue_flagged' => true]);

        $facultyFlagged = BookUsage::where('status', 'active')
            ->whereNotNull('faculty_id')
            ->where('is_overdue_flagged', false)
            ->where(function ($q) use ($today, $cutoff) {
                $q->where(function ($q2) use ($today) {
                    $q2->whereNotNull('due_date')->whereDate('due_date', '<', $today);
                })->orWhere(function ($q2) use ($cutoff) {
                    $q2->whereNull('due_date')->where('time_in', '<=', $cutoff);
                });
            })
            ->update(['is_overdue_flagged' => true]);

        $total = $studentFlagged + $facultyFlagged;

        $this->info("Flagged {$studentFlagged} student and {$facultyFlagged} faculty borrows as possibly lost ({$total} total).");

        if ($total > 0) {
            $adminEmail = Setting::getValue('admin_email', config('mail.from.address'));
            Mail::to($adminEmail)->send(new OverdueNotificationMail($studentFlagged, $facultyFlagged));
            $this->info("Overdue notification email sent to {$adminEmail}.");
        }

        return self::SUCCESS;
    }
}
