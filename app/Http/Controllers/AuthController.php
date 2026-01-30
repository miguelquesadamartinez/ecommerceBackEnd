<?php

/**
 *
 * This controller seems not to be used in the project.
 *
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        //Fonction avant changement Nacer :
         if (Auth::attempt($credentials)) {
            // Connexion OK (LDAP)
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects.',
        ]);
        /*$credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($request->email == 'api.user@callmedicall.com') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user = Auth::user();
        $user = User::find($user->id);
        $token = $user->createToken('authToken')->plainTextToken;
        return response()->json(['token' => $token], 200);*/

    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out'], 200);
    }

    public function verifyToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);
        $tokenParts = explode('|', $request->token);
        if (count($tokenParts) !== 2) {
            return response()->json(['message' => 'Token is invalid'], 204);
        }
        $tokenId = $tokenParts[0];
        $tokenHash = hash('sha256', $tokenParts[1]);
        $personalAccessToken = PersonalAccessToken::where('id', $tokenId)
            ->where('token', $tokenHash)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
        if (!$personalAccessToken) {
            return response()->json(['message' => 'Token is invalid'], 204);
        }
        $user = $personalAccessToken->tokenable;
        if (!$user) {
            return response()->json(['message' => 'Token is invalid'], 204);
        }
        return response()->json([
            'message' => 'Token is valid',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'user_type' => $user->user_type != '' ? $user->user_type : 'Call',
                'email' => $user->email
            ]
        ], 200);
    }
}
