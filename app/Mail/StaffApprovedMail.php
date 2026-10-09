<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StaffApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $staff;

    public function __construct($staff)
    {
        $this->staff = $staff;
    }

    public function build()
    {
        return $this->subject('Your PUPSJ Libris Staff Account Approved')
                    ->markdown('emails.staff-approved');
    }
}
