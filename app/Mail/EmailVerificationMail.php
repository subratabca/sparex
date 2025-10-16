<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $userType;
    
    public function __construct($user, $userType = 'client')
    {
        $this->user = $user;
        $this->userType = $userType;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Email Verification',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.email-verification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}


