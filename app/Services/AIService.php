<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;
use Throwable;

class AIService
{
    public function generateResponse(string $comment, string $name): string
    {
        try {
            $response = OpenAI::responses()->create([
                'model' => (string) config('openai.model'),
                'instructions' => 'Ты вежливый менеджер лендинга разработчика. Ответь по-русски, до 50 слов. Обращайся по имени, не выдумывай сроки и не давай обещаний, которых нет во входных данных.',
                'input' => "Имя: {$name}\nКомментарий: {$comment}",
                'max_output_tokens' => 300,
            ]);

            $answer = trim((string) $response->outputText);

            if ($answer === '') {
                throw new RuntimeException('OpenAI returned an empty response.');
            }

            return $answer;
        } catch (Throwable $exception) {
            Log::warning('AI недоступен, используется fallback', [
                'exception' => $exception::class,
            ]);

            return "Спасибо, $name! Мы получили ваш комментарий и обязательно свяжемся с вами в ближайшее время.";
        }
    }
}
