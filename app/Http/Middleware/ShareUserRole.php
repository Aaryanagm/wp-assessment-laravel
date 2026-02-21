<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareUserRole
{
    public function handle(Request $request, Closure $next)
    {
        View::share('userRole', session('user_role'));

        return $next($request);
    }
}