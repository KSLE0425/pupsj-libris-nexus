<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';
    protected $description = 'Expire pending reservations whose 5-minute window has passed';

    public function handle(): void
    {
        $expired = Reservation::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $reservation) {
            $reservation->update(['status' => 'expired']);
        }

        if ($expired->count() > 0) {
            $this->info("Expired {$expired->count()} reservation(s).");
        }
    }
}
