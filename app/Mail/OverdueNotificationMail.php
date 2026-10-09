<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $studentCount,
        public int $facultyCount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Library Alert: Overdue Books Detected');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.overdue-notification');
    }
}
