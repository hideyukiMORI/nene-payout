<?php

declare(strict_types=1);

namespace NenePayout\Auth;

final readonly class User
{
    public function __construct(
        public string $id,
        public string $email,
        public string $passwordHash,
        public string $role,
        public ?string $organizationId = null,
        public string $status = 'active',
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
