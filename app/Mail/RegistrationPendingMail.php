<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationPendingMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $firstName;
    public string $accountType;

    public function __construct(string $firstName, string $accountType = 'student')
    {
        $this->firstName   = $firstName;
        $this->accountType = $accountType;
    }

    public function build()
    {
        return $this->subject('Account Registration Received — PUPSJ Libris')
                    ->view('emails.registration-pending');
    }
}
