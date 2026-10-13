<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (! $request->user() || $request->user()->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak mengakses endpoint ini',
                'error'   => ['code' => 'FORBIDDEN', 'details' => []],
            ], 403);
        }

        return $next($request);
    }
}