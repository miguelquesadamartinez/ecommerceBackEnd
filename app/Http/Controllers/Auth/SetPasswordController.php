<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class SetPasswordController extends Controller
{
    public function setPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => [
                'required',
                'string',
                'min:12',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/'
            ],
        ]);

        //$user = User::where('email', $request->email)->first();
        $user = User::where('email', $request->email)->where('password', Hash::make($request->old_password))->first();

        if (!$user) {
            return response()->json(['message' => 'User not found or not validated.'], 403);
        } else {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
            return response()->json(['message' => 'Password updated successfully.'], 200);
        }
    }
}


