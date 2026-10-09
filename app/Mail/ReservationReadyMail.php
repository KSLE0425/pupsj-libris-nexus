<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $book;

    public function __construct($reservation)
    {
        $this->reservation = $reservation;
        $this->book = $reservation->book;
    }

    public function build()
    {
        return $this->subject('[PUPSJ Libris] Your reserved book is now available')
                    ->markdown('emails.reservation-ready');
    }
}