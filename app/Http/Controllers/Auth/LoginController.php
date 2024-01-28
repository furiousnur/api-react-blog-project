<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Token;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $credentials = $request->only('email', 'password');
            if (auth()->attempt($credentials)) {
                $user = auth()->user();
                if (!$user->auth_token) {
                    return response()->json(['error' => 'The user do not have the token'], 401);
                }
                $token = $user->createToken('authToken')->accessToken;
                $tokenModel = $user->tokens()->where('id', $token->id)->first();
                $tokenModel->expires_in = 60 * 24; // Set the value accordingly
                $tokenModel->save();
                $encryptToken = encrypt($token->token);
                return response()->json([
                    'message' => 'Login successful',
                    'user' => $user,  // Include user details if needed
                    'token' => $encryptToken,
                    'token_type' => 'Bearer',
                    'token_name' => $token->name,
                    'expires_at' => $token->expires_at,
                ]);
            }
            return response()->json(['error' => 'UnAuthorised'], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Login failed. Please try again.',
            ], 500);
        }
    }

    public function tokenVerify(Request $request) {
        $accessToken = $request->bearerToken();
        $decryptedToken = decrypt($accessToken);
        $token = DB::table('personal_access_tokens')->where('token', $decryptedToken)->first();
        if ($token !== null) {
            if (!$token->revoked) {
                if ($token->expires_at !== null && now() > $token->expires_at) {
                    return response()->json(['valid' => false, 'message' => 'Token is expired'], 401);
                }
                return response()->json(['valid' => true, 'message' => 'Token is valid']);
            } else {
                return response()->json(['valid' => false, 'message' => 'Token has been revoked'], 401);
            }
        }
        return response()->json(['valid' => false, 'message' => 'Token is not valid'], 401);
    }

    public function logout(Request $request) {
        $reqToken = $request->bearerToken();
        $decryptedToken = decrypt($reqToken);
        $token = PersonalAccessToken::where('token', $decryptedToken)->first();
        if ($token) {
            $token->delete();
            return response()->json(['message' => 'Logout successful']);
        }
        return response()->json(['valid' => false, 'message' => 'Token is not valid'], 401);
    }
}
