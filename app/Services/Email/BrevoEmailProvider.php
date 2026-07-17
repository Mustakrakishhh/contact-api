<?php

namespace App\Services\Email;

use App\Contracts\TransactionalEmailProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class BrevoEmailProvider implements TransactionalEmailProvider
{
    public function send(
        string $recipientEmail,
        string $recipientName,
        string $subject,
        string $htmlContent,
    ): string {
        $apiKey = (string) config('services.brevo.api_key');
        $senderEmail = (string) config('services.brevo.sender.email');

        if ($apiKey === '') {
            throw new RuntimeException('Brevo API key is not configured.');
        }

        if ($senderEmail === '') {
            throw new RuntimeException('Brevo sender email is not configured.');
        }

        $response = Http::baseUrl((string) config('services.brevo.base_url'))
            ->withHeaders(['api-key' => $apiKey])
            ->acceptJson()
            ->connectTimeout((int) config('services.brevo.connect_timeout'))
            ->timeout((int) config('services.brevo.timeout'))
            ->retry([200, 500], function (Throwable $exception): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response->serverError());
            }, throw: false)
            ->post('/smtp/email', [
                'sender' => [
                    'email' => $senderEmail,
                    'name' => (string) config('services.brevo.sender.name'),
                ],
                'to' => [[
                    'email' => $recipientEmail,
                    'name' => $recipientName,
                ]],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ]);

        $response->throw();

        $messageId = $response->json('messageId');

        if (! is_string($messageId) || $messageId === '') {
            throw new RuntimeException('Brevo returned an invalid response without a message ID.');
        }

        return $messageId;
    }
}
