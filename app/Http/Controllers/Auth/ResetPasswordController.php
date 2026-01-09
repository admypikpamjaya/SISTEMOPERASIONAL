<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\User\ResetPasswordDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\User\UserService;
use Illuminate\Http\Request;

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

    public function reset(ResetPasswordRequest $request)
    {
        try 
        {
            $this->service->resetPassword(ResetPasswordDTO::fromArray($request->validated()));
            return redirect()->route('login')->with('success', 'Password berhasil direset');
        }
        catch(\Throwable $e)
        {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
