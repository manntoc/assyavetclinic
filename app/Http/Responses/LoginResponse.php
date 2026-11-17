<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = Auth::user();

        // Bersihkan session intended jika mengarah ke homepage atau halaman login agar tidak menjebak user
        $intended = session()->get('url.intended');
        if ($intended && (rtrim($intended, '/') === rtrim(url('/'), '/') || str_contains($intended, 'login'))) {
            session()->forget('url.intended');
        }

        if ($user) {
            // Jika role adalah admin, superadmin, atau karyawan
            $staffRoles = ['admin', 'superadmin', 'karyawan'];
            $isStaff = in_array($user->role, $staffRoles)
                || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super-admin']));

            if ($isStaff) {
                return redirect()->intended(route('dashboard-user')); // /dashboard-admin
            }
        }

        return redirect()->intended(route('homepage')); // /
    }
}

