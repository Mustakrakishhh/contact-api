<?php

namespace App\Services;

use App\Repositories\ContactRepository;
use App\Services\AIService;
use App\Services\EmailService;

class ContactService
{
    public function __construct(
        protected ContactRepository $repository,
        protected AIService $ai,
        protected EmailService $email
    ) {}

    public function handle(array $data): array
    {
        // Генерируем AI-ответ
        $aiReply = $this->ai->generateResponse($data['comment'], $data['name']);

        // Сохраняем обращение
        $contact = $this->repository->create(array_merge($data, ['ai_response' => $aiReply]));

        // Отправляем письма (можно увести в очередь позже)
        $this->email->sendOwner($contact);
        $this->email->sendUserCopy($contact);

        return [
            'contact' => $contact,
            'ai_reply' => $aiReply,
        ];
    }
}
