<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $adminUserIds = [1, 2, 3, 4];

        if (Auth::check() && in_array(Auth::user()->id, $adminUserIds)) {
            return $next($request);
        }

        // Redirect non-admin users to user panel
        return redirect('/user');
    }
}
