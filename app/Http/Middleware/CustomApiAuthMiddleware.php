<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class CustomApiAuthMiddleware extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (! $request->bearerToken()) {
            return response()->json(['status'=>'error', 'message' => 'Token not provided'], 401);
        }

        return parent::handle($request, $next, ...$guards);
    }
}
