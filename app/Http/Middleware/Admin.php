<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        abort(403, 'Admin access only.');
    }
}
