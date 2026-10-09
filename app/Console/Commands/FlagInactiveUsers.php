<?php

namespace App\Console\Commands;

use App\Models\Faculty;
use App\Models\Student;
use Illuminate\Console\Command;

class FlagInactiveUsers extends Command
{
    protected $signature   = 'library:flag-inactive-users';
    protected $description = 'Mark users dormant after 90 days without borrowing; restore approved status when they borrow again.';

    public function handle(): void
    {
        $cutoff = now()->subDays(90);

        $flagged = 0;
        $restored = 0;

        foreach ([Student::class, Faculty::class] as $model) {
            $flagged += $model::where('status', 'approved')
                ->where(fn($q) => $q->whereNull('last_borrow_at')
                    ->orWhere('last_borrow_at', '<', $cutoff))
                ->update(['status' => 'inactive']);

            $restored += $model::where('status', 'inactive')
                ->where('last_borrow_at', '>=', $cutoff)
                ->update(['status' => 'approved']);
        }

        $this->info("Flagged {$flagged} users as dormant. Restored {$restored} users to approved.");
    }
}
