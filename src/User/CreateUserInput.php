<?php

declare(strict_types=1);

namespace NenePayout\User;

final readonly class CreateUserInput
{
    public function __construct(
        public string $email,
        public string $role,
    ) {
    }
}
