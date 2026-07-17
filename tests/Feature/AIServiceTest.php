<?php

use App\Services\AIService;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Responses\CreateResponse;
use OpenAI\Testing\Enums\OverrideStrategy;

it('returns the generated OpenAI response', function () {
    OpenAI::fake([
        CreateResponse::fake(
            override: [
                'output' => [
                    [
                        'type' => 'message',
                        'id' => 'msg_test',
                        'status' => 'completed',
                        'role' => 'assistant',
                        'content' => [[
                            'type' => 'output_text',
                            'text' => 'Спасибо, Иван! Расскажите подробнее о проекте.',
                            'annotations' => [],
                        ]],
                    ],
                ],
            ],
            strategy: OverrideStrategy::Replace,
        ),
    ]);

    $reply = app(AIService::class)->generateResponse(
        comment: 'Нужна разработка API.',
        name: 'Иван',
    );

    expect($reply)->toBe('Спасибо, Иван! Расскажите подробнее о проекте.');
});

it('returns a fallback when OpenAI is unavailable', function () {
    OpenAI::fake([
        new RuntimeException('Provider unavailable'),
    ]);

    $reply = app(AIService::class)->generateResponse(
        comment: 'Нужна разработка API.',
        name: 'Иван',
    );

    expect($reply)->toBe(
        'Спасибо, Иван! Мы получили ваш комментарий и обязательно свяжемся с вами в ближайшее время.',
    );
});
