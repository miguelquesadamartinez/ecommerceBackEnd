<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\NomaneHelper;
use App\Mail\TemporaryPasswordMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 403);
        } else {

            $tempPassword = NomaneHelper::generateSecurePassword(12);

            $user->password = Hash::make($tempPassword);
            $user->save();

            Mail::to($user->email)->send(new TemporaryPasswordMail($user, $tempPassword));

            return response()->json(['success' => 'email sent.'], 200);
        }
    }
}
