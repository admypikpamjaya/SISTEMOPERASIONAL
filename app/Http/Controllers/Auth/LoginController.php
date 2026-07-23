<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthenticationRequest;
use App\Enums\User\UserRole;
use App\Models\LoginHistory;
use App\Services\SystemManagement\ClientContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Throwable;

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
                ->with('auth_failed', 'Email atau password salah!')
                ->withInput($request->only('email'));

        if (Auth::user()?->role === UserRole::SYSTEM_MANAGEMENT->value) {
            Auth::logout();

            return redirect()
                ->route('system-management.login')
                ->with('auth_failed', 'Akun Sistem Management wajib masuk dari halaman khusus.');
        }

        $request->session()->regenerate();

        $this->storeLoginHistory($request);

        return redirect()->intended('/');
    }

    private function storeLoginHistory(AuthenticationRequest $request): void
    {
        if (!Auth::check() || !Schema::hasTable('login_histories')) {
            return;
        }

        try {
            LoginHistory::query()->create([
                ...app(ClientContextService::class)->fromRequest($request),
                'user_id' => (string) Auth::id(),
                'session_id' => (string) $request->session()->getId(),
                'locale' => (string) app()->getLocale(),
                'logged_in_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
