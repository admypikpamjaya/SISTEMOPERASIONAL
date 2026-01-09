<?php 

namespace App\DTOs\User;

use App\Enums\User\UserRole;

class RegisterUserDTO 
{
    public function __construct(
        public string $name,
        public string $email,
        public UserRole $role
    ) {}

    public static function fromArray(array $data): self 
    {
        return new self(
            $data['name'],
            $data['email'],
            UserRole::from($data['role'])
        );
    }
}