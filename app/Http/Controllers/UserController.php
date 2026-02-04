<?php

namespace App\Http\Controllers;

use App\Helpers\NomaneHelper;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\TemporaryPasswordMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'is_admin' => 'integer',
            'is_admin' => 'teleoperator_id',
        ]);

        $tempPassword = NomaneHelper::generateSecurePassword(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($tempPassword),
            'is_admin' => $request->is_admin,
            'teleoperator_id' => $request->teleoperator_id, // Here is Wrong
        ]);

        Mail::to($user->email)->send(new TemporaryPasswordMail($user, $tempPassword));

        return response()->json(['success' => 'User registered and email sent.'], 200);
        //return back()->with('success', 'User registered and email sent.');
    }
}
