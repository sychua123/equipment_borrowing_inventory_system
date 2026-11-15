<?php
// Author: Lai Chee Kit
// Module 4: the dashboard and reports are for the Admin only

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Only the Admin can open this page.');

        return $next($request);
    }
}
