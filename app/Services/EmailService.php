<?php

namespace App\Services;

use App\Contracts\TransactionalEmailProvider;
use App\Models\Contact;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailService
{
    public function __construct(
        protected TransactionalEmailProvider $provider,
    ) {}

    /**
     * @return array{owner: bool, user: bool}
     */
    public function sendNotifications(Contact $contact): array
    {
        return [
            'owner' => $this->sendSafely(
                contact: $contact,
                notification: 'owner',
                recipientEmail: (string) config('services.brevo.owner.email'),
                recipientName: (string) config('services.brevo.owner.name'),
                subject: 'Новое обращение с сайта',
                view: 'emails.owner',
            ),
            'user' => $this->sendSafely(
                contact: $contact,
                notification: 'user_copy',
                recipientEmail: $contact->email,
                recipientName: $contact->name,
                subject: 'Копия вашего обращения',
                view: 'emails.user_copy',
            ),
        ];
    }

    private function sendSafely(
        Contact $contact,
        string $notification,
        string $recipientEmail,
        string $recipientName,
        string $subject,
        string $view,
    ): bool {
        try {
            $messageId = $this->provider->send(
                recipientEmail: $recipientEmail,
                recipientName: $recipientName,
                subject: $subject,
                htmlContent: view($view, ['contact' => $contact])->render(),
            );

            Log::info('Email notification accepted by provider', [
                'contact_id' => $contact->getKey(),
                'notification' => $notification,
                'provider_message_id' => $messageId,
            ]);

            return true;
        } catch (Throwable $exception) {
            Log::error('Email notification failed', [
                'contact_id' => $contact->getKey(),
                'notification' => $notification,
                'exception' => $exception::class,
            ]);

            return false;
        }
    }
}
