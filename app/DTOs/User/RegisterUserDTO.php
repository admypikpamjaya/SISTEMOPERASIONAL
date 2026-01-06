<?php 

namespace App\DTOs\User;

use App\Enums\User\UserRole;

class RegisterUserDTO 
{
    public function __construct(
        public string $name,
        public string $username,
        public UserRole $role
    ) {}

    public static function fromArray(array $data): self 
    {
        return new self(
            $data['name'],
            $data['username'],
            UserRole::from($data['role'])
        );
    }
}