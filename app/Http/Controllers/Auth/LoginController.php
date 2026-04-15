<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    //
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (\Illuminate\Support\Facades\Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            $user = \Illuminate\Support\Facades\Auth::user();
            
            if (!$user->is_active) {
                \Illuminate\Support\Facades\Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda belum diaktifkan.']);
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau Password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
