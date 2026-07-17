<?php

namespace App\Services;

use App\Repositories\ContactRepository;

class ContactService
{
    public function __construct(
        protected ContactRepository $repository,
        protected AIService $ai,
        protected EmailService $email
    ) {}

    public function handle(array $data): array
    {
        $aiReply = $this->ai->generateResponse($data['comment'], $data['name']);

        $contact = $this->repository->create(array_merge($data, ['ai_response' => $aiReply]));

        $emailDelivery = $this->email->sendNotifications($contact);

        if ($emailDelivery['user']) {
            $this->repository->markUserEmailSent($contact);
        }

        return [
            'contact' => $contact,
            'ai_reply' => $aiReply,
            'email_delivery' => $emailDelivery,
        ];
    }
}
