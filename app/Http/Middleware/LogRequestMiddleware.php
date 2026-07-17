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
            'request_id' => $request->header('X-Request-ID'),
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'payload_fields' => array_keys($request->all()),
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
