<?php 

namespace App\Services\User;

use App\DTOs\User\RegisterUserDTO;
use App\DTOs\User\UserDataDTO;

use App\Models\User;
use Illuminate\Support\Str;

class UserService 
{
    public function getUsers(?string $keyword = null, ?int $page = 1)
    {
        $users = User::where('name', 'like', '%'.$keyword.'%')
            ->orWhere('username', 'like', '%'.$keyword.'%')
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
            'username' => $dto->username,
            'password' => bcrypt($password),
            'role' => $dto->role->value
        ]);

        return UserDataDTO::fromModel($user);
    }

    public function updateUser(string $id, UserDataDTO $dto): UserDataDTO 
    {
        $user = User::find($id);
        if(empty($user))
            throw new \Exception('User tidak ditemukan', 404);

        $user->update([
            'name' => $dto->name,
            'username' => $dto->username,
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