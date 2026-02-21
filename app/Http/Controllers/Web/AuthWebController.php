<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        // WordPress users table uses user_email
        $user = User::where('user_email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['Invalid credentials']);
        }

        // user_pass must be bcrypt (as we migrated earlier)
        if (!Hash::check($request->password, $user->user_pass)) {
            return back()->withErrors(['Invalid credentials']);
        }

        // Generate JWT with role claim
        $token = JWTAuth::claims([
            'role' => $user->role
        ])->fromUser($user);

        // Store in session for Blade usage
        session([
            'jwt_token' => $token,
            'user_role' => $user->role
        ]);

        return redirect('/dashboard');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}