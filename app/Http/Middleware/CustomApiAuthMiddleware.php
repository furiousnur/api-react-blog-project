<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CustomApiAuthMiddleware extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        /*if (!$request->bearerToken()) {
            return response()->json(['status'=>'error', 'message' => 'Token not provided'], 401);
        }*/
        if ($request->bearerToken()){
            $token = $request->bearerToken();
            $decryptedToken = decrypt($token);
            $token = DB::table('personal_access_tokens')
                ->join('users', 'users.id', '=', 'personal_access_tokens.tokenable_id')
                ->where('token', $decryptedToken)->first();
            if (!$token) {
                return response()->json(['status'=>'error', 'message' => 'Token is Invalid'], 401);
            }
            if (!$token->auth_token) {
                return response()->json(['status'=>'error', 'message' => 'Unauthorized User'], 401);
            }
            Session::put('user', $token);
        }
        return $next($request);
//        return parent::handle($request, $next, ...$guards);
    }
}
