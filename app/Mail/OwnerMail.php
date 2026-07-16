<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contact $contact) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новое обращение с сайта',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.owner',
            with: ['contact' => $this->contact],
        );
    }
}
