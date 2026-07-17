<?php

use App\Contracts\TransactionalEmailProvider;
use App\Models\Contact;
use App\Services\AIService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    config()->set([
        'services.brevo.owner.email' => 'owner@example.com',
        'services.brevo.owner.name' => 'Владелец',
        'services.contact.rate_limit' => 5,
    ]);

    $this->mock(AIService::class)
        ->shouldReceive('generateResponse')
        ->andReturn('Спасибо за обращение!');
});

it('stores a contact and sends both email notifications', function () {
    $provider = Mockery::mock(TransactionalEmailProvider::class);
    $provider->shouldReceive('send')->twice()->andReturn('<owner@brevo>', '<user@brevo>');
    $this->app->instance(TransactionalEmailProvider::class, $provider);

    $response = $this->postJson('/api/contact', validContactPayload());

    $response
        ->assertCreated()
        ->assertJsonPath('email_status', 'sent')
        ->assertJsonPath('ai_reply', 'Спасибо за обращение!');

    $contact = Contact::query()->sole();

    expect($contact->sent_to_user)->toBeTrue();
    $this->assertModelExists($contact);
});

it('keeps the contact when the email provider is unavailable', function () {
    $provider = Mockery::mock(TransactionalEmailProvider::class);
    $provider->shouldReceive('send')->twice()->andThrow(new RuntimeException('Provider unavailable'));
    $this->app->instance(TransactionalEmailProvider::class, $provider);

    $response = $this->postJson('/api/contact', validContactPayload());

    $response
        ->assertStatus(202)
        ->assertJsonPath('email_status', 'deferred');

    $contact = Contact::query()->sole();

    expect($contact->sent_to_user)->toBeFalse();
    $this->assertModelExists($contact);
});

it('returns validation errors for an invalid request', function () {
    $this->postJson('/api/contact', [
        'name' => '',
        'phone' => 'not-a-phone',
        'email' => 'not-an-email',
        'comment' => 'x',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'phone', 'email', 'comment']);
});

it('rate limits repeated contact requests', function () {
    config()->set('services.contact.rate_limit', 2);

    $provider = Mockery::mock(TransactionalEmailProvider::class);
    $provider->shouldReceive('send')->times(4)->andReturn('<message@brevo>');
    $this->app->instance(TransactionalEmailProvider::class, $provider);

    $this->postJson('/api/contact', validContactPayload())->assertCreated();
    $this->postJson('/api/contact', validContactPayload())->assertCreated();
    $this->postJson('/api/contact', validContactPayload())
        ->assertTooManyRequests()
        ->assertJsonPath('message', 'Слишком много обращений. Повторите попытку через минуту.');
});

/**
 * @return array{name: string, phone: string, email: string, comment: string}
 */
function validContactPayload(): array
{
    return [
        'name' => 'Иван Петров',
        'phone' => '+79991234567',
        'email' => 'contact.test@gmail.com',
        'comment' => 'Хочу обсудить разработку API.',
    ];
}
