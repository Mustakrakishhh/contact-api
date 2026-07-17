<?php

use App\Services\Email\BrevoEmailProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set([
        'services.brevo.api_key' => 'brevo-test-key',
        'services.brevo.base_url' => 'https://api.brevo.com/v3',
        'services.brevo.connect_timeout' => 1,
        'services.brevo.timeout' => 2,
        'services.brevo.sender.email' => 'sender@example.com',
        'services.brevo.sender.name' => 'Contact API',
    ]);

    Http::preventStrayRequests();
});

it('sends a transactional email through the Brevo API', function () {
    Http::fake([
        'api.brevo.com/v3/smtp/email' => Http::response([
            'messageId' => '<message-id@brevo>',
        ], 201),
    ]);

    $messageId = app(BrevoEmailProvider::class)->send(
        recipientEmail: 'recipient@example.com',
        recipientName: 'Иван',
        subject: 'Тестовое письмо',
        htmlContent: '<p>Сообщение</p>',
    );

    expect($messageId)->toBe('<message-id@brevo>');

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://api.brevo.com/v3/smtp/email'
            && $request->hasHeader('api-key', 'brevo-test-key')
            && $request['sender']['email'] === 'sender@example.com'
            && $request['to'][0]['email'] === 'recipient@example.com'
            && $request['subject'] === 'Тестовое письмо';
    });
});

it('fails before making a request when the API key is missing', function () {
    config()->set('services.brevo.api_key');

    expect(fn () => app(BrevoEmailProvider::class)->send(
        recipientEmail: 'recipient@example.com',
        recipientName: 'Иван',
        subject: 'Тестовое письмо',
        htmlContent: '<p>Сообщение</p>',
    ))->toThrow(RuntimeException::class, 'Brevo API key is not configured.');

    Http::assertNothingSent();
});
