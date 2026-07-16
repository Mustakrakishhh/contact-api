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

        return response()->json([
            'message' => 'Обращение получено',
            'ai_reply' => $result['ai_reply'],
        ], 201);
    }

    // Если есть middleware – убедитесь, что там только throttle
    public static function middleware(): array
    {
        return [
            new \Illuminate\Routing\Controllers\Middleware('throttle:5,1'),
        ];
    }
}
