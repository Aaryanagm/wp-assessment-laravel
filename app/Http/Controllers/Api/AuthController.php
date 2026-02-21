<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('user_email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->user_pass)) {
            return back()->withErrors(['Invalid credentials']);
        }

        $token = JWTAuth::claims([
            'role' => $user->role
        ])->fromUser($user);

        session([
            'jwt_token' => $token,
            'user_role' => $user->role
        ]);

        return redirect('/dashboard');
    }

    public function me()
    {
        return response()->json(auth()->user());
    }
}