<?php

declare(strict_types=1);

namespace NenePayout\User;

final readonly class UpdateUserInput
{
    public function __construct(
        public string $role,
    ) {
    }
}
