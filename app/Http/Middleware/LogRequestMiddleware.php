<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('Request', [
            'ip' => $request->ip(),
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'payload' => $request->except(['password', 'password_confirmation']),
        ]);

        $start = microtime(true);

        $response = $next($request);

        Log::info('Response', [
            'status' => $response->status(),
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
        ]);

        return $response;
    }
}
