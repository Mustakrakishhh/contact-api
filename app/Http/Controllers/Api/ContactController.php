<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __invoke(ContactRequest $request, ContactService $service): JsonResponse
    {
        $result = $service->handle($request->validated());
        $emailSent = $result['email_delivery']['owner'] && $result['email_delivery']['user'];

        return response()->json([
            'message' => $emailSent
                ? 'Обращение получено, копия отправлена на вашу почту.'
                : 'Обращение сохранено, но почтовый сервис временно недоступен.',
            'ai_reply' => $result['ai_reply'],
            'email_status' => $emailSent ? 'sent' : 'deferred',
        ], $emailSent ? 201 : 202);
    }
}
