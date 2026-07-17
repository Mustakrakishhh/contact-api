<?php

namespace App\Repositories;

use App\Models\Contact;

class ContactRepository
{
    public function create(array $data): Contact
    {
        return Contact::create($data);
    }

    public function markUserEmailSent(Contact $contact): void
    {
        $contact->update(['sent_to_user' => true]);
    }

    public function getStats(): array
    {
        return [
            'total' => Contact::count(),
            'today' => Contact::whereDate('created_at', today())->count(),
        ];
    }
}
