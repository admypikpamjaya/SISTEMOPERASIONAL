<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\User\ResetPasswordDTO;
use App\Enums\User\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    public function __construct(
        private UserService $service
    ) {}

    public function index(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->token,
            'email' => $request->email
        ]);
    }

    public function request()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $email = strtolower(trim((string) $validated['email']));
        $user = User::query()->where('email', $email)->first();

        if (!$user) {
            return back()->with('success', 'Jika email terdaftar, link reset password akan dikirim.');
        }

        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors([
                'email' => __($status),
            ])->withInput($request->only('email'));
        }

        return back()->with('success', 'Link reset password berhasil dikirim ke email.');
    }

    public function sendSystemManagementResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email Sistem Management wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $email = strtolower(trim((string) $validated['email']));
        $user = User::query()
            ->where('email', $email)
            ->where('role', UserRole::SYSTEM_MANAGEMENT->value)
            ->first();

        if (!$user) {
            return back()->with('success', 'Jika email Sistem Management terdaftar, link reset password akan dikirim.');
        }

        $this->service->sendResetPasswordLinkForUser($user);

        return back()->with('success', 'Link reset password Sistem Management berhasil dikirim ke email.');
    }

    public function reset(ResetPasswordRequest $request)
    {
        try 
        {
            $email = strtolower(trim((string) $request->validated('email')));
            $user = User::query()->where('email', $email)->first();
            $redirectRoute = $user?->role === UserRole::SYSTEM_MANAGEMENT->value
                ? 'system-management.login'
                : 'login';

            $this->service->resetPassword(ResetPasswordDTO::fromArray($request->validated()));
            return redirect()->route($redirectRoute)->with('success', 'Password berhasil direset');
        }
        catch(\Throwable $e)
        {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
