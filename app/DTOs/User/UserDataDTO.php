<?php 

namespace App\DTOs\User;

use App\Enums\User\UserRole;
use App\Models\User;

class UserDataDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public UserRole $role
    ) {}

    public static function fromModel(User $user): self 
    {
        return new self($user->id, $user->name, $user->email, UserRole::from($user->role));
    }

    public static function fromArray(array $data): self 
    {
        return new self($data['id'], $data['name'], $data['email'], UserRole::from($data['role']));
    }
}