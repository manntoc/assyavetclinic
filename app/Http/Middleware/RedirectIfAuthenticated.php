<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $staffRoles = ['admin', 'superadmin', 'karyawan'];
                $isStaff = $user && (in_array($user->role, $staffRoles)
                    || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super-admin'])));

                if ($isStaff) {
                    return redirect()->route('dashboard-user'); // /dashboard-admin
                }

                return redirect()->route('homepage'); // /
            }
        }

        return $next($request);
    }
}
