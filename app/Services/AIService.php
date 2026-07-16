<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class AIService
{
    public function generateResponse(string $comment, string $name): string
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'Ты вежливый менеджер. Ответь на комментарий до 50 слов. Обращайся по имени. Никаких обещаний, если не уверен.'],
                    ['role' => 'user', 'content' => "Имя: $name. Комментарий: $comment"]
                ],
                'max_tokens' => 150,
            ]);

            return trim($response->choices[0]->message->content);
        } catch (\Exception $e) {
            Log::warning('AI недоступен, используется fallback', ['error' => $e->getMessage()]);
            return "Спасибо, $name! Мы получили ваш комментарий и обязательно свяжемся с вами в ближайшее время.";
        }
    }
}
