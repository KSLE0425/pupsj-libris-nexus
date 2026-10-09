<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    public $guard;
    public $resetUrl;

    public function __construct($token, $email, $guard)
    {
        $this->token = $token;
        $this->email = $email;
        $this->guard = $guard;
        
        // Generate reset URL based on guard
        $routeName = match($guard) {
            'student' => 'password.reset.form',
            'faculty' => 'password.reset.form',
            default => 'password.reset.form',
        };
        
        $this->resetUrl = route($routeName, ['token' => $token, 'email' => $email, 'guard' => $guard]);
    }

    public function envelope(): Envelope
    {
        $guardLabel = match($this->guard) {
            'student' => 'Student',
            'faculty' => 'Faculty',
            default => 'Admin',
        };
        
        return new Envelope(
            subject: "{$guardLabel} Password Reset - PUPSJ Libris",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
        );
    }
}