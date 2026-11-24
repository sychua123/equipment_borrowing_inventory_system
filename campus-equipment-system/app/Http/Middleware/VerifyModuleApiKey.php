<?php
// Author: Lai Chee Kit
// Module 4: only other modules that know the shared key may call our web service

namespace App\Http\Middleware;

use App\Support\Ifa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyModuleApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $sent = (string) $request->header('X-API-KEY', '');

        // hash_equals compares in constant time, so the key cannot be guessed from response timing
        if (! hash_equals((string) config('module4.api_key'), $sent)) {
            return Ifa::fail($request->query('requestID'), 'Invalid or missing API key.', 401);
        }

        return $next($request);
    }
}
