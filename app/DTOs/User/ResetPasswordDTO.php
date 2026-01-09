<?php

namespace App\DTOs\User;

class ResetPasswordDTO 
{
    public function __construct(
        public string $token,
        public string $email,
        public string $password
    ) {}

    public static function fromArray(array $data): self 
    {
        return new self(
            $data['token'],
            $data['email'],
            $data['password']
        );
    }
}