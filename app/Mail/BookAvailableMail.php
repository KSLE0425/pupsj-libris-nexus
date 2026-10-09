<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookAvailableMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;
    public $book;

    public function __construct($notification)
    {
        $this->notification = $notification;
        $this->book = $notification->book;
    }

    public function build()
    {
        return $this->subject('[PUPSJ Libris] A book you wanted is now available')
                    ->markdown('emails.book-available');
    }
}