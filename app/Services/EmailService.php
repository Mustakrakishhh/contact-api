<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\Mail;
use App\Mail\OwnerMail;
use App\Mail\UserCopyMail;

class EmailService
{
    public function sendOwner(Contact $contact): void
    {
        Mail::to(config('mail.owner_email'))->send(new OwnerMail($contact));
    }

    public function sendUserCopy(Contact $contact): void
    {
        Mail::to($contact->email)->send(new UserCopyMail($contact));
    }
}
