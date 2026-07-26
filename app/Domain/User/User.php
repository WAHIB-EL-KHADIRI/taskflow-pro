<?php

declare(strict_types=1);

namespace App\Domain\User;

class User
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name = '',
        public readonly string $email = '',
        public readonly string $password = '',
        public readonly string $role = 'user',
        public readonly ?string $avatar = null,
        public readonly ?string $bio = null,
        public readonly string $theme = 'light',
        public readonly string $locale = 'fr',
        public readonly bool $emailVerified = false,
        public readonly ?string $rememberToken = null,
        public readonly ?string $resetToken = null,
        public readonly ?string $resetTokenExpires = null,
        public readonly ?string $lastLogin = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }
}
