<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCopyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contact $contact)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Копия вашего обращения на сайт',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user_copy',
            with: ['contact' => $this->contact],
        );
    }
}
