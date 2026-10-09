<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FacultyApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $faculty;

    public function __construct($faculty)
    {
        $this->faculty = $faculty;
    }

    public function build()
    {
        return $this->subject('Your PUPSJ Libris Faculty Account Has Been Approved')
                    ->view('emails.faculty-approved');
    }
}