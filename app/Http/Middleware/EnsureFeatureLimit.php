<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureLimit
{
    public function handle(Request $request, Closure $next, ?string $limitKey = null, ?string $feature = null): Response
    {
        // Feature limit middleware pass-through
        return $next($request);
    }
}
