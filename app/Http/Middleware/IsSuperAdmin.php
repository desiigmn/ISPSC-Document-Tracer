<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
{
    // Check if user is logged in AND is a superadmin
    if (auth()->check() && auth()->user()->role === 'superadmin') {
        return $next($request);
    }

    // Otherwise, block access
    abort(403, 'Unauthorized access.');
}
}
