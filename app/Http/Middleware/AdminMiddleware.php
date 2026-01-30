<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->is_admin)
            return $next($request);
        return response()->json(['message' => 'No autorizado.'], 403);
    }
}
