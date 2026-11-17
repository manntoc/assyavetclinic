<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Bersihkan session intended jika mengarah ke homepage atau halaman login agar tidak menjebak user
        $intended = session()->get('url.intended');
        if ($intended && (rtrim($intended, '/') === rtrim(url('/'), '/') || str_contains($intended, 'login'))) {
            session()->forget('url.intended');
        }

        // Check the role of the authenticated user
        $staffRoles = ['admin', 'superadmin', 'karyawan'];
        $isStaff = $user && (in_array($user->role, $staffRoles)
            || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super-admin'])));

        if ($isStaff) {
            return redirect()->intended(RouteServiceProvider::ADMIN);
        }

        return redirect()->intended(RouteServiceProvider::USER);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function pindahadmin(Request $request)
    {
        // Langsung beralih ke CMS Website tanpa logout paksa
        return redirect()->route('dashboard');
    }
}
