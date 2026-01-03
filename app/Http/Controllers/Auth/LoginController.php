<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthenticationRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function authenticate(AuthenticationRequest $request)
    {
        $credentials = $request->safe()->except('remember');
        $remember = $request->boolean('remember');

        if(!Auth::attempt($credentials, $remember))
            return redirect()
                ->route('login')
                ->with('auth_failed', 'Username atau password salah!')
                ->withInput($request->only('username'));

        $request->session()->regenerate();
        return redirect()->intended('/');
    }
}
