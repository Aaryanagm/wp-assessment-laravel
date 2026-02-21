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

        $user = User::where('user_email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['Invalid credentials']);
        }

        if (!Hash::check($request->password, $user->user_pass)) {
            return back()->withErrors(['Invalid credentials']);
        }


        $roleMeta = \DB::table('wp_usermeta')
            ->where('user_id', $user->ID)
            ->where('meta_key', 'wp_capabilities')
            ->first();

        $role = 'guest';

        if ($roleMeta) {
            $capabilities = unserialize($roleMeta->meta_value);
            $role = array_key_first($capabilities);
        }

        $token = JWTAuth::claims([
            'role' => $role
        ])->fromUser($user);

        session([
            'jwt_token' => $token,
            'user_role' => $role
        ]);

        return redirect('/dashboard');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}