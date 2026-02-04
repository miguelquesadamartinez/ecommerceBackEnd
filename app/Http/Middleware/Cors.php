<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        LOG::debug('CORS Middleware applied.');
        LOG::debug( env('CORS_ALLOWED_ORIGINS', '*') );


        $allowedOrigins = explode(',', env('CORS_ALLOWED_ORIGINS', '*'));
        $origin = $request->headers->get('Origin');

        // Determinar el origen permitido
        $allowOrigin = '*';
        if (!in_array('*', $allowedOrigins) && $origin) {
            $allowOrigin = in_array($origin, $allowedOrigins) ? $origin : '';
        } elseif ($origin) {
            $allowOrigin = $origin;
        }

        $headers = [
            'Access-Control-Allow-Origin' => $allowOrigin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json(['message' => 'OK'], 200, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
