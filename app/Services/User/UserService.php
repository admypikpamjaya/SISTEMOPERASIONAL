<?php 

namespace App\Services\User;

use App\DTOs\User\RegisterUserDTO;
use App\DTOs\User\ResetPasswordDTO;
use App\DTOs\User\UserDataDTO;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserService 
{
    public function getUsers(?string $keyword = null, ?int $page = 1)
    {
        $users = User::where('name', 'like', '%'.$keyword.'%')
            ->orWhere('email', 'like', '%'.$keyword.'%')
            ->paginate(5, ['*'], 'page', $page);
        
        return $users;
    }

    public function getUser(string $id): UserDataDTO
    {
        $user = User::find($id);
        if(empty($user))
            throw new \Exception('User tidak ditemukan', 404);

        return UserDataDTO::fromModel($user);
    }

    public function createUser(RegisterUserDTO $dto): UserDataDTO
    {
        $password = Str::random(12);
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => bcrypt($password),
            'role' => $dto->role->value
        ]);

        return UserDataDTO::fromModel($user);
    }

    public function sendResetPasswordLink(string $id): void 
    {
        $user = User::find($id);
        if(empty($user))
            throw new \Exception('User tidak ditemukan', 404);
        
        $status = Password::sendResetLink([
            'email' => $user->email
        ]);

        if($status !== Password::RESET_LINK_SENT)
            throw new \Exception(__($status), 500);
    }

    public function resetPassword(ResetPasswordDTO $dto)
    {
        $password = $dto->password;
        $status = Password::reset([
            'token' => $dto->token,
            'email' => $dto->email,
            'password' => $password,
            'password_confirmation' => $password
        ], function ($user) use ($password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();

            $user->tokens()?->delete();
        });

        if($status !== Password::PASSWORD_RESET)
        {
            $message = match($status) 
            {
                Password::INVALID_TOKEN => 'Token reset password tidak valid atau sudah kedaluwarsa',
                Password::INVALID_USER => 'Email tidak ditemukan',
                default => 'Gagal mereset password',
            };
            
            throw new \Exception($message, 400);
        }
    }

    public function updateUser(string $id, UserDataDTO $dto): UserDataDTO 
    {
        $user = User::find($id);
        if(empty($user))
            throw new \Exception('User tidak ditemukan', 404);

        $user->update([
            'name' => $dto->name,
            'email' => $dto->email,
            'role' => $dto->role->value
        ]);
        
        return UserDataDTO::fromModel($user);
    }

    public function deleteUser(string $id): void 
    {
        $user = User::find($id);
        if(empty($user))
            throw new \Exception('User tidak ditemukan', 404);
        
        $user->delete();
    }
}